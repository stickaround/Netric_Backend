<?php

/**
 * Controller for FileSystem interaction
 */

namespace Netric\Controller;

use Netric\Mvc;
use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Authentication\AuthenticationService;
use Netric\Entity\EntityLoader;
use Netric\FileSystem\FileSystem;
use Netric\FileSystem\ImageResizer;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Permissions\DaclLoader;
use Netric\Permissions\Dacl;
use Netric\Log\LogInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use DateTime;

/**
 * Class FilesController
 *
 * Handle API interactions with the FileSystem
 *
 * @package Netric\Controller
 */
class FilesController extends AbstractFactoriedController implements ControllerInterface
{
    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Service used to get the current user/account
     */
    private AuthenticationService $authService;

    /**
     * Handles the loading and saving of entities
     */
    private EntityLoader $entityLoader;

    /**
     * Handles the loading and saving of groupings
     */
    private GroupingLoader $groupingLoader;

    /**
     * Handles the loading and saving of dacl permissions
     */
    private DaclLoader $daclLoader;

    /**
     * Service used to import, export, create, update files
     */
    private FileSystem $fileSystem;

    /**
     * Resizer for images that we need to downscale or upscale
     */
    private ImageResizer $imageResizer;

    /**
     * Logger for recording what is going on
     */
    private LogInterface $log;

    /**
     * Initialize controller and all dependencies
     *
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param EntityLoader $entityLoader Handles the loading and saving of entities
     * @param GroupingLoader $groupingLoader Handles the loading and saving of groupings
     * @param DaclLoader $daclLoader Handles the loading and saving of dacl permissions
     * @param FileSystem $fileSystem Service used to import, export, create, update files
     * @param ImageResizer $imageResizer Resizer for images that we need to downscale or upscale
     * @param LogInterface $log Logger for recording what is going on
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        EntityLoader $entityLoader,
        GroupingLoader $groupingLoader,
        DaclLoader $daclLoader,
        FileSystem $fileSystem,
        ImageResizer $imageResizer,
        LogInterface $log
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->entityLoader = $entityLoader;
        $this->groupingLoader = $groupingLoader;
        $this->daclLoader = $daclLoader;
        $this->fileSystem = $fileSystem;
        $this->imageResizer = $imageResizer;
        $this->log = $log;
    }

    /**
     * Get the currently authenticated account
     *
     * @return Account
     */
    private function getAuthenticatedAccount()
    {
        $authIdentity = $this->authService->getIdentity();
        if (!$authIdentity) {
            return null;
        }

        return $this->accountContainer->loadById($authIdentity->getAccountId());
    }

    /**
     * Upload a new file to the filesystem via POST
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postUploadAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(['error' => "No authenticated account found."]);
            return $response;
        }

        $folderid = $request->getParam("folderid");
        $path = $request->getParam("path");
        $files = $request->getParam('files');
        $fileId = $request->getParam('file_id');
        $fileName = $request->getParam('file_name');

        // Check if the request was sent as a json object
        $rawBody = $request->getBody();
        if ($rawBody) {
            $body = json_decode($rawBody, true);

            if (isset($body['folderid'])) {
                $folderid = $body['folderid'];
            }

            if (isset($body['path'])) {
                $path = $body['path'];
            }

            if (isset($body['files'])) {
                $files = $body['files'];
            }

            if (isset($body['file_id'])) {
                $fileId = $body['file_id'];
            }

            if (isset($body['file_name'])) {
                $fileName = $body['file_name'];
            }
        }

        $currentUser = $currentAccount->getAuthenticatedUser();

        // If folderid has been passed the override the text path
        $folder = null;
        if ($folderid) {
            $folder = $this->fileSystem->openFolderById($folderid, $currentUser);
        } elseif ($path) {
            $folder = $this->fileSystem->openFolder($path, $currentUser, true);
        }

        // Could not create or get a parent folder. Return an error.
        if (!$folder) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(['error' => "Could not open the folder specified."]);
            return $response;
        }

        $folderPath = $folder->getFullPath();

        // List of files that just got uploaded
        $uploadedFiles = [];
        $ret = [];

        // Make sure we have the resources to upload this file
        ini_set("max_execution_time", "7200");
        ini_set("max_input_time", "7200");

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
                    $uploadedFiles[] = [
                        'name' => $file['name'][$idx],
                        'type' => $file['type'][$idx],
                        'tmp_name' => $file['tmp_name'][$idx],
                        'error' => $file['error'][$idx],
                        'size' => $file['size'][$idx],
                    ];
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
                return $this->sendOutput(
                    [
                        "error" => "Security Violation: " . $uploadedFile['tmp_name'] .
                            " was not uploaded via POST."
                    ]
                );
            }

            /*
             * Check security here to make sure the user has access to the folderPath
             * If the folder does not exist, then fileSystem->importFile will also verify
             * that the user has permission to the parent folder before creating a child folder
             */
            $folderEntity = $this->fileSystem->openFolder($folderPath, $currentUser);

            if ($folderEntity && $folderPath !== FileSystem::PATH_TEMP) {
                $dacl = $this->daclLoader->getForEntity($folderEntity, $currentUser);

                // Provide the $folderEntity when checking the if the $currentUser has access to the folder.
                if (!$dacl->isAllowed($currentUser, Dacl::PERM_FULL, $folderEntity)) {
                    // Log a warning to flag repeat offenders
                    $this->log->warning(
                        "User " . $currentUser->getName() . " tried to upload to $folderPath but does not have access"
                    );

                    // Return a 403
                    $response->setReturnCode(
                        HttpResponse::STATUS_CODE_FORBIDDEN,
                        "Access to folder $folderPath denied for user " . $currentUser->getName()
                    );
                    return $response;
                }
            }

            // Import into netric file system
            $file = $this->fileSystem->importFile(
                $currentUser,
                $uploadedFile['tmp_name'],
                $folderPath,
                $uploadedFile["name"],
                ["entity_id" => $fileId, "name" => $fileName]
            );

            if ($file) {
                $ret[] = [
                    "entity_id" => $file->getEntityId(),
                    "name" => $file->getValue("name"),
                    "ts_updated" => $file->getValue("ts_updated")
                ];
            } else {
                $ret[] = -1;
            }

            // Cleanup
            unlink($uploadedFile['tmp_name']);
        }

        $response->write($ret);
        return $response;
    }

    /**
     * PUT pass-through for uploading
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function putUploadAction(HttpRequest $request): HttpResponse
    {
        return $this->postUploadAction($request);
    }

    /**
     * Download a file
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getDownloadAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);

        // File id is a required param
        $fileId = $request->getParam("file_id");
        if (!$fileId) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_NOT_FOUND, "No file id supplied.");
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST, "No authenticated account found.");
            return $response;
        }

        $currentUser = $currentAccount->getAuthenticatedUser();

        // Load the file
        $fileEntity = $this->fileSystem->openFileById($fileId, $currentUser);

        // Let the caller know if the file does not exist
        if (!$fileEntity) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_NOT_FOUND);
            $response->write(['error' => "No file entity found."]);
            return $response;
        }

        // Make sure the current user has access
        $dacl = $this->daclLoader->getForEntity($fileEntity, $currentUser);
        if (!$dacl->isAllowed($currentUser, Dacl::PERM_VIEW, $fileEntity)) {
            $this->log->warning(
                "FilesController->getDownloadAction: User " . $currentUser->getName() .
                    " does not have permissions to " .
                    $fileEntity->getEntityId() . ":" . $fileEntity->getName()
            );

            // Return a 403
            $response->setReturnCode(
                HttpResponse::STATUS_CODE_FORBIDDEN,
                "Access to file $fileId denied for user " . $currentUser->getName()
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
                $currentUser,
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
        $disposition = ($request->getParam('disposition')) ? $request->getParam('disposition') : 'inline';
        $response->setContentDisposition($disposition, $fileEntity->getName());
        $response->setContentType($fileEntity->getMimeType());
        $response->setContentLength($fileEntity->getValue('file_size'));
        $dateLastModified = new DateTime();
        $dateLastModified->setTimestamp($fileEntity->getValue("ts_updated"));
        $response->setLastModified($dateLastModified);

        $userGroups = $this->groupingLoader->get(ObjectTypes::USER . '/groups', $currentAccount->getAccountId());

        // Allow caching if everyone has access or if this is an image
        if ($dacl->groupIsAllowed($userGroups->getByName(UserEntity::GROUP_EVERYONE), Dacl::PERM_VIEW) || $fileEntity->isImage()) {
            $response->setCacheable(
                md5($currentAccount->getName() .
                    ".file." . $fileEntity->getEntityId() .
                    '.r' . $fileEntity->getValue("revision"))
            );

            // Only allow caching on the browser for 1 day
            $response->setHeader("Cache-Control", "private, max-age=86400");
        }

        // Set netric entity header
        $response->setHeader('X-Entity', $fileEntity->getEntityId());

        // Wrap the file in a stream wrapper and return the response
        $response->setStream($this->fileSystem->openFileStream($fileEntity));
        return $response;
    }


    /**
     * Redirect to a user's profile image
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getUserImageAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST, "No authenticated account found.");
            return $response;
        }

        $currentUser = $currentAccount->getAuthenticatedUser();

        // If the user id was not passed then we will use current user's id
        $userGuid = $request->getParam("owner_id");
        if (!$userGuid) {
            $userGuid = $currentUser->getEntityId();
        }

        // Get the user entity for the user id
        $userToGetImageFor = $this->entityLoader->getEntityById($userGuid, $currentAccount->getAccountId());
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
        return $this->getDownloadAction($request);
    }
}
