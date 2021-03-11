<?php

namespace NetricTest\Controller;

use PHPUnit\Framework\TestCase;
use Netric\Request\HttpRequest;
use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Authentication\AuthenticationService;
use Netric\Authentication\AuthenticationIdentity;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityGroupings\EntityGroupings;
use Netric\EntityGroupings\Group;
use Netric\Permissions\DaclLoader;
use Netric\Permissions\Dacl;
use Netric\Log\LogInterface;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\FolderEntity;
use Netric\Entity\ObjType\FileEntity;
use Netric\FileSystem\ImageResizer;
use Netric\FileSystem\FileSystem;
use Netric\Controller\FilesController;

use Ramsey\Uuid\Uuid;

/**
 * Test calling the files controller
 * @group integration
 */
class FilesControllerTest extends TestCase
{
    /**
     * Initialized controller with mock dependencies
     */
    private FilesController $filesController;

    /**
     * Dependency mocks
     */
    private Account $mockAccount;
    private EntityLoader $mockEntityLoader;
    private AuthenticationService $mockAuthService;
    private EntityDefinitionLoader $mockEntityDefinitionLoader;
    private GroupingLoader $mockGroupingLoader;
    private DaclLoader $mockDaclLoader;
    private FileSystem $fileSystem;
    private ImageResizer $imageResizer;
    private LogInterface $mockLog;

    protected function setUp(): void
    {
        // Create mocks
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockEntityDefinitionLoader = $this->createMock(EntityDefinitionLoader::class);
        $this->mockGroupingLoader = $this->createMock(GroupingLoader::class);
        $this->mockDaclLoader = $this->createMock(DaclLoader::class);
        $this->fileSystem = $this->createMock(FileSystem::class);
        $this->imageResizer = $this->createMock(ImageResizer::class);
        $this->mockLog = $this->createMock(LogInterface::class);

        // Provide identity for mock auth service
        $this->mockAuthService = $this->createMock(AuthenticationService::class);
        $ident = new AuthenticationIdentity('blahaccount', 'blahuser');
        $this->mockAuthService->method('getIdentity')->willReturn($ident);

        // Return mock authenticated account
        $this->mockAccount = $this->createStub(Account::class);
        $this->mockAccount->method('getAccountId')->willReturn(Uuid::uuid4()->toString());

        $accountContainer = $this->createMock(AccountContainerInterface::class);
        $accountContainer->method('loadById')->willReturn($this->mockAccount);

        // Create the controller with mocks
        $this->filesController = new FilesController(
            $accountContainer,
            $this->mockAuthService,
            $this->mockEntityLoader,
            $this->mockGroupingLoader,
            $this->mockDaclLoader,
            $this->fileSystem,
            $this->imageResizer,
            $this->mockLog
        );
        $this->filesController->testMode = true;
    }

    /**
     * Try uploading a file into the FileSystem through the controller
     */
    public function testUpload()
    {
        $requestData = [
            'folderid' => Uuid::uuid4()->toString(),
            'path' => '/',
            'files' => [],
            'file_id' => Uuid::uuid4()->toString(),
            'file_name' => 'test_file.txt',
        ];

        $daclPermissions = [
            'view' => true,
            'edit' => true,
            'delete' => true
        ];

        // Create test folder entity
        $mockFolderEntity = $this->createMock(FolderEntity::class);

        // Create test file entity
        $mockFileEntity = $this->createMock(FileEntity::class);

        // Mock the fileSystem service which is used to get file/folder entity
        $this->fileSystem->method('openFolder')->willReturn($mockFolderEntity);
        $this->fileSystem->method('importFile')->willReturn($mockFileEntity);
        $this->fileSystem->method('openFolderById')->willReturn($mockFolderEntity);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        $mockDacl->method('getUserPermissions')->willReturn($daclPermissions);
        $mockDacl->method('toArray')->willReturn([]);

        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure postAuthenticateAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode($requestData));
        $response = $this->filesController->postUploadAction($request);

        // It should only return the id of the default view
        $this->assertEquals([], $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in uploading a file into the FileSystem through the controller
     */
    public function testUploadActionCatchingErrors()
    {
        $requestData = [
            'folderid' => null,
            'path' => '/',
            'files' => [],
            'file_id' => null,
            'file_name' => 'test_file.txt',
        ];

        // Make sure postAuthenticateAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode($requestData));
        $response = $this->filesController->postUploadAction($request);

        // It should only return the id of the default view
        $this->assertEquals(["error" => "Could not open the folder specified."], $response->getOutputBuffer());
    }

    /**
     * Try downloading a file
     */
    public function testGetDownloadAction()
    {
        $groupId = Uuid::uuid4()->toString();
        $groupDetails = [
            "group_id" => $groupId,
            "name" => 'Test Group',
            "f_system" => true,
            "sort_order" => 1,
            "commit_id" => 1
        ];

        $daclPermissions = [
            'view' => true,
            'edit' => true,
            'delete' => true
        ];

        // Create test folder entity
        $mockFolderEntity = $this->createMock(FolderEntity::class);

        // Create test file entity
        $fileId = Uuid::uuid4()->toString();
        $mockFileEntity = $this->createMock(FileEntity::class);
        $mockFileEntity->method('getEntityId')->willReturn($fileId);

        // Mock the fileSystem service which is used to get file/folder entity
        $this->fileSystem->method('openFileById')->willReturn($mockFileEntity);

        // Create test dacl permission for this task
        $mockDacl = $this->createMock(Dacl::class);
        $mockDacl->method('isAllowed')->willReturn(true);
        $mockDacl->method('getUserPermissions')->willReturn($daclPermissions);
        $mockDacl->method('toArray')->willReturn([]);

        // Create the group for testing
        $mockEntityGroup = $this->createMock(Group::class);
        $mockEntityGroup->method('toArray')->willReturn($groupDetails);

        // Create the entity groupings for testing
        $mockEntityGroupings = $this->createMock(EntityGroupings::class);
        $mockEntityGroupings->method('getByGuidOrGroupId')->willReturn($mockEntityGroup);
        $mockEntityGroupings->method('toArray')->willReturn([$groupDetails]);

        // Mock the grouping loader service which is used to get the entity groupings
        $this->mockGroupingLoader->method('get')->willReturn($mockEntityGroupings);

        // Mock the dacl loader service which is used to load the dacl permission
        $this->mockDaclLoader->method('getForEntity')->willReturn($mockDacl);

        // Make sure postAuthenticateAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('file_id', $fileId);
        $request->setParam('max_width', 40);
        $request->setParam('max_height', 40);
        $response = $this->filesController->getDownloadAction($request);

        // It should only return the id of the default view
        $this->assertEquals($fileId, $response->getHeaders()['X-Entity']);
    }
}
