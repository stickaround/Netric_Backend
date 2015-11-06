<?php
/**
 * FileSystem service
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\FileSystem;

use Netric\EntityQuery;
use Netric\Entity\ObjType\User;
use Netric\Entity\ObjType\Folder;
use Netric\EntityLoader;
use Netric\Entity\DataMapperInterface;

/**
 * Create a file system service
 *
 * @package Netric\FileSystem
 */
class FileSystem
{
    /**
     * Index to query entities
     *
     * @var EntityQuery\Index\IndexInterface
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
     * @var DataMapperInterface
     */
    private $entityDataMapper = null;

    /**
     * Current user
     *
     * @var User
     */
    private $user = null;

    /**
     * The root folder for this account
     *
     * @var Folder
     */
    private $rootFolder = null;

    public function importFile($localPath, $remotePath)
    {

    }

    public function openFile($path)
    {

    }

    /**
     * Get a file by id
     *
     * @param $fid Unique id of the file
     * @return File
     */
    public function openFileById($fid)
    {
        return $this->entityLoader->get("file", $fid);
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
        if (!$createIfMissing && ($path == "%tmp%" || $path == "%userdir%" || $path == "%home%"))
            $createIfMissing = true;

        $folders = $this->splitPathToFolderArray($path, $createIfMissing);

        if ($folders)
        {
            return array_pop($folders);
        }
        else
        {
            return null;
        }
    }

    /**
     * Get a folder by id
     *
     * @param $folderId
     * @return Folder
     */
    public function openFolderById($folderId)
    {
        return $this->entityLoader->get("folder", $folderId);
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
     * Convert number of bytes into a human readable form
     *
     * @param integer $size The size in bytes
     * @return string The human readable form of the size in bytes
     */
    public function getHumanSize($size)
    {
        if ($size >= 1000000000000)
            return round($size/1000000000000, 1) . "TB";
        if ($size >= 1000000000)
            return round($size/1000000000, 1) . "G";
        if ($size >= 1000000)
            return round($size/1000000, 1) . "M";
        if ($size >= 1000)
            return round($size/1000, 0) . "K";
        if ($size < 1000)
            return $size + "B";
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
         * Translate any variables in path like %tmp% and %userdir% to actual paths
         * and also normalize everything relative to root so /my/path will return:
         * my/path since root is always implied.
         */
        $path = $this->substituteVariables($path);

        // Parse folder path
        $folderNames = explode("/", $path);
        $folders = array($this->rootFolder);
        $lastFolder = $this->rootFolder;
        foreach ($folderNames as $nextFolderName)
        {
            $nextFolder = $this->getChildFolderByName($nextFolderName, $lastFolder);

            // If the folder exists add it and continue
            if ($nextFolder->getId())
            {
                $folders[] = $lastFolder;
            }
            else if ($createIfMissing)
            {
                // TODO: Check permissions to see if we have access to create

                $nextFolder = $this->entityLoader->create("folder");
                $nextFolder->setValue("name", $nextFolderName);
                $nextFolder->setValue("parent_id", $lastFolder->getId());
                $nextFolder->setValue("owner_id", $this->user->getId());
                $this->entityDataMapper->save($nextFolder);
            }
            else
            {
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

        $retval = str_replace("%tmp%", "/System/Temp", $retval);

        // Get a user's home directory
        $retval = str_replace("%userdir%", "/System/Users/".$this->user->getId(), $retval);
        $retval = str_replace("%home%", "/System/Users/".$this->user->getId(), $retval);

        // Get email attechments directory for a user
        $retval = str_replace(
            "%emailattachments%",
            "/System/Users/".$this->user->getId() . "/System/Email Attachments",
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
     * @param Folder $parentFolder The folder that contains a child folder named $name
     * @return Folder|null
     */
    private function getChildFolderByName($name, Folder $parentFolder)
    {
        $query = new EntityQuery("folder");
        $query->where("parent_id")->equals($parentFolder->getId());
        $query->andWhere("name")->equals($name);
        $result = $this->entityIndex->executeQuery($query);
        if ($result->getNum())
        {
            return $result->getEntity();
        }

        return null;
    }

    /**
     * Get the root folder entity for this account
     */
    private function setRootFolder()
    {
        $query = new EntityQuery("folder");
        $query->where("parent_id")->equals("");
        $query->andWhere("name")->equals("/");
        $query->andWhere("f_system")->equals(true);

        $result = $this->entityIndex->executeQuery($query);
        if ($result->getNum())
        {
            $this->rootFolder = $result->getEntity();
        }
        else
        {
            // Create root folder
            $rootFolder = $this->entityLoader->create("folder");
            $rootFolder->setValue("name", "/");
            $rootFolder->setValue("owner_id", $this->user->getId());
            $rootFolder->setValue("f_system", true);
            $this->entityDataMapper->save($rootFolder);

            // Now set it for later reference
            $this->rootFolder = $rootFolder;
        }
    }
}