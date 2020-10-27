<?php

/**
 * FileSystem service
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\FileSystem;

use Netric\Error;
use Netric\EntityQuery\EntityQuery;
use Netric\Permissions\Dacl;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\FolderEntity;
use Netric\Entity\ObjType\FileEntity;
use Netric\Entity\EntityLoader;
use Netric\Entity\DataMapper\EntityDataMapperInterface;
use Netric\FileSystem\FileStore\FileStoreInterface;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Create a file system service
 *
 * @package Netric\FileSystem
 */
class FileSystem implements Error\ErrorAwareInterface
{
    /**
     * Index to query entities
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * Entity loader
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Entity saver
     *
     * @var EntityDataMapperInterface
     */
    private $entityDataMapper = null;

    /**
     * Current user
     *
     * @var UserEntity
     */
    private $user = null;

    /**
     * The root folder for this account
     *
     * @var FolderEntity
     */
    private $rootFolder = null;

    /**
     * Handles reading, writing, and deleting file data
     *
     * @var FileStoreInterface
     */
    private $fileStore = null;

    /**
     * Errors
     *
     * @var Error\Error[]
     */
    private $errors = [];

    /**
     * Define some path constants so we are not typing them as string literals each time
     */
    const PATH_TEMP = '/System/Temp';

    /**
     * Class constructor
     *
     * @param FileStoreInterface $fileStore Default fileStore for file data
     * @param UserEntity $user Current user
     * @param EntityLoader $entityLoader Used to load foldes and files
     * @param EntityDataMapperInterface $dataMapper DataMapper for account
     * @param IndexInterface $entityQueryIndex Index to find entities
     */
    public function __construct(
        FileStoreInterface $fileStore,
        UserEntity $user,
        EntityLoader $entityLoader,
        EntityDataMapperInterface $dataMapper,
        IndexInterface $entityQueryIndex
    ) {
        $this->fileStore = $fileStore;
        $this->user = $user;
        $this->entityDataMapper = $dataMapper;
        $this->entityLoader = $entityLoader;
        $this->entityIndex = $entityQueryIndex;

        $this->setRootFolder();
    }

    /**
     * Import a local file into the FileSystem
     *
     * @param string $localFilePath Path to a local file to import
     * @param string $remoteFolderPath The folder to import the new file into
     * @param string $fileNameOverride Optional alternate name to use other than the imported file name
     * @param array $fileEntityData Optional If we have a $fileEntityData, then we will be updating an existing file instead of creating a new file
     * @return FileEntity The imported file
     * @throws \RuntimeException if it cannot open the folder path specified
     */
    public function importFile($localFilePath, $remoteFolderPath, $fileNameOverride = "", $fileEntityData = null)
    {
        // Open FileSystem folder - second param creates it if not exists
        $parentFolder = $this->openFolder($remoteFolderPath, true);
        if (!$parentFolder) {
            throw new \RuntimeException("Could not open " . $remoteFolderPath);
        }

        // Check first if we have $fileId, if so then we will just load that file id
        if ($fileEntityData && isset($fileEntityData["entity_id"]) && !empty($fileEntityData["entity_id"])) {
            $file = $this->entityLoader->getEntityById($fileEntityData["entity_id"], $this->user->getAccountId());
            $file->setValue("name", $fileEntityData["name"]);
        } else {
            // Create a new file that will represent the file data
            $file = $this->entityLoader->create(ObjectTypes::FILE, $this->user->getAccountId());
            $file->setValue("owner_id", $this->user->getEntityId());

            // In some cases you may want to name the file something other than the local file name
            // such as when importing randomly named temp files.
            if ($fileNameOverride) {
                $file->setValue("name", $fileNameOverride);
            }
        }

        $file->setValue("folder_id", $parentFolder->getEntityId());
        $this->entityDataMapper->save($file, $this->user);

        // Upload the file to the FileStore
        $result = $this->fileStore->uploadFile($file, $localFilePath, $this->user);

        // If it fails then try to get the last error
        if (!$result && $this->fileStore->getLastError()) {
            $this->errors[] = $this->fileStore->getLastError();
        }

        return ($result) ? $file : null;
    }

    /**
     * Open a file by fileName and path
     *
     * @param $folderPath
     * @param $fileName
     * @return FileEntity|null
     */
    public function openFile($folderPath, $fileName)
    {
        $folder = $this->openFolder($folderPath);
        if ($folder) {
            return $this->getChildFileByName($fileName, $folder);
        } else {
            return null;
        }
    }

    /**
     * Delete a file
     *
     * @param FileEntity $file The file to delete
     * @param bool|false $purge If true the permanently purge the file
     * @return bool True on success, false on failure.
     */
    public function deleteFile(FileEntity $file, $purge = false)
    {
        $ret = $this->entityDataMapper->delete($file, $this->user);
        if ($ret) {
            $this->entityLoader->clearCacheByGuid($file->getEntityId());
        }
        return $ret;
    }

    /**
     * Delete a folder
     *
     * @param Folder $folder The folder to delee
     * @param bool|false $purge If true the permanently purge the file
     * @return bool True on success, false on failure.
     */
    public function deleteFolder(FolderEntity $folder, $purge = false)
    {
        return $this->entityDataMapper->delete($folder, $this->user);
    }

    /**
     * Get a file by id
     *
     * @param string fileId Unique id of the file
     * @return FileEntity
     */
    public function openFileById($fid)
    {
        if (!$fid) {
            throw new \InvalidArgumentException("File id is a required param.");
        }

        $file = $this->entityLoader->getEntityById($fid, $this->user->getAccountId());
        return ($file && $file->getEntityId()) ? $file : null;
    }

    /**
     * Open a file and place it in a stream wrapper for standard PHP stream functions
     *
     * @param $fid
     * @return resource|null Null if file not found
     */
    public function openFileStreamById($fid)
    {
        $file = $this->openFileById($fid);
        if ($file) {
            return FileStreamWrapper::open($this, $file);
        } else {
            return null;
        }
    }

    /**
     * Open a folder by a path
     *
     * @param $path The path to open - /my/favorite/path
     * @param bool|false $createIfMissing If true, create full path then return
     * @return Folder|null If found (or created), then return the folder, otherwise null
     */
    public function openFolder($path, $createIfMissing = false)
    {
        // Create system paths no matter what
        if (!$createIfMissing && ($path == "%tmp%" || $path == "%userdir%" || $path == "%home%")) {
            $createIfMissing = true;
        }

        // Check if we are just trying to get root
        if ($path === "/") {
            return $this->rootFolder;
        }

        $folders = $this->splitPathToFolderArray($path, $createIfMissing);

        if ($folders) {
            return array_pop($folders);
        } else {
            return null;
        }
    }

    /**
     * Get a folder by id
     *
     * @param $folderId
     * @return FilderEntity
     */
    public function openFolderById($folderId)
    {
        return $this->entityLoader->getEntityById($folderId, $this->user->getAccountId());
    }

    /**
     * Check to see if a given folder path exists
     *
     * @param $path The path to look for
     * @return bool true if the folder exists, otherwise false
     */
    public function folderExists($path)
    {
        $folders = $this->splitPathToFolderArray($path, false);
        return ($folders) ? true : false;
    }

    /**
     * Check to see if a file exists in a given path
     *
     * @param string $folderPath The full path of the folder to look in
     * @param string $fileName The name of the file in the folder path
     * @return bool true if exists, otherwise false
     */
    public function fileExists($folderPath, $fileName)
    {
        return ($this->openFile($folderPath, $fileName)) ? true : false;
    }

    /**
     * Convert number of bytes into a human readable form
     *
     * @param integer $size The size in bytes
     * @return string The human readable form of the size in bytes
     */
    public function getHumanSize($size)
    {
        if ($size >= 1000000000000) {
            return round($size / 1000000000000, 1) . "T";
        }
        if ($size >= 1000000000) {
            return round($size / 1000000000, 1) . "G";
        }
        if ($size >= 1000000) {
            return round($size / 1000000, 1) . "M";
        }
        if ($size >= 1000) {
            return round($size / 1000, 0) . "K";
        }
        if ($size < 1000) {
            return $size . "B";
        }
    }

    /**
     * Get the root folder for this account
     *
     * @return Folder
     */
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * Return the last logged error
     *
     * @return Error
     */
    public function getLastError()
    {
        return $this->errors[count($this->errors) - 1];
    }

    /**
     * Return all logged errors
     *
     * @return Error[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Determine if file is a temp file
     *
     * @param FileEntity $file The file to check
     * @return bool true if a temp file, false if it is note in the temp directory
     */
    public function fileIsTemp(FileEntity $file)
    {
        if (!$file->getEntityId()) {
            return false;
        }

        $tempFolder = $this->openFolder("%tmp%");

        if ($file->getValue("folder_id") == $tempFolder->getEntityId()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Move a file to a new folder
     *
     * @param FileEntity $file The file to move
     * @param FolderEntity $toFolder The folder to move to
     * @return bool true on success, false if failed
     */
    public function moveFile(FileEntity $file, FolderEntity $toFolder)
    {
        if (!$file || !$toFolder || !$toFolder->getEntityId()) {
            return false;
        }

        // Change file to new folder
        $file->setValue("folder_id", $toFolder->getEntityId());
        $this->entityDataMapper->save($file, $this->user);

        return true;
    }

    /**
     * Easy way to create a new empty file in a directory
     *
     * Example:
     *  $fileSystem->createFile('myfilename.txt', '/my/full/path');
     *
     * @param string $fileName
     * @param string $folderPath Defaults to temp directory initially if not set
     * @param bool $overwriteIfExists If the file already exists overwrite
     * @return FileEntity The created file or null if there was a problem -- call getLastError for details
     */
    public function createFile($folderPath, $fileName, $overwriteIfExists = false)
    {
        $folder = $this->openFolder($folderPath);
        $fullFolderPath = $folder->getFullPath();

        // First check to see if the file already exists
        $existingFile = $this->openFile($fullFolderPath, $fileName);
        if ($existingFile) {
            if ($overwriteIfExists) {
                $this->deleteFile($existingFile);
            } else {
                $this->errors[] = new Error\Error("File $fileName already exists in $folderPath");
                return null;
            }
        }

        // Create the new empty file
        $file = $this->entityLoader->create(ObjectTypes::FILE, $this->user->getAccountId());
        $file->setValue("name", $fileName);
        $file->setValue("folder_id", $folder->getEntityId());
        $file->setValue("name", $this->escapeFilename($fileName));
        $file->setValue("owner_id", $this->user->getEntityId());
        $file->setValue("file_size", 0);
        $this->entityDataMapper->save($file, $this->user);

        return $file;
    }

    /**
     * Set dacl permissions for a file and save the changes
     *
     * @param FileEntity $file
     * @param Dacl $dacl
     * @return void
     */
    public function setFileDacl(FileEntity $file, Dacl $dacl)
    {
        $file->setValue('dacl', json_encode($dacl->toArray()));
        $this->entityLoader->save($file, $this->user);
    }

    /**
     * Set dacl permissions for a folder and save the changes
     *
     * @param FolderEntity $folder
     * @param Dacl $dacl
     * @return void
     */
    public function setFolderDacl(FolderEntity $folder, Dacl $dacl)
    {
        $folder->setValue('dacl', json_encode($dacl->toArray()));
        $this->entityLoader->save($folder, $this->user);
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
        // TODO: add append to fileStore->writeFile
        return $this->fileStore->writeFile($file, $dataOrStream, $user);
    }

    /**
     * Read and return numBypes (or all) of a file
     *
     * @param FileEntity $file The meta-data Entity for this file
     * @param null $numBytes Number of bytes, if null then return while file
     * @param null $offset Starting offset, defaults to current pointer
     * @return mixed
     */
    public function readFile(FileEntity $file, $numBytes = null, $offset = null)
    {
        return $this->fileStore->readFile($file, $numBytes, $offset);
    }

    /**
     * Split a path into an array of folders
     *
     * @param string $path The folder path to split into an array of folders
     * @param bool $createIfMissing If set to true the function will attempt to create any missing directories
     * @return Entity[]
     */
    private function splitPathToFolderArray($path, $createIfMissing = false)
    {
        /*
         * Translate any variables in path like %tmp% and %userdir% to actual path
         */
        $path = $this->substituteVariables($path);

        /*
         * Normalize everything relative to root so /my/path will return:
         * my/path since root is always implied.
         */
        if (strlen($path) > 1 && $path[0] === '/') {
            // Skip over first '/'
            $path = substr($path, 1);
        }

        // Parse folder path
        $folderNames = explode("/", $path);
        $folders = [$this->rootFolder];
        $lastFolder = $this->rootFolder;
        foreach ($folderNames as $nextFolderName) {
            $nextFolder = $this->getChildFolderByName($nextFolderName, $lastFolder);

            // If the folder exists add it and continue
            if ($nextFolder && $nextFolder->getEntityId()) {
                $folders[] = $nextFolder;
            } elseif ($createIfMissing) {
                // TODO: Check permissions to see if we have access to create

                $nextFolder = $this->entityLoader->create(ObjectTypes::FOLDER, $this->user->getAccountId());
                $nextFolder->setValue("name", $nextFolderName);
                $nextFolder->setValue("parent_id", $lastFolder->getEntityId());
                $nextFolder->setValue("owner_id", $this->user->getEntityId());
                $this->entityDataMapper->save($nextFolder, $this->user);

                $folders[] = $nextFolder;
            } else {
                // Full path does not exist
                return false;
            }

            // Move to the next hop
            $lastFolder = $nextFolder;
        }

        return $folders;
    }

    /**
     * Handle variable substitution and normalize path
     *
     * @param string $path The path to replace variables with
     * @return string The path with variables substituted for real values
     */
    private function substituteVariables($path)
    {
        $retval = $path;

        $retval = str_replace("%tmp%", self::PATH_TEMP, $retval);

        // Get a user's home directory
        $retval = str_replace("%userdir%", "/System/Users/" . $this->user->getEntityId(), $retval);
        $retval = str_replace("%home%", "/System/Users/" . $this->user->getEntityId(), $retval);

        // Get email attechments directory for a user
        $retval = str_replace(
            "%emailattachments%",
            "/System/Users/" . $this->user->getEntityId() . "/System/Email Attachments",
            $retval
        );

        // Replace any empty directories
        $retval = str_replace("//", "/", $retval);

        // TODO: Now kill all unallowed chars?
        /*
        $retval = str_replace("%", "", $retval);
        $retval = str_replace("?", "", $retval);
        $retval = str_replace(":", "", $retval);
        $retval = str_replace("\\", "", $retval);
        $retval = str_replace(">", "", $retval);
        $retval = str_replace("<", "", $retval);
        $retval = str_replace("|", "", $retval);
         */

        return $retval;
    }

    /**
     * Get a child folder by name
     *
     * @param $name
     * @param FolderEntity $parentFolder The folder that contains a child folder named $name
     * @return Folder|null
     */
    private function getChildFolderByName($name, FolderEntity $parentFolder)
    {
        $query = new EntityQuery(ObjectTypes::FOLDER, $parentFolder->getAccountId());
        $query->where("parent_id")->equals($parentFolder->getEntityId());
        $query->andWhere("name")->equals($name);
        $result = $this->entityIndex->executeQuery($query);
        if ($result->getNum()) {
            return $result->getEntity();
        }

        return null;
    }

    /**
     * Get a child file by name
     *
     * @param string $fileName The name of the file to look for
     * @param FolderEntity $parentFolder The folder that contains a child folder named $name
     * @return Folder|null
     */
    private function getChildFileByName($fileName, FolderEntity $parentFolder)
    {
        $query = new EntityQuery(ObjectTypes::FILE, $parentFolder->getAccountId());
        $query->where("folder_id")->equals($parentFolder->getEntityId());
        $query->andWhere("name")->equals($fileName);
        $result = $this->entityIndex->executeQuery($query);
        if ($result->getNum()) {
            return $result->getEntity();
        }

        return null;
    }

    /**
     * Get the root folder entity for this account
     */
    private function setRootFolder()
    {
        /// First try to get the existing folder root if it exists
        $query = new EntityQuery(ObjectTypes::FOLDER, $this->user->getAccountId());
        $query->where("parent_id")->equals("");
        $query->andWhere("name")->equals("/");
        $query->andWhere("f_system")->equals(true);
        $result = $this->entityIndex->executeQuery($query);
        if ($result->getNum()) {
            $this->rootFolder = $result->getEntity();
            return;
        }

        // Create root folder
        $rootFolder = $this->entityLoader->create(ObjectTypes::FOLDER, $this->user->getAccountId());
        $rootFolder->setValue("name", "/");
        $rootFolder->setValue("owner_id", $this->user->getEntityId());
        $rootFolder->setValue("f_system", true);
        $this->entityLoader->save($rootFolder, $this->user);

        // Now set it for later reference
        $this->rootFolder = $rootFolder;
    }

    /**
     * Replaces the special characters with blank
     *
     * @param string $filename Name of the file to escape
     * @return string Escaped file name
     */
    private function escapeFilename($filename)
    {
        return preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $filename);
    }
}
