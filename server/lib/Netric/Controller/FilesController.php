<?php
/**
 * Controller for FileSystem interactoin
 */
namespace Netric\Controller;

use Netric\Mvc;
use Netric\FileSystem\FileSystem;

/**
 * Class FilesController
 *
 * Handle API interactions with the FileSystem
 *
 * @package Netric\Controller
 */
class FilesController extends Mvc\AbstractController
{
    /**
     * FileSystem instance
     *
     * @var FileSystem
     */
    private $fileSystem = null;

    /**
     * Path to local data directory for storing files
     *
     * @var string
     */
    private $dataPath = null;

    /**
     * Override initialization
     */
    protected function init()
    {
        // Get ServiceManager for the account
        $sl = $this->account->getServiceManager();

        // Get the FileSystem service
        $this->fileSystem = $sl->get("Netric/FileSystem/FileSystem");

        // Set the local dataPath from the system config service
        $config = $sl->get("Config");
        $this->dataPath = $config->data_path;
    }

    /**
     * Upload a new file to the filesystem via POST
     *
     * @return array Response
     */
    public function upload()
    {
        $request = $this->getRequest();

        // Make sure we have the resources to upload this file
		ini_set("max_execution_time", "7200");
		ini_set("max_input_time", "7200");

		$folder = null;
		$ret = array();

		// If folderid has been passed the override the text path
		if ($request->getParam('folderid'))
            $folder = $this->fileSystem->openFolderById($request->getParam('folderid'));
        else if ($request->getParam('path'))
            $folder = $this->fileSystem->openFolder($request->getParam('path'), true);

        // Could not create or get a parent folder. Return an error.
		if (!$folder)
            return $this->setOutput(array("error"=>"Could not open the folder specified"));

        $folderPath = $folder->getFullPath();

        // Process each file
        $files = $request->getParam('files');

        /**
         * Check to see if input multiple was set (or multiple files were uploaded with the same name).
         * @see http://php.net/manual/en/features.file-upload.multiple.php for more information
         */
        if(isset($files['files']))
        {
            $uploadedFiles = array();

            /**
             * Map the files and mimic the structure of the normal $_FILES
             * $type contains the 'name', 'type', 'tmp_name', 'size' indexes
             */
            foreach($files['files'] as $type=>$fileData)
            {
                /**
                 * Get the file uploaded data.
                 * $data contains the value of filename, filetype, tmp_name, and filesize
                 */
                foreach($fileData as $idx=>$data)
                {
                    $uploadedFiles[$idx][$type] = $data;
                }
            }

            $files = $uploadedFiles;
        }

        foreach($files as $uploadedFile)
        {
            /*
             * Make sure that the file was uploaded via HTTP_POST. This is useful to help
             * ensure that a malicious user hasn't tried to trick the script into working
             * on files upon which it should not be working--for instance, /etc/passwd.
             *
             * However, we will need to bypass this for unit tests which will be managed
             * with $this->testMode and will be set in the unit test and never anywhere else.
             */
            if (!is_uploaded_file($uploadedFile['tmp_name']) && !$this->testMode)
            {
                return $this->setOutput(
                    array(
                        "error"=>"Security Violation: " . $uploadedFile['tmp_name'] .
                        " was not uploaded via POST."
                    )
                );
            }

            // Import into netric file system
            $file = $this->fileSystem->importFile(
                $uploadedFile['tmp_name'], $folderPath, $uploadedFile["name"]
            );

            if ($file)
            {
                $ret[] = array(
                    "id" => $file->getId(),
                    "name" => $file->getValue("name"),
                    "ts_updated" => $file->getValue("ts_updated")
                );
            }
            else
            {
                $ret[] = -1;
            }

            // Cleanup
            unlink($uploadedFile['tmp_name']);
        }

        return $this->sendOutput($ret);
    }
}