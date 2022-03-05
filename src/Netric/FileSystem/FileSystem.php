<?php

/**
 * FileSystem service
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2022 Aereus, LLC
 */

namespace Netric\FileSystem;

use Netric\Entity\EntityInterface;
use Netric\Error;
use Netric\EntityQuery\EntityQuery;
use Netric\Permissions\Dacl;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\FolderEntity;
use Netric\Entity\ObjType\FileEntity;
use Netric\Entity\EntityLoader;
use Netric\FileSystem\FileStore\FileStoreInterface;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityDefinition\ObjectTypes;
use RuntimeException;

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
     * Unames used for special folders
     */
    const UNAME_ROOT = 'root';      // Account root
    const UNAME_TEMP = 'temp';      // Where temp files get stored
    const UNAME_ENTITY = 'entity';  // Where entity folders are placed

    /**
     * Class constructor
     *
     * @param FileStoreInterface $fileStore Default fileStore for file data
     * @param EntityLoader $entityLoader Used to load foldes and files
     * @param IndexInterface $entityQueryIndex Index to find entities
     */
    public function __construct(
        FileStoreInterface $fileStore,
        EntityLoader $entityLoader,
        IndexInterface $entityQueryIndex
    ) {
        $this->fileStore = $fileStore;
        $this->entityLoader = $entityLoader;
        $this->entityIndex = $entityQueryIndex;
    }

    /**
     * Import a local file into the temp folder
     *
     * @param UserEntity $user The user that owns the file
     * @param string $localFilePath Path to a local file to import
     * @param string $fileNameOverride Optional alternate name to use other than the imported file name
     * @param array $fileEntityData Optional If we have a $fileEntityData,
     *              then we will be updating an existing file instead of creating a new file
     * @return FileEntity The imported file
     * @throws \RuntimeException if it cannot open the folder path specified
     */
    public function importTempFile(
        UserEntity $user,
        string $localFilePath,
        string $fileNameOverride = "",
        $fileEntityData = null
    ) {
        // Open FileSystem folder - second param creates it if not exists
        $parentFolder = $this->getTempFolder($user);
        if (!$parentFolder) {
            // Try to create the temp folder
            throw new \RuntimeException("Could not open temp folder");
        }

        return $this->importFileToFolder($user, $parentFolder, $localFilePath, $fileNameOverride, $fileEntityData);
    }

    /**
     * Import a local file into the FileSystem
     *
     * @param UserEntity $user The user that owns the file
     * @param FolderEntity $folder THe folder to import into
     * @param string $localFilePath Path to a local file to import
     * @param string $remoteFolderPath The folder to import the new file into
     * @param string $fileNameOverride Optional alternate name to use other than the imported file name
     * @param array $fileEntityData Optional If we have a $fileEntityData, then we will be updating an existing file instead of creating a new file
     * @return FileEntity The imported file
     * @throws \RuntimeException if it cannot open the folder path specified
     */
    public function importFileToFolder(UserEntity $user, FolderEntity $parentFolder, string $localFilePath, string $fileNameOverride = "", $fileEntityData = null)
    {
        // Check first if we have $fileId, if so then we will just load that file id
        if ($fileEntityData && isset($fileEntityData["entity_id"]) && !empty($fileEntityData["entity_id"])) {
            $file = $this->entityLoader->getEntityById($fileEntityData["entity_id"], $user->getAccountId());
            $file->setValue("name", $fileEntityData["name"]);
        } else {
            // Create a new file that will represent the file data
            $file = $this->entityLoader->create(ObjectTypes::FILE, $user->getAccountId());
            $file->setValue("owner_id", $user->getEntityId());

            // In some cases you may want to name the file something other than the local file name
            // such as when importing randomly named temp files.
            if ($fileNameOverride) {
                $file->setValue("name", $fileNameOverride);
            }
        }

        $file->setValue("folder_id", $parentFolder->getEntityId());
        $this->entityLoader->save($file, $user);

        // Upload the file to the FileStore
        $result = $this->fileStore->uploadFile($file, $localFilePath, $user);

        // If it fails then try to get the last error
        if (!$result && $this->fileStore->getLastError()) {
            $this->errors[] = $this->fileStore->getLastError();
        }

        return ($result) ? $file : null;
    }

    /**
     * Open a file by fileName
     *
     * @param FolderEntity $folder The folder to look for files in
     * @param string $fileName The name of the file in the folder path
     * @param UserEntity $user The user that owns the file
     * @return FileEntity|null
     */
    public function openFileByName(FolderEntity $folder, string $fileName, UserEntity $user)
    {
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
     * @param UserEntity $user The user that owns the file
     * @param bool|false $purge If true the permanently purge the file
     * @return bool True on success, false on failure.
     */
    public function deleteFile(FileEntity $file, UserEntity $user, bool $purge = false)
    {
        return $this->entityLoader->delete($file, $user);
    }

    /**
     * Get a stream resource to read data for a file
     *
     * @param FileEntity $file
     * @return resource
     */
    public function openFileStream(FileEntity $file)
    {
        return $this->fileStore->openFileStream($file);
    }

    /**
     * Delete a folder
     *
     * @param FolderEntity $folder The folder to delee
     * @param UserEntity $user The user that owns the Folder
     * @param bool|false $purge If true the permanently purge the file
     * @return bool True on success, false on failure.
     */
    public function deleteFolder(FolderEntity $folder, UserEntity $user, $purge = false)
    {
        return $this->entityLoader->delete($folder, $user);
    }

    /**
     * Get a file by id
     *
     * @param string fileId Unique id of the file
     * @param UserEntity $user The user that owns the file
     * @return FileEntity
     */
    public function openFileById(string $fid, UserEntity $user)
    {
        if (!$fid) {
            throw new \InvalidArgumentException("File id is a required param.");
        }

        $file = $this->entityLoader->getEntityById($fid, $user->getAccountId());
        return ($file && $file->getEntityId()) ? $file : null;
    }

    /**
     * Open a file and place it in a stream wrapper for standard PHP stream functions
     *
     * @param string $fid The fileId to open
     * @param UserEntity $user The user that owns the file
     * @return resource|null Null if file not found
     */
    public function openFileStreamById(string $fileId, UserEntity $user)
    {
        $file = $this->openFileById($fileId, $user);
        if ($file) {
            return $this->openFileStream($file);
        } else {
            return null;
        }
    }

    /**
     * Open (if exists) or create a folder
     *
     * @param FolderEntity $parentFolder
     * @param string $folderName
     * @param UserEntity $user
     * @return FolderEntity
     */
    public function openOrCreateFolder(FolderEntity $parentFolder, string $folderName, UserEntity $user): FolderEntity
    {
        $folder = $this->getChildFolderByName($folderName, $parentFolder);
        if ($folder) {
            return $folder;
        }

        // Create a new folder
        $folder = $this->entityLoader->create(ObjectTypes::FOLDER, $user->getAccountId());
        $folder->setValue("parent_id", $parentFolder->getEntityId());
        $folder->setValue("name", $folderName);
        $this->entityLoader->save($folder, $user);
        return $folder;
    }

    /**
     * Get (or create if missing) the folder used to store files for an entity
     *
     * @param EntityInterface $entity
     * @param UserEntity $user
     * @return FolderEntity
     */
    public function getOrCreateEntityFolder(EntityInterface $entity, UserEntity $user): FolderEntity
    {
        if (!$entity->getEntityId()) {
            throw new RuntimeException("getOrCreateEntityFolder can only be called on a saved entity");
        }

        $folderWithUname = $this->entityLoader->getByUniqueName(
            ObjectTypes::FOLDER,
            $entity->getEntityId(), // The uname of the folder will be the entity id
            $user->getAccountId()
        );

        // Entity folder alraedy exists
        if ($folderWithUname) {
            return $folderWithUname;
        }

        // Create a new folder with the file entity_id as the uname
        $entitySystemFolder = $this->entityLoader->getByUniqueName(
            ObjectTypes::FOLDER,
            self::UNAME_ENTITY,
            $user->getAccountId()
        );

        // Catch scenario where the entity system folder has not been created yet
        if (!$entitySystemFolder) {
            throw new RuntimeException(
                "The Entity root volume does not exist, please run setup/update to initialize"
            );
        }

        $entityAttFolder = $this->entityLoader->create(
            ObjectTypes::FOLDER,
            $user->getAccountId()
        );
        $entityAttFolder->setValue('name', $entity->getEntityId());
        $entityAttFolder->setValue('uname', $entity->getEntityId());
        $entityAttFolder->setValue('parent_id', $entitySystemFolder->getEntityId());
        $this->entityLoader->save($entityAttFolder, $user);
        return $entityAttFolder;
    }

    /**
     * Get a folder by id
     *
     * @param string $folderId The id of the folder that we are going to open
     * @param UserEntity $user The user that owns the the folder
     * @return FolderEntity|null
     */
    public function openFolderById(string $folderId, UserEntity $user): FolderEntity|null
    {
        return $this->entityLoader->getEntityById($folderId, $user->getAccountId());
    }


    /**
     * Check to see if a file exists in a given path
     *
     * @param string $folderPath The full path of the folder to look in
     * @param string $fileName The name of the file in the folder path
     * @param UserEntity $user The user that owns the the file
     * @return bool true if exists, otherwise false
     */
    public function fileExists(FolderEntity $folder, string $fileName, UserEntity $user)
    {
        return ($this->openFileByName($folder, $fileName, $user)) ? true : false;
    }

    /**
     * Check to see if a file exists in a given path
     *
     * @param string $folderPath The full path of the folder to look in
     * @param string $fileName The name of the file in the folder path
     * @param UserEntity $user The user that owns the the file
     * @return bool true if exists, otherwise false
     */
    public function folderExists(FolderEntity $folder, string $folderName, UserEntity $user)
    {
        return ($this->getChildFolderByName($folderName, $folder, $user)) ? true : false;
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
     * @param UserEntity $user The user that owns the the file
     * @return bool true if a temp file, false if it is note in the temp directory
     */
    public function fileIsTemp(FileEntity $file, UserEntity $user)
    {
        if (!$file->getEntityId()) {
            return false;
        }

        $tempFolder = $this->getTempFolder($user);

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
     * @param UserEntity $user The user that owns the the file
     * @return bool true on success, false if failed
     */
    public function moveFile(FileEntity $file, FolderEntity $toFolder, UserEntity $user)
    {
        if (!$file || !$toFolder || !$toFolder->getEntityId()) {
            return false;
        }

        // Change file to new folder
        $file->setValue("folder_id", $toFolder->getEntityId());
        $this->entityLoader->save($file, $user);

        return true;
    }

    /**
     * Easy way to create a new empty file in a directory
     *
     * Example:
     *  $fileSystem->createTempFile('myfilename.txt', $user);
     *
     * @param string $folderPath Defaults to temp directory initially if not set
     * @param string $fileName The name of the file
     * @param UserEntity $user The user that owns the file
     * @param bool $overwriteIfExists If the file already exists overwrite
     * @return FileEntity The created file or null if there was a problem -- call getLastError for details
     */
    public function createTempFile(string $fileName, UserEntity $user, $overwriteIfExists = false)
    {
        $tempFolder = $this->getTempFolder($user);
        return $this->createFile($tempFolder, $fileName, $user, $overwriteIfExists);
    }

    /**
     * Easy way to create a new empty file in a directory
     *
     * Example:
     *  $fileSystem->createFile($folder, 'myfilename.txt', $user);
     *
     * @param string $folderPath Defaults to temp directory initially if not set
     * @param string $fileName The name of the file
     * @param UserEntity $user The user that owns the file
     * @param bool $overwriteIfExists If the file already exists overwrite
     * @return FileEntity The created file or null if there was a problem -- call getLastError for details
     */
    public function createFile(FolderEntity $folder, string $fileName, UserEntity $user, $overwriteIfExists = false)
    {
        // First check to see if the file already exists
        $existingFile = $this->openFileByName($folder, $fileName, $user);
        if ($existingFile) {
            if ($overwriteIfExists) {
                $this->deleteFile($existingFile, $user);
            } else {
                $this->errors[] = new Error\Error("File $fileName already exists in" . $folder->getEntityId());
                return null;
            }
        }

        // Create the new empty file
        $file = $this->entityLoader->create(ObjectTypes::FILE, $user->getAccountId());
        $file->setValue("name", $fileName);
        $file->setValue("folder_id", $folder->getEntityId());
        $file->setValue("name", $this->escapeFilename($fileName));
        $file->setValue("owner_id", $user->getEntityId());
        $file->setValue("file_size", 0);
        $this->entityLoader->save($file, $user);

        return $file;
    }

    /**
     * Set dacl permissions for a file and save the changes
     *
     * @param FileEntity $file
     * @param Dacl $dacl
     * @param FileEntity $file The meta-data Entity for this file
     * @return void
     */
    public function setFileDacl(FileEntity $file, Dacl $dacl, UserEntity $user)
    {
        $file->setValue('dacl', json_encode($dacl->toArray()));
        $this->entityLoader->save($file, $user);
    }

    /**
     * Set dacl permissions for a folder and save the changes
     *
     * @param FolderEntity $folder
     * @param Dacl $dacl
     * @param FileEntity $file The meta-data Entity for this file
     * @return void
     */

    public function setFolderDacl(FolderEntity $folder, Dacl $dacl, UserEntity $user)
    {
        $folder->setValue('dacl', json_encode($dacl->toArray()));
        $this->entityLoader->save($folder, $user);
    }

    /**
     *  Set/change the folder owner
     *
     * @param FolderEntity $folder
     * @param string $ownerId the ID of the owner
     * @param UserEntity $user Current user making the change
     * @return void
     */
    public function setFolderOwner(FolderEntity $folder, string $ownerId, UserEntity $user)
    {
        $folder->setValue('owner_id', $ownerId);
        $this->entityLoader->save($folder, $user);
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
     * Get a child folder by name
     *
     * @param string $name The name of the folder
     * @param FolderEntity $parentFolder The folder that contains a child folder named $name
     * @return FolderEntity|null
     */
    private function getChildFolderByName(string $name, FolderEntity $parentFolder): FolderEntity|null
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
     * @return FileEntity|null
     */
    private function getChildFileByName($fileName, FolderEntity $parentFolder): FileEntity|null
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
     * Setup the requred file system for an account
     *
     * This is expensive so it should only be run in setup/update routines
     * on release. Not during requests.
     *
     * @param UserEntity $user
     * @return void
     */
    public function initializeFileSystem(UserEntity $user): void
    {
        // Start by migrating any legacy folder structures
        $this->migrateV2ToV3FileSystem($user);

        // First make sure the root directory exists
        $rootFolder = $this->getRootFolder($user);
        if (!$rootFolder) {
            $rootFolder = $this->entityLoader->create(ObjectTypes::FOLDER, $user->getAccountId());
            $rootFolder->setValue("name", "/");
            $rootFolder->setValue("f_system", true);
            $rootFolder->setValue('uname', self::UNAME_ROOT);
            $this->entityLoader->save($rootFolder, $user);
        }

        // Make sure that temp folder is not yet created
        $tempFolder = $this->getTempFolder($user);
        if (!$tempFolder) {
            // Create temp folder
            $tempFolder = $this->entityLoader->create(ObjectTypes::FOLDER, $user->getAccountId());
            $tempFolder->setValue("name", "Temp");
            $tempFolder->setValue("f_system", true);
            $tempFolder->setValue('uname', self::UNAME_TEMP);
            $this->entityLoader->save($tempFolder, $user);
        }

        $entityFolder = $this->entityLoader->getByUniqueName(
            ObjectTypes::FOLDER,
            self::UNAME_ENTITY,
            $user->getAccountId()
        );
        if (!$entityFolder) {
            // Create temp folder
            $rootFolder = $this->entityLoader->create(ObjectTypes::FOLDER, $user->getAccountId());
            $rootFolder->setValue("name", "Entity");
            $rootFolder->setValue("f_system", true);
            $rootFolder->setValue('uname', self::UNAME_ENTITY);
            $this->entityLoader->save($rootFolder, $user);
        }
    }

    /**
     * This is a temporary function that will be used to migrate the old folder structure to the new one
     *
     * @param UserEntity $user
     * @return void
     */
    private function migrateV2ToV3FileSystem(UserEntity $user)
    {
        $rootFolder = $this->entityLoader->getByUniqueName(
            ObjectTypes::FOLDER,
            self::UNAME_ROOT,
            $user->getAccountId()
        );
        if (!$rootFolder) {
            $query = new EntityQuery(ObjectTypes::FOLDER, $user->getAccountId());
            $query->where("parent_id")->equals("");
            $query->andWhere("name")->equals("/");
            $query->andWhere("f_system")->equals(true);
            $result = $this->entityIndex->executeQuery($query);
            if ($result->getNum() === 0) {
                // If there was no root at all, this is a brnd new filesystem
                // and there is nothing to migrate.
                return;
            }

            $rootFolder = $result->getEntity();
            $rootFolder->setValue('uname', self::UNAME_ROOT);
            $this->entityLoader->save($rootFolder, $user);
        }

        // Get the system folder if it exists (we used this in the legacy system)
        $systemFolder = $this->getChildFolderByName("System", $rootFolder);
        if (!$systemFolder) {
            // There is nothing else to migrate since it was all stored in the system folder
            return;
        }

        // Move the temp folder
        if (!$this->entityLoader->getByUniqueName(
            ObjectTypes::FOLDER,
            self::UNAME_TEMP,
            $user->getAccountId()
        )) {
            $tempFolder = $this->getChildFolderByName("Temp", $systemFolder);
            if ($tempFolder) {
                $tempFolder->setValue("parent_id", "");
                $tempFolder->setValue("uname", self::UNAME_TEMP);
                $this->entityLoader->save($tempFolder, $user);
            }
        }

        // Move the entity attachments folder
        if (!$this->entityLoader->getByUniqueName(
            ObjectTypes::FOLDER,
            self::UNAME_ENTITY,
            $user->getAccountId()
        )) {
            $entityFolder = $this->getChildFolderByName("Entity", $systemFolder);
            if ($entityFolder) {
                $entityFolder->setValue("parent_id", "");
                $entityFolder->setValue("uname", self::UNAME_ENTITY);
                $this->entityLoader->save($entityFolder, $user);
            }
        }
    }

    /**
     * Get the root folder entity for this account
     *
     * @param UserEntity $user The user that should own the root folder
     */
    public function getTempFolder(UserEntity $user)
    {
        return $this->entityLoader->getByUniqueName(
            ObjectTypes::FOLDER,
            self::UNAME_TEMP,
            $user->getAccountId()
        );
    }

    /**
     * Get the root folder entity for this account
     *
     * @param UserEntity $user The user that should own the root folder
     */
    public function getRootFolder(UserEntity $user)
    {
        return $this->entityLoader->getByUniqueName(
            ObjectTypes::FOLDER,
            self::UNAME_ROOT,
            $user->getAccountId()
        );
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
