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
     * Test to make sure we get a folder by id
     */
    public function testOpenFolderById()
    {
        $testFolder = $this->fileSystem->openOrCreateFolder(
            $this->fileSystem->getRootFolder($this->account->getAuthenticatedUser()),
            "testOpenFolderById",
            $this->account->getAuthenticatedUser()
        );
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
    public function testImportTempFile()
    {
        $fileToImport = __DIR__ . "/FileStore/fixtures/file-to-upload.txt";

        // Test importing a local file
        $importedFile = $this->fileSystem->importTempFile(
            $this->account->getAuthenticatedUser(),
            $fileToImport,
            "testImportFile"
        );
        $this->assertNotNull($importedFile);
        $this->assertEquals("file-to-upload.txt", $importedFile->getValue("name"));
        $this->assertEquals(filesize($fileToImport), $importedFile->getValue("file_size"));

        // Queue files for cleanup
        $this->testFiles[] = $importedFile;
    }

    /**
     * Test importing a new file with existing file entity
     */
    public function testImportFileExisting()
    {
        $fileToImport = __DIR__ . "/FileStore/fixtures/file-to-upload-existing.txt";
        $rootFolder = $this->fileSystem->getRootFolder($this->account->getSystemUser());

        // Create an existing file
        $file = $this->entityLoader->create("file", $this->account->getAccountId());
        $file->setValue("name", "newFile.jpg");
        $this->entityLoader->save($file, $this->account->getSystemUser());
        $this->testFiles[] = $file;

        // Test importing a local file
        $importedFile = $this->fileSystem->importFileToFolder(
            $this->account->getAuthenticatedUser(),
            $rootFolder,
            $fileToImport,
            "",
            ["entity_id" => $file->getValue("entity_id"), "name" => "myupdatedfile.jpg"]
        );

        $this->assertNotNull($importedFile);
        $this->assertEquals("myupdatedfile.jpg", $importedFile->getValue("name"));
        $this->assertEquals(filesize($fileToImport), $importedFile->getValue("file_size"));

        // Queue files for cleanup
        $this->testFiles[] = $importedFile;
    }

    /**
     * Test opening a file by unique id
     */
    public function testOpenFileById()
    {
        $fileToImport = __DIR__ . "/FileStore/fixtures/file-to-upload.txt";
        $importedFile = $this->fileSystem->importTempFile(
            $this->account->getAuthenticatedUser(),
            $fileToImport
        );

        // Test opening the file straight from the fileSystem by id
        $openedFile = $this->fileSystem->openFileById($importedFile->getEntityId(), $this->account->getAuthenticatedUser());
        $this->assertNotNull($openedFile);
        $this->assertFalse(empty($openedFile->getEntityId()));
        $this->assertEquals($importedFile->getEntityId(), $openedFile->getEntityId());

        // Queue files for cleanup
        $this->testFiles[] = $importedFile;
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

    public function testFileExists()
    {
        $rootFolder = $this->fileSystem->getRootFolder($this->account->getAuthenticatedUser());
        $file = $this->fileSystem->fileExists($rootFolder, "missingfile.txt", $this->account->getAuthenticatedUser());
        $this->assertFalse($file);

        // Create the missing file
        $file = $this->fileSystem->createFile($rootFolder, "presentfile.txt", $this->account->getAuthenticatedUser(), true);
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

        // Create /testDeleteFolder
        $subFolder = $this->entityLoader->create(ObjectTypes::FOLDER, $this->account->getAccountId());
        $subFolder->setValue("name", "testDeleteFolder");
        $subFolder->setValue("parent_id", $rootFolder->getEntityId());
        $this->dataMapper->save($subFolder, $this->account->getSystemUser());

        $ret = $this->fileSystem->deleteFolder($subFolder, $this->account->getAuthenticatedUser(), true);
        $this->assertTrue($ret);

        // Make sure this does not exist any more
        $this->assertFalse(
            $this->fileSystem->folderExists(
                $this->fileSystem->getRootFolder($this->account->getAuthenticatedUser()),
                "testDeleteFolder",
                $this->account->getAuthenticatedUser()
            )
        );
    }

    public function testFileIsTemp()
    {
        $fldr = $this->fileSystem->getTempFolder($this->account->getAuthenticatedUser());
        $this->assertNotNull($fldr->getEntityId());

        // Third param is overwrite = true
        $file = $this->fileSystem->createTempFile("test", $this->account->getAuthenticatedUser(), true);
        $this->testFiles[] = $file;

        // Test
        $this->assertTrue($this->fileSystem->fileIsTemp($file, $this->account->getAuthenticatedUser()));

        // Move then test again
        $fldr = $this->fileSystem->openOrCreateFolder(
            $this->fileSystem->getRootFolder($this->account->getAuthenticatedUser()),
            "testFileIsTemp",
            $this->account->getAuthenticatedUser()
        );
        $this->queueFolderForCleanup($fldr);
        $this->fileSystem->moveFile($file, $fldr, $this->account->getAuthenticatedUser());
        $this->assertFalse($this->fileSystem->fileIsTemp($file, $this->account->getAuthenticatedUser()));
    }

    public function testCreateFile()
    {
        // Try making a new file
        $file = $this->fileSystem->createTempFile("testCreateFile.txt", $this->account->getAuthenticatedUser(), true);
        $this->testFiles[] = $file; // For cleanup
        $this->assertNotNull($file->getEntityId());

        // Now try creating same file without overwrite - causes an error
        $file2 = $this->fileSystem->createTempFile("testCreateFile.txt", $this->account->getAuthenticatedUser(), false);
        $this->assertNull($file2);
        $this->assertNotNull($this->fileSystem->getLastError());

        // Overwrite the old file with a new one
        $file3 = $this->fileSystem->createTempFile("testCreateFile.txt", $this->account->getAuthenticatedUser(), true);
        $this->testFiles[] = $file3;
        $this->assertNotNull($file3->getEntityId());
        $this->assertNotEquals($file->getEntityId(), $file3->getEntityId());
    }

    public function testMoveFile()
    {
        // Try making a new file
        $file = $this->fileSystem->createTempFile("testFileMove.txt", $this->account->getAuthenticatedUser(), true);
        $this->testFiles[] = $file; // For cleanup
        $this->assertNotNull($file->getEntityId());

        $fldr = $this->fileSystem->openOrCreateFolder(
            $this->fileSystem->getRootFolder($this->account->getAuthenticatedUser()),
            "testFileMove",
            $this->account->getAuthenticatedUser()
        );
        $this->queueFolderForCleanup($fldr);
        $this->fileSystem->moveFile($file, $fldr, $this->account->getAuthenticatedUser());

        // Test to make sure it has moved
        $this->assertEquals($fldr->getEntityId(), $file->getValue("folder_id"));
    }

    public function testWriteAndReadFile()
    {
        $testData = "test data";

        $file = $this->fileSystem->createTempFile("testFileMove.txt", $this->account->getAuthenticatedUser(), true);
        $retSizeUploaded = $this->fileSystem->writeFile($file, $testData, $this->account->getSystemUser());
        $this->assertGreaterThan(0, $retSizeUploaded);
        $this->assertEquals($testData, $this->fileSystem->readFile($file));
    }
}
