<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem\FileStore;

use Netric\Error;
use Netric\Entity\ObjType\FileEntity;
use Netric\Entity\EntityLoader;
use Netric\FileSystem\FileStore\Exception\CannotConnectException;
use MogileFs;
use MogileFsException;

/**
 * Store files in MogileFS
 */
class MogileFileStore extends Error\AbstractHasErrors implements FileStoreInterface
{
    /**
     * Account/tenant ID
     *
     * @var int
     */
    private $accountId = null;

    /**
     * Entity Loader for pulling revision data
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Temporary folder path for processing remote files locally
     *
     * @var string
     */
    private $tmpPath = null;

    /**
     * The Mogile tracker address
     *
     * @var null|string
     */
    private $mogileServer = null;

    /**
     * The port of the mogiletracker to connect to
     *
     * @var int|null
     */
    private $mogilePort = null;

    /**
     * The name of the account on mogile storing files for netric
     *
     * @var string|null
     */
    private $mogileAccount = null;

    /**
     * MogileFs client
     *
     * @var MogileFs
     */
    private $mogileFs = null;

    /**
     * Class used for mogileFS
     */
    const MOGILE_CLASS = "userfiles";

    /**
     * Class constructor
     *
     * @param string $accountId The unique id of the tennant's account
     * @param EntityLoader $entityLoader An entity loader to save entitites. We need to use entity loader so it can cache the entity data.
     * @param string $tmpPath The temp folder path
     * @param string $mogileServer The server endpoint to connect to
     * @param string $mogileAccount The account name to use for file storage
     * @param int $mogilePort Optional port to connect to the mogile tracker to find files
     */
    public function __construct(
        $accountId,
        EntityLoader $entityLoader,
        $tmpPath,
        string $mogileServer,
        string $mogileAccount,
        int $mogilePort = 7001
    ) {
        $this->accountId = $accountId;
        $this->entityLoader = $entityLoader;
        $this->tmpPath = $tmpPath;
        $this->mogileServer = $mogileServer;
        $this->mogilePort = $mogilePort;
        $this->mogileAccount = $mogileAccount;
    }

    /**
     * Read and return numBypes (or all) of a file
     *
     * @param FileEntity $file The meta-data Entity for this file
     * @param null $numBytes Number of bytes, if null then return while file
     * @param null $offset Starting offset, defaults to current pointer
     * @return mixed
     * @throws Exception\FileNotFoundException if the file is not found
     */
    public function readFile(FileEntity $file, $numBytes = null, $offset = null)
    {
        if (!$file->getValue("dat_ans_key")) {
            throw new Exception\FileNotFoundException($file->getId() . ":" . $file->getName() . " not found. No key");
        }

        $metadata = $this->getMogileFsConnection()->get($file->getValue("dat_ans_key"));

        // MogileFs will return 0 paths if no files are available
        if (!isset($metadata['paths']) || $metadata['paths'] == '0') {
            return false;
        }

        if (!isset($metadata['path1'])) {
            return false;
        }

        // If file has not yet been opened, then open it
        if (!$file->getFileHandle()) {
            if ($this->fileExists($file)) {
                // Get a valid handle (sometimes we have to try a few servers)
                for ($i = 1; $i <= (int) $metadata['paths']; $i++) {
                    if (isset($metadata['path' . $i])) {
                        $handle = fopen($metadata['path' . $i], 'rb');
                        if ($handle) {
                            $file->setFileHandle($handle);
                            break;
                        }
                    }
                }
            } else {
                throw new Exception\FileNotFoundException(
                    "Key '" . $file->getValue("dat_ans_key") . "' is not in the MogileFS store: " .
                    $this->getLastError()->getMessage()
                );
            }
        }

        // If offset was not defined then get the whole file
        if (!$offset) {
            $offset = -1;
        }

        // If the user did not indicate the number of bytes to read then whole file
        if (!$numBytes) {
            $numBytes = $file->getValue("file_size");
        }

        if ($file->getFileHandle()) {
            return stream_get_contents($file->getFileHandle(), $numBytes, $offset);
        } else {
            return false;
        }
    }

    /**
     * Write data to a file
     *
     * @param FileEntity $file The meta-data Entity for this file
     * @param $dataOrStream $data Binary data to write or a stream resource
     * @return int number of bytes written
     */
    public function writeFile(FileEntity $file, $dataOrStream)
    {
        // 1. Write to temp
        $tempName = $this->getTempName($file);
        $tempPath = $this->tmpPath . "/" . $tempName;

        if (is_resource($dataOrStream)) {
            $tmpFile = fopen($tempPath, 'w');
            while (!feof($dataOrStream)) {
                $buf = fread($dataOrStream, 2082);
                if ($buf) {
                    fwrite($tmpFile, $buf);
                }
            }
            fclose($tmpFile);
        } else {
            file_put_contents($tempPath, $dataOrStream);
        }

        $bytesWritten = filesize($tempPath);

        // 2. Upload
        if ($this->uploadFile($file, $tempPath)) {
            // Cleanup
            unlink($tempPath);

            return $bytesWritten;
        } else {
            // Cleanup
            @unlink($tempPath);

            // Return error, uploadFile should have set this->getLastError()
            return -1;
        }
    }

    /**
     * Upload a file to the data store
     *
     * @param FileEntity $file Meta-data Entity for the file
     * @param string $localPath Path of a local file
     * @return true on success, false on failure
     */
    public function uploadFile(FileEntity $file, $localPath)
    {
        if (!file_exists($localPath)) {
            $this->addErrorFromMessage("Could not upload file: $localPath does not exist");
            return false;
        }

        // Close the file handle if open
        if ($file->getFileHandle()) {
            fclose($file->getFileHandle());
            $file->setFileHandle(null);
        }

        // If the filename was not yet set for this file then get from source
        if (!$file->getValue("name")) {
            if (strrpos($localPath, "/") !== false) { // unix
                $parts = explode("/", $localPath);
            } elseif (strrpos($localPath, "\\") !== false) { // windows
                $parts = explode("\\", $localPath);
            } else {
                $parts = array($localPath);
            }

            // last entry is the file name
            $file->setValue("name", $parts[count($parts)-1]);
        }

        $size = filesize($localPath);

        if ($size <= 0) {
            $this->addErrorFromMessage("Cannot upload zero byte file");
            return false;
        }

        // Generate a unique id for the file
        $key = $this->accountId . "/" . $file->getId() . "/";
        $key .= $file->getValue("revision") . "/" . $file->getName();

        // Put the file on the server
        if (!$this->getMogileFsConnection()->put($localPath, $key, self::MOGILE_CLASS)) {
            $this->addErrorFromMessage("Could not upload file: $localPath");
            return false;
        }

        // Update file entity
        $file->setValue("file_size", $size);
        // If set, then clear. Path will remain in saved revision.
        $file->setValue("dat_local_path", "");
        $file->setValue("dat_ans_key", $key);

        $this->entityLoader->save($file);
        return true;
    }

    /**
     * Delete a file from the DataMapper
     *
     * @param FileEntity $file The file to purge data for
     * @param int $revision If set then only delete data for a specific revision
     * @return mixed
     */
    public function deleteFile(FileEntity $file, $revision = null)
    {
        // We cannot delete something that was never uploaded
        if (!$file->getValue('dat_ans_key')) {
            return false;
        }

        // Assume failure until we succeed
        $key = $file->getValue("dat_ans_key");
        try {
            if (!$this->getMogileFsConnection()->delete($key)) {
                $this->addErrorFromMessage("Could not delte file");
                return false;
            }
        } catch (MogileFsException $e) {
            $this->addErrorFromMessage("File deletion failed: " . $e->getMessage());
            return false;
        }

        // Delete all past revisions
        $revisions = $this->entityLoader->getRevisions("file", $file->getId());
        foreach ($revisions as $fileRev) {
            if ($fileRev->getValue("dat_ans_key")) {
                try {
                    if (!$this->getMogileFsConnection()->delete($fileRev->getValue("dat_ans_key"))) {
                        $this->addErrorFromMessage("Could not delete file");
                    }
                } catch (MogileFsException $e) {
                    $this->addErrorFromMessage("File deletion failed: " . $e->getMessage());
                }
            }
        }

        // If we made it this far we have succeeded
        return true;
    }

    /**
     * Check to see if a file exists in the store
     *
     * @param FileEntity $file The file to purge data for
     * @return bool true if it exists, otherwise false
     */
    public function fileExists(FileEntity $file)
    {
        // If we are missing a key then we know for sure it does not exist in the store
        if (!$file->getValue('dat_ans_key')) {
            return false;
        }

        // Normally mogile will throw an exception if the file does not exist
        try {
            $ret = $this->getMogileFsConnection()->get($file->getValue('dat_ans_key'));
        } catch (MogileFsException $ex) {
            return false;
        }

        // Just in case it returns with no paths
        if (isset($ret['paths'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generate a temp name for this file so we can work with it autonomously in the tmp dir
     *
     * @param FileEntity $file
     * @return string unique temp name
     */
    private function getTempName(FileEntity $file)
    {
        return "file-" . $this->accountId . "-" . $file->getId() . "-" . $file->getValue('revision');
    }

    /**
     * Retrieve existing connection or establish a new one
     *
     * @return MogileFs
     * @throws MogileFsException if we cannot connect
     */
    private function getMogileFsConnection()
    {
        if ($this->mogileFs) {
            return $this->mogileFs;
        }

        // Establish mogile connection
        try {
            $this->mogileFs = new MogileFs();
            $this->mogileFs->connect($this->mogileServer, $this->mogilePort, $this->mogileAccount);
        } catch (MogileFsException $ex) {
            // Throw generic FileSystem exception to let callers know what failed
            throw new CannotConnectException($ex->getMessage());
        }

        return $this->mogileFs;
    }
}
