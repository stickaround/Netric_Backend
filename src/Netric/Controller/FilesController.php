<?php

/**
 * Controller for FileSystem interactoin
 */
namespace Netric\Controller;

use Netric\Entity\ObjType\UserEntity;
use Netric\Mvc;
use Netric\Entity\EntityLoaderFactory;
use Netric\FileSystem\FileSystemFactory;
use Netric\FileSystem\ImageResizerFactory;
use Netric\FileSystem\FileStreamWrapper;
use Netric\Application\Response\HttpResponse;
use Netric\Permissions\DaclLoader;
use Netric\Permissions\Dacl;
use DateTime;
use Netric\Config\ConfigFactory;

/**
 * Class FilesController
 *
 * Handle API interactions with the FileSystem
 *
 * @package Netric\Controller
 */
class FilesController extends Mvc\AbstractAccountController
{
    /**
     * FileSystem instance
     *
     * @var FileSystem
     */
    private $fileSystem = null;

    /**
     * Resizer for images that we need to downscale or upscale
     *
     * @var ImageResizer
     */
    private $imageResizer = null;

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
        $this->fileSystem = $sl->get(FileSystemFactory::class);

        // Set the local dataPath from the system config service
        $config = $sl->get(ConfigFactory::class);
        $this->dataPath = $config->data_path;

        // Set resizer if we are working with images
        $this->imageResizer = $sl->get(ImageResizerFactory::class);
    }

    /**
     * Override to allow anonymous users to access this controller for authentication
     *
     * @return \Netric\Permissions\Dacl
     */
    public function getAccessControlList()
    {
        $dacl = new Dacl();

        // By default allow authenticated users to access a controller
        $dacl->allowGroup(UserEntity::GROUP_EVERYONE);

        return $dacl;
    }

    /**
     * Upload a new file to the filesystem via POST
     *
     * @return array array|Response
     */
    public function postUploadAction()
    {
        $request = $this->getRequest();
        $log = $this->account->getApplication()->getLog();

        // Make sure we have the resources to upload this file
        ini_set("max_execution_time", "7200");
        ini_set("max_input_time", "7200");


        $folder = null;
        $ret = array();

        // If folderid has been passed the override the text path
        if ($request->getParam('folderid')) {
            $folder = $this->fileSystem->openFolderById($request->getParam('folderid'));
        } elseif ($request->getParam('path')) {
            $folder = $this->fileSystem->openFolder($request->getParam('path'), true);
        }

        // Could not create or get a parent folder. Return an error.
        if (!$folder) {
            return $this->setOutput(array("error" => "Could not open the folder specified"));
        }

        $folderPath = $folder->getFullPath();

        // Process each file
        $files = $request->getParam('files');

        // List of files that just got uploaded
        $uploadedFiles = array();

        /**
         * When a file is uploaded it can be sent as 'input_name' or as 'input_name[]'
         */
        foreach ($files as $file) {
            /**
             * Check to see if input multiple was set (or multiple files were uploaded with the
             * same name) which will be represented as:
             * array(
             *  'filename' => array('file1name', 'file2name'),
             *  'filetype' => array('file1type', 'file2type'),
             *  'tmp_name' => array('file1tmp', 'file2tmp'),
             *  'filesize' => array('100', '200'),
             * );
             *
             * This is really a poor design, but unfortunately it's how PHP handles multiple file
             * updates. We just convert it to a more sane format below where each file is it's own []
             * and the below code does not care what the form name is for the uplaoded file.
             *
             * @see http://php.net/manual/en/features.file-upload.multiple.php for more information
             */
            if (is_array($file['name'])) {
                foreach ($file['name'] as $idx => $filename) {
                    $uploadedFiles[] = array(
                        'name' => $file['name'][$idx],
                        'type' => $file['type'][$idx],
                        'tmp_name' => $file['tmp_name'][$idx],
                        'error' => $file['error'][$idx],
                        'size' => $file['size'][$idx],
                    );
                }
            } else {
                // Standard single file upload
                $uploadedFiles[] = $file;
            }
        }

        foreach ($uploadedFiles as $uploadedFile) {
            /*
             * Make sure that the file was uploaded via HTTP_POST. This is useful to help
             * ensure that a malicious user hasn't tried to trick the script into working
             * on files upon which it should not be working--for instance, /etc/passwd.
             *
             * However, we will need to bypass this for unit tests which will be managed
             * with $this->testMode and will be set in the unit test and never anywhere else.
             */
            if (!is_uploaded_file($uploadedFile['tmp_name']) && !$this->testMode) {
                return $this->setOutput(
                    array(
                        "error" => "Security Violation: " . $uploadedFile['tmp_name'] .
                            " was not uploaded via POST."
                    )
                );
            }

            /*
             * Check security here to make sure the user has access to the folderPath
             * If the folder does not exist, then fileSystem->importFile will also verify
             * that the user has permission to the parent folder before creating a child folder
             */
            $folderEntity = $this->fileSystem->openFolder($folderPath);
            if ($folderEntity) {
                $user = $this->account->getUser();
                $daclLoader = $this->account->getServiceManager()->get(DaclLoader::class);
                $dacl = $daclLoader->getForEntity($folderEntity);
                if (!$dacl->isAllowed($user)) {
                    // Log a warning to flag repeat offenders
                    $log->warning(
                        "User " . $user->getName() . " tried to upload to $folderPath but does not have access"
                    );

                    // Return a 403
                    $response = new HttpResponse($request);
                    $response->setReturnCode(
                        HttpResponse::STATUS_CODE_FORBIDDEN,
                        "Access to folder $folderPath denied for user " . $user->getName()
                    );
                    return $response;
                }
            }

            $fileId = $request->getParam('file_id');
            $fileName = $request->getParam('file_name');

            // Import into netric file system
            $file = $this->fileSystem->importFile(
                $uploadedFile['tmp_name'],
                $folderPath,
                $uploadedFile["name"],
                array("id" => $fileId, "name" => $fileName)
            );

            if ($file) {
                $ret[] = array(
                    "id" => $file->getId(),
                    "name" => $file->getValue("name"),
                    "ts_updated" => $file->getValue("ts_updated")
                );
            } else {
                $ret[] = -1;
            }

            // Cleanup
            unlink($uploadedFile['tmp_name']);
        }

        return $this->sendOutput($ret);
    }

    /**
     * PUT pass-through for uploading
     */
    public function putUploadAction()
    {
        return $this->postUploadAction();
    }

    /**
     * Download a file
     *
     * @return HttpResponse
     */
    public function getDownloadAction()
    {
        $request = $this->getRequest();
        $fileId = $request->getParam("file_id");
        $user = $this->account->getUser();
        $log = $this->account->getApplication()->getLog();

        $response = new HttpResponse($request);

        // File id is a required param
        if (!$fileId) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_NOT_FOUND, "No file id supplied");
            return $response;
        }

        // Load the file
        $fileEntity = $this->fileSystem->openFileById($fileId);

        // Let the caller know if the file does not exist
        if (!$fileEntity) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_NOT_FOUND);
            return $response;
        }

        // Make sure the current user has access
        $daclLoader = $this->account->getServiceManager()->get(DaclLoader::class);
        $dacl = $daclLoader->getForEntity($fileEntity);
        if (!$dacl->isAllowed($user)) {
            $log->warning(
                "FilesController->getDownloadAction: User " . $user->getName() .
                    " does not have permissions to " .
                    $fileEntity->getId() . ":" . $fileEntity->getName()
            );

            // Return a 403
            $response = new HttpResponse($request);
            $response->setReturnCode(
                HttpResponse::STATUS_CODE_FORBIDDEN,
                "Access to file $fileId denied for user " . $user->getName()
            );
            return $response;
        }

        // Handle image resizing
        $maxWidth = $request->getParam('max_width');
        $maxHeight = $request->getParam('max_height');
        if (($maxWidth || $maxHeight) && ($fileEntity->getType() === 'png' || $fileEntity->getType() === 'jpg')) {
            // Change null max_* to -1 so that the resizer knows to not try and downscale to 0
            if (!$maxWidth) {
                $maxWidth = -1;
            }
            if (!$maxHeight) {
                $maxHeight = -1;
            }

            // Resize the image and return the new (temp) fileEntity
            $resizedFileEntity = $this->imageResizer->resizeFile(
                $fileEntity,
                $maxWidth,
                $maxHeight
            );

            // If we were able to resize the entity then return it instead
            if ($resizedFileEntity) {
                $fileEntity = $resizedFileEntity;
            }
        }

        // Set standard file headers
        $response->setContentDisposition('inline', $fileEntity->getName());
        $response->setContentType($fileEntity->getMimeType());
        $response->setContentLength($fileEntity->getValue('file_size'));
        $dateLastModified = new DateTime();
        $dateLastModified->setTimestamp($fileEntity->getValue("ts_updated"));
        $response->setLastModified($dateLastModified);

        // Allow caching if everyone has access
        if ($dacl->groupIsAllowed(UserEntity::GROUP_EVERYONE, Dacl::PERM_VIEW)) {
            $response->setCacheable(
                md5($this->account->getName() .
                    ".file." . $fileEntity->getId() .
                    '.r' . $fileEntity->getValue("revision"))
            );
        }

        // Set netric entity header
        $response->setHeader('X-Entity', $fileEntity->getObjRef());

        // Wrap the file in a stream wrapper and return the response
        $response->setStream(FileStreamWrapper::open($this->fileSystem, $fileEntity));
        return $response;
    }

    /**
     * Redirect to a user's profile image
     *
     * @return HttpResponse
     */
    public function getUserImageAction()
    {
        $request = $this->getRequest();
        $response = new HttpResponse($request);
        $userId = $request->getParam("user_id");

        // If the user id was not passed we use current
        if (!$userId) {
            $userId = $this->account->getUser()->getId();
        }

        // We will need the entityLoader to load up a user
        $serviceManager = $this->getApplication()->getAccount()->getServiceManager();
        $entiyLoader = $serviceManager->get(EntityLoaderFactory::class);

        // Get the user entity for the user id
        $userToGetImageFor = $entiyLoader->get('user', $userId);
        $imageId = ($userToGetImageFor) ? $userToGetImageFor->getValue('image_id') : null;

        // 404 if the user was not found or there was no image_id uploaded
        if (!$imageId) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_NOT_FOUND);
            return $response;
        }

        // Set the request file id
        $request->setParam('file_id', $imageId);

        /*
         * Now do a backend redirect where no response is sent to the browser
         * but the newly modified request will be sent to $this->getDownloadAction()
         * becaues we want to preserve caching with the user profile links
         */
        return $this->getDownloadAction();
    }
}
