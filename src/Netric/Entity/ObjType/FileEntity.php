<?php

namespace Netric\Entity\ObjType;

use Netric\FileSystem\FileStore\FileStoreFactory;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityGroupings\GroupingLoader;

/**
 * Folder for entity
 */
class FileEntity extends Entity implements EntityInterface
{
    /**
     * File handle reference
     *
     * @var resource
     */
    private $fileHandle = null;

    /**
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader The loader for a specific entity
     * @param string $publicServer The URL of the public server used to generate public links
     */
    public function __construct(
        EntityDefinition $def,
        EntityLoader $entityLoader,
        GroupingLoader $groupingLoader,
    ) {
        parent::__construct($def, $entityLoader, $groupingLoader);
    }

    /**
     * Clean-up file handle if not closed
     */
    public function __destruct()
    {
        if ($this->fileHandle) {
            @fclose($this->fileHandle);
            $this->fileHandle = null;
        }
    }

    /**
     * Called right before the endity is purged (hard delete)
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeDeleteHard(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        $fileStore = $serviceLocator->get(FileStoreFactory::class);

        // When this file gets purged we should delete the raw data from the fileStore
        $fileStore->deleteFile($this);
    }

    /**
     * Get a file handle if set
     *
     * @return resource
     */
    public function getFileHandle()
    {
        return $this->fileHandle;
    }

    /**
     * Set a file handle
     *
     * @var resource $fileHandle
     */
    public function setFileHandle($fileHandle)
    {
        $this->fileHandle = $fileHandle;
    }

    /**
     * Get the file type from the extension
     *
     * @return string
     */
    public function getType()
    {
        $ext = substr($this->getValue("name"), strrpos($this->getValue("name"), '.') + 1);
        return strtolower($ext);
    }

    /**
     * Get a mime type from the extension
     */
    public function getMimeType()
    {
        $type = $this->getType();

        switch ($type) {
            case 'jpg':
            case 'jpeg':
                return "image/jpeg";
            case 'png':
                return "image/png";
            case 'mp4':
                return "video/mp4";
            case 'mov':
                return "video/quicktime";
            case 'avi':
                return "video/x-msvideo";
            case 'wmv':
                return "video/x-ms-wmv";
            case '3gp':
                return "video/3gpp";
            case 'mkv':
                return "video/x-matroska";
            default:
                return "application/octet-stream";
        }
    }

    /**
     * Check if this file is an image
     *
     * @return bool
     */
    public function isImage(): bool
    {
        $type = $this->getType();
        switch ($type) {
            case 'jpg':
            case 'jpeg':
            case 'png':
                return true;
        }

        return false;
    }
}
