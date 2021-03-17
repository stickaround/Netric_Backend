<?php
/*
 * Interface definition for a file system data mapper
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\FileSystem\FileStore;

use Netric\Error;
use Netric\Entity\ObjType\FileEntity;
use Netric\Entity\ObjType\UserEntity;

/**
 * Define
 */
interface FileStoreInterface extends Error\ErrorAwareInterface
{
    /**
     * Read and return numBypes (or all) of a file
     *
     * @param FileEntity $file The meta-data Entity for this file
     * @param null $numBytes Number of bytes, if null then return while file
     * @param null $offset Starting offset, defaults to current pointer
     * @return mixed
     */
    public function readFile(FileEntity $file, $numBytes = null, $offset = null);

    /**
     * Write data to a file
     *
     * @param FileEntity $file The meta-data Entity for this file
     * @param mixed $dataOrStream $data Binary data to write or a stream resource
     * @param UserEntity The user that is uploading the file
     * @return int number of bytes written
     */
    public function writeFile(FileEntity $file, $dataOrStream, UserEntity $user);

    /**
     * Get a file stream to read from
     *
     * @param FileEntity $file The meta-data Entity for this file
     * @return resource
     */
    public function openFileStream(FileEntity $file);

    /**
     * Upload a file to the data store
     *
     * @param FileEntity $file Meta-data Entity for the file
     * @param $localPath Path of a local file
     * @param UserEntity $user The user uploading the file
     * @return true on success, false on failure
     */
    public function uploadFile(FileEntity $file, $localPath, UserEntity $user);

    /**
     * Delete a file from the DataMapper
     *
     * @param FileEntity $file The file to purge data for
     * @param int $revision If set then only delete data for a specific revision
     * @return mixed
     */
    public function deleteFile(FileEntity $file, $revision = null);

    /**
     * Check to see if a file exists in the store
     *
     * @param FileEntity $file The file to purge data for
     * @return bool true if it exists, otherwise false
     */
    public function fileExists(FileEntity $file);
}
