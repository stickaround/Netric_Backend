<?php

/**
 * Test the FileSystem service
 */

namespace NetricTest\FileSystem;

use Netric\FileSystem\FileSystem;
use Netric\Entity\DataMapper\EntityDataMapperInterface;
use Netric\EntityQuery\EntityQuery;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType;
use Netric\FileSystem\FileSystemFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityDefinition\ObjectTypes;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;

/**
 * @group integration
 */
class FileSystemTest extends TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Get FileSystem
     *
     * @var FileSystem
     */
    private $fileSystem = null;

    /**
     * Entity DataMapper
     *
     * @var EntityDataMapperInterface
     */
    private $dataMapper = null;

    /**
     * Entity loader
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Entity Query index for finding things
     *
     * @var EntityQuery\Index\IndexInterface
     */
    private $entityIndex = null;

    /**
     * Test folders to cleanup
     *
     * @var ObjType\Folder[]
     */
    private $testFolders = [];

    /**
     * Test files to cleanup
     *
     * @var ObjType\File[]
     */
    private $testFiles = [];

    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();

        $this->fileSystem = $sl->get(FileSystemFactory::class);
        $this->dataMapper = $sl->get(EntityDataMapperFactory::class);
        $this->entityLoader = $sl->get(EntityLoaderFactory::class);
        $this->entityIndex = $sl->get(IndexFactory::class);
    }

    protected function tearDown(): void
    {
        // Clean-up test files
        foreach ($this->testFiles as $file) {
            $this->fileSystem->deleteFile($file, $this->account->getAuthenticatedUser(), true);
        }

        // Delete all test folders in reverse order - in case they are children of each other
        $folders = array_reverse($this->testFolders);
        foreach ($folders as $folder) {
            $this->fileSystem->deleteFolder($folder, $this->account->getAuthenticatedUser(), true);
        }
    }

    private function queueFolderForCleanup($folder)
    {
        if ($folder->getValue('name') == '/') {
            throw new \Exception("You cannot delete root");
        }

        $this->testFolders[] = $folder;
    }

    /**
     * Test to make sure the root folder is set on construction
     */
    public function testGetRootFolder()
    {
        $this->assertNotNull($this->fileSystem->getRootFolder($this->account->getSystemUser()));
    }

    /**
     * Test opening an existing folder by path
     */
    public function testOpenFolder()
    {
        // Create some test folders
        $rootFolder = $this->fileSystem->getRootFolder($this->account->getSystemUser());

        // Cleanup first
        if ($this->fileSystem->openFolder("/testOpenSub", $this->account->getAuthenticatedUser())) {
            $this->fileSystem->deleteFolder($this->fileSystem->openFolder("/testOpenSub"), $this->account->getAuthenticatedUser());
        }

        // Create /testOpenSub
        $subFolder = $this->entityLoader->create(ObjectTypes::FOLDER, $this->account->getAccountId());
        $subFolder->setValue("name", "testOpenSub");
        $subFolder->setValue("parent_id", $rootFolder->getEntityId());
        $this->dataMapper->save($subFolder, $this->account->getSystemUser());
        $this->queueFolderForCleanup($subFolder);

        // Create /testOpenSub/Child
        $childFolder = $this->entityLoader->create(ObjectTypes::FOLDER, $this->account->getAccountId());
        $childFolder->setValue("name", "Child");
        $childFolder->setValue("parent_id", $subFolder->getEntityId());
        $this->dataMapper->save($childFolder, $this->account->getSystemUser());
        $this->queueFolderForCleanup($childFolder);

        // Try opening /testOpenSub
        $openedFolder = $this->fileSystem->openFolder("/testOpenSub", $this->account->getAuthenticatedUser());
        $this->assertNotNull($openedFolder);
        $this->assertEquals($openedFolder->getEntityId(), $subFolder->getEntityId());
        $this->assertEquals(
            $openedFolder->getValue("name"),
            $subFolder->getValue("name")
        );

        // Try opening /testOpenSub/Child
        $openedFolder = $this->fileSystem->openFolder("/testOpenSub/Child", $this->account->getAuthenticatedUser());
        $this->assertNotNull($openedFolder);
        $this->assertEquals($openedFolder->getEntityId(), $childFolder->getEntityId());
        $this->assertEquals(
            $openedFolder->getValue("name"),
            $childFolder->getValue("name")
        );
    }

    /**
     * Make sure that we can open a new folder and create it if missing
     */
    public function testOpenFolderCreateMissing()
    {
        $origChildId = null;

        // First delete test path if it exists
        $folder = $this->fileSystem->openFolder("/testOpenSubCreate/Child", $this->account->getAuthenticatedUser());
        if ($folder) {
            $this->dataMapper->delete($folder, $this->account->getAuthenticatedUser());
        }
        $folder = $this->fileSystem->openFolder("/testOpenSubCreate", $this->account->getAuthenticatedUser());
        if ($folder) {
            $origChildId = $folder->getEntityId();
            $this->dataMapper->delete($folder, $this->account->getAuthenticatedUser());
        }

        // Now open the folder with create flag (second)
        $openedFolder = $this->fileSystem->openFolder("/testOpenSubCreate/Child", $this->account->getAuthenticatedUser(), true);
        $this->assertNotNull($openedFolder);
        $this->assertNotEquals($origChildId, $openedFolder->getEntityId());

        // Stash for cleanup
        $this->queueFolderForCleanup($this->fileSystem->openFolder("/testOpenSubCreate", $this->account->getAuthenticatedUser()));
        $this->queueFolderForCleanup($this->fileSystem->openFolder("/testOpenSubCreate/Child", $this->account->getAuthenticatedUser()));
    }

    /**
     * Test to make sure we get a folder by id
     */
    public function testOpenFolderById()
    {
        $testFolder = $this->fileSystem->openFolder("/testOpenFolderById", $this->account->getAuthenticatedUser(), true);
        $this->queueFolderForCleanup($testFolder);
        $folderId = $testFolder->getEntityId();

        // Try to re-open the above folder by id
        $sameFolder = $this->fileSystem->openFolderById($folderId, $this->account->getAuthenticatedUser());
        $this->assertNotNull($sameFolder);
        $this->assertEquals($folderId, $sameFolder->getEntityId());
    }

    /**
     * Test importing a new file
     */
    public function testImportFile()
    {
        $fileToImport = __DIR__ . "/FileStore/fixtures/file-to-upload.txt";

        // Test importing a local file
        $importedFile = $this->fileSystem->importFile($this->account->getAuthenticatedUser(), $fileToImport, "/testImportFile");
        $this->assertNotNull($importedFile);
        $this->assertEquals("file-to-upload.txt", $importedFile->getValue("name"));
        $this->assertEquals(filesize($fileToImport), $importedFile->getValue("file_size"));

        // Queue files for cleanup
        $this->testFiles[] = $importedFile;
        $this->queueFolderForCleanup($this->fileSystem->openFolder("/testImportFile", $this->account->getAuthenticatedUser()));
    }

    /**
     * Test importing a new file with existing file entity
     */
    public function testImportFileExisting()
    {
        $fileToImport = __DIR__ . "/FileStore/fixtures/file-to-upload-existing.txt";

        // Create an existing file
        $file = $this->entityLoader->create("file", $this->account->getAccountId());
        $file->setValue("name", "newFile.jpg");
        $this->entityLoader->save($file, $this->account->getSystemUser());
        $this->testFiles[] = $file;

        // Test importing a local file
        $importedFile = $this->fileSystem->importFile(
            $this->account->getAuthenticatedUser(),
            $fileToImport,
            "/testImportFile",
            "",
            ["entity_id" => $file->getValue("entity_id"), "name" => "myupdatedfile.jpg"]
        );

        $this->assertNotNull($importedFile);
        $this->assertEquals("myupdatedfile.jpg", $importedFile->getValue("name"));
        $this->assertEquals(filesize($fileToImport), $importedFile->getValue("file_size"));

        // Queue files for cleanup
        $this->testFiles[] = $importedFile;
        $this->queueFolderForCleanup($this->fileSystem->openFolder("/testImportFile", $this->account->getAuthenticatedUser()));
    }

    /**
     * Test opening a file by unique id
     */
    public function testOpenFileById()
    {
        $fileToImport = __DIR__ . "/FileStore/fixtures/file-to-upload.txt";
        $importedFile = $this->fileSystem->importFile($this->account->getAuthenticatedUser(), $fileToImport, "/testOpenFileById");

        // Test opening the file straight from the fileSystem by id
        $openedFile = $this->fileSystem->openFileById($importedFile->getEntityId(), $this->account->getAuthenticatedUser());
        $this->assertNotNull($openedFile);
        $this->assertFalse(empty($openedFile->getEntityId()));
        $this->assertEquals($importedFile->getEntityId(), $openedFile->getEntityId());

        // Queue files for cleanup
        $this->testFiles[] = $importedFile;
        $this->queueFolderForCleanup($this->fileSystem->openFolder("/testOpenFileById", $this->account->getAuthenticatedUser()));
    }

    /**
     * Make sure we can convert bytes to human readable text like 1,000 to 1k
     */
    public function testGetHumanSize()
    {
        $this->assertEquals("999B", $this->fileSystem->getHumanSize(999));
        $this->assertEquals("1K", $this->fileSystem->getHumanSize(1000));
        $this->assertEquals("500K", $this->fileSystem->getHumanSize(500000));
        $this->assertEquals("1M", $this->fileSystem->getHumanSize(1000000));
        $this->assertEquals("5M", $this->fileSystem->getHumanSize(5000000));
        $this->assertEquals("1G", $this->fileSystem->getHumanSize(1000000000));
        $this->assertEquals("5G", $this->fileSystem->getHumanSize(5000000000));
        $this->assertEquals("1T", $this->fileSystem->getHumanSize(1000000000000));
    }

    /**
     * Test that we can verify the existence (and non-existence) of a folder path
     */
    public function testFolderExists()
    {
        $testPath = "/testFolderExists";
        $folder = $this->fileSystem->openFolder($testPath, $this->account->getAuthenticatedUser());
        if ($folder) {
            $this->dataMapper->delete($folder, $this->account->getAuthenticatedUser());
        }

        // Test a non-existent folder
        $this->assertFalse($this->fileSystem->folderExists($testPath, $this->account->getAuthenticatedUser()));

        // Create the file now
        $folder = $this->fileSystem->openFolder($testPath, $this->account->getAuthenticatedUser(), true);
        $this->queueFolderForCleanup($folder);

        // Test a existent folder
        $this->assertTrue($this->fileSystem->folderExists($testPath, $this->account->getAuthenticatedUser()));
    }

    public function testFileExists()
    {
        $file = $this->fileSystem->fileExists("/", "missingfile.txt", $this->account->getAuthenticatedUser());
        $this->assertFalse($file);

        // Create the missing file
        $file = $this->fileSystem->createFile("/", "presentfile.txt", $this->account->getAuthenticatedUser(), true);
        $this->testFiles[] = $file; // Cleanup
        $this->assertNotNull($file);
        $this->assertFalse(empty($file->getEntityId()));
    }

    public function testDeleteFile()
    {
        $testFile = $this->entityLoader->create("file", $this->account->getAccountId());
        $testFile->setValue("name", "myfile.txt");
        $this->dataMapper->save($testFile, $this->account->getSystemUser());
        $fileId = $testFile->getEntityId();

        $ret = $this->fileSystem->deleteFile($testFile, $this->account->getAuthenticatedUser(), true);
        $this->assertTrue($ret);

        // Make sure this does not exist any more
        $this->assertNull($this->fileSystem->openFileById($fileId, $this->account->getAuthenticatedUser()));
    }

    public function testDeleteFolder()
    {
        // Create some test folders
        $rootFolder = $this->fileSystem->getRootFolder($this->account->getSystemUser());

        // Cleanup first
        if ($this->fileSystem->openFolder("/testDeleteFolder", $this->account->getAuthenticatedUser())) {
            $this->fileSystem->deleteFolder($this->fileSystem->openFolder("/testDeleteFolder"), $this->account->getAuthenticatedUser(), true);
        }

        // Create /testDeleteFolder
        $subFolder = $this->entityLoader->create(ObjectTypes::FOLDER, $this->account->getAccountId());
        $subFolder->setValue("name", "testDeleteFolder");
        $subFolder->setValue("parent_id", $rootFolder->getEntityId());
        $this->dataMapper->save($subFolder, $this->account->getSystemUser());

        $ret = $this->fileSystem->deleteFolder($subFolder, $this->account->getAuthenticatedUser(), true);
        $this->assertTrue($ret);

        // Make sure this does not exist any more
        $this->assertFalse($this->fileSystem->folderExists("/testDeleteFolder", $this->account->getAuthenticatedUser()));
    }

    public function testFileIsTemp()
    {
        $fldr = $this->fileSystem->openFolder("%tmp%", $this->account->getAuthenticatedUser(), true);
        $this->assertNotNull($fldr->getEntityId());

        // Third param is overwrite = true
        $file = $this->fileSystem->createFile("%tmp%", "test", $this->account->getAuthenticatedUser(), true);
        $this->testFiles[] = $file;

        // Test
        $this->assertTrue($this->fileSystem->fileIsTemp($file, $this->account->getAuthenticatedUser()));

        // Move then test again
        $fldr = $this->fileSystem->openFolder("/testFileIsTemp", $this->account->getAuthenticatedUser(), true);
        $this->queueFolderForCleanup($fldr);
        $this->fileSystem->moveFile($file, $fldr, $this->account->getAuthenticatedUser());
        $this->assertFalse($this->fileSystem->fileIsTemp($file, $this->account->getAuthenticatedUser()));
    }

    public function testCreateFile()
    {
        // Try making a new file
        $file = $this->fileSystem->createFile("%tmp%", "testCreateFile.txt", $this->account->getAuthenticatedUser(), true);
        $this->testFiles[] = $file; // For cleanup
        $this->assertNotNull($file->getEntityId());

        // Now try creating same file without overwrite - causes an error
        $file2 = $this->fileSystem->createFile("%tmp%", "testCreateFile.txt", $this->account->getAuthenticatedUser(), false);
        $this->assertNull($file2);
        $this->assertNotNull($this->fileSystem->getLastError());

        // Overwrite the old file with a new one
        $file3 = $this->fileSystem->createFile("%tmp%", "testCreateFile.txt", $this->account->getAuthenticatedUser(), true);
        $this->testFiles[] = $file3;
        $this->assertNotNull($file3->getEntityId());
        $this->assertNotEquals($file->getEntityId(), $file3->getEntityId());
    }

    public function testMoveFile()
    {
        // Try making a new file
        $file = $this->fileSystem->createFile("%tmp%", "testFileMove.txt", $this->account->getAuthenticatedUser(), true);
        $this->testFiles[] = $file; // For cleanup
        $this->assertNotNull($file->getEntityId());

        $fldr = $this->fileSystem->openFolder("/testFileMove", $this->account->getAuthenticatedUser(), true);
        $this->queueFolderForCleanup($fldr);
        $this->fileSystem->moveFile($file, $fldr, $this->account->getAuthenticatedUser());

        // Test to make sure it has moved
        $this->assertEquals($fldr->getEntityId(), $file->getValue("folder_id"));
    }

    public function testWriteAndReadFile()
    {
        $testData = "test data";

        $file = $this->fileSystem->createFile("%tmp%", "testFileMove.txt", $this->account->getAuthenticatedUser(), true);
        $retSizeUploaded = $this->fileSystem->writeFile($file, $testData, $this->account->getSystemUser());
        $this->assertGreaterThan(0, $retSizeUploaded);
        $this->assertEquals($testData, $this->fileSystem->readFile($file));
    }
}
