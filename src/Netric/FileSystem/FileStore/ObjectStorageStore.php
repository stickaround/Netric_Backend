<?php

namespace Netric\FileSystem\FileStore;

use http\Exception\RuntimeException;
use Netric\Error;
use Netric\Entity\ObjType\FileEntity;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use ObjectStorageSdk\ObjectStorageClient;
use ObjectStorageSdk\ObjectStorageClientInterface;

/**
 * Store files in Aereus ObjectStoragae
 */
class ObjectStorageStore extends Error\AbstractHasErrors implements FileStoreInterface
{
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
     * The objectstorage server
     *
     * @var string
     */
    private $server = '';

    /**
     * The name of the account on mogile storing files for netric
     *
     * @var string
     */
    private $account = '';

    /**
     * Secret used for connecting to the object store
     *
     * @var string
     */
    private $secret = '';

    /**
     * MogileFs client
     *
     * @var ObjectStorageClientInterface
     */
    private $client = null;

    /**
     * We store everything in the netric bucket for now
     */
    const BUCKET = 'netric';

    /**
     * Class constructor
     *
     * @param EntityLoader $entityLoader An entity loader to save entitites.
     * @param string $tmpPath The temp folder path
     * @param string $mogileServer The server endpoint to connect to
     * @param string $mogileAccount The account name to use for file storage
     */
    public function __construct(
        EntityLoader $entityLoader,
        $tmpPath,
        string $server,
        string $account,
        string $secret
    ) {
        $this->entityLoader = $entityLoader;
        $this->tmpPath = $tmpPath;
        $this->server = $server;
        $this->account = $account;
        $this->secret = $secret;
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
            throw new Exception\FileNotFoundException(
                $file->getEntityId() . ":" . $file->getName() . " not found. No key."
            );
        }

        $handle = $this->getClient()->getStream(self::BUCKET, $file->getValue("dat_ans_key"));
        $file->setFileHandle($handle);

        // If offset was not defined then get the whole file
        if (!$offset) {
            $offset = -1;
        }

        // If the user did not indicate the number of bytes to read then whole file
        if ($numBytes === null) {
            $numBytes = $file->getValue("file_size");
        }

        if ($file->getFileHandle()) {
            return stream_get_contents($file->getFileHandle(), $numBytes, $offset);
        }

        return false;
    }

    /**
     * Get a file stream to read from
     *
     * @param FileEntity $file The meta-data Entity for this file
     * @return resource
     */
    public function openFileStream(FileEntity $file)
    {
        if (!$file->getValue("dat_ans_key")) {
            throw new Exception\FileNotFoundException(
                $file->getEntityId() . ":" . $file->getName() . " not found. No key."
            );
        }

        return $this->getClient()->getStream(self::BUCKET, $file->getValue("dat_ans_key"));
    }

    /**
     * Write data to a file
     *
     * @param FileEntity $file The meta-data Entity for this file
     * @param $dataOrStream $data Binary data to write or a stream resource
     * @param UserEntity $user
     * @return int number of bytes written
     */
    public function writeFile(FileEntity $file, $dataOrStream, UserEntity $user)
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
        if ($this->uploadFile($file, $tempPath, $user)) {
            // Cleanup
            unlink($tempPath);
            return $bytesWritten;
        }

        // Cleanup
        @unlink($tempPath);

        // Return error, uploadFile should have set this->getLastError()
        return -1;
    }

    /**
     * Upload a file to the data store
     *
     * @param FileEntity $file Meta-data Entity for the file
     * @param string $localPath Path of a local file
     * @param UserEntity $user
     * @return bool true on success, false on failure
     */
    public function uploadFile(FileEntity $file, $localPath, UserEntity $user)
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
                $parts = [$localPath];
            }

            // last entry is the file name
            $file->setValue("name", $parts[count($parts) - 1]);
        }

        $size = filesize($localPath);

        if ($size <= 0) {
            $this->addErrorFromMessage("Cannot upload zero byte file");
            return false;
        }

        // Generate a unique id for the file
        $key = $user->getAccountId() . "/" . $file->getEntityId() . "/";
        $key .= $file->getValue("revision") . "/" . $file->getName();

        // Put the file on the server
        if (!$this->getClient()->uploadFromFile(self::BUCKET, $key, $localPath)) {
            $this->addErrorFromMessage("Could not upload file: $localPath");
            return false;
        }

        // Update file entity
        $file->setValue("file_size", $size);

        // If set, then clear. Path will remain in saved revision.
        $file->setValue("dat_local_path", "");
        $file->setValue("dat_ans_key", $key);

        $this->entityLoader->save($file, $user);
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
        if (!$this->getClient()->remove(self::BUCKET, $file->getValue("dat_ans_key"))) {
            $this->addErrorFromMessage("Could not delete file");
            return false;
        }

        // Delete all past revisions
        $revisions = $this->entityLoader->getRevisions($file->getEntityId(), $file->getValue('account_id'));
        foreach ($revisions as $fileRev) {
            if ($fileRev->getValue("dat_ans_key")) {
                if (!$this->client->remove(self::BUCKET, $fileRev->getValue("dat_ans_key"))) {
                    $this->addErrorFromMessage("Could not delete file");
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

        return $this->client->exists(self::BUCKET, $file->getValue('dat_ans_key'));
    }

    /**
     * Generate a temp name for this file so we can work with it autonomously in the tmp dir
     *
     * @param FileEntity $file
     * @return string unique temp name
     */
    private function getTempName(FileEntity $file)
    {
        return "file-" . $file->getAccountId() . "-" . $file->getEntityId() . "-" . $file->getValue('revision');
    }

    /**
     * Retrieve existing connection or establish a new one
     *
     * @return MogileFs
     * @throws MogileFsException if we cannot connect
     */
    private function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        // Construct object storage client
        $this->client = new ObjectStorageClient($this->account, $this->secret, $this->server);

        return $this->client;
    }
}
