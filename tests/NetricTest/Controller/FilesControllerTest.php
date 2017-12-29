<?php
/**
 * Test calling the files controller
 */
namespace NetricTest\Controller;

use Netric;
use Netric\EntityLoader;
use Netric\Account\Account;
use Netric\Controller\FilesController;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\FolderEntity;
use Netric\Entity\ObjType\FileEntity;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;

class FilesControllerTest extends TestCase
{
    /**
     * Account used for testing
     *
     * @var Account
     */
    protected $account = null;

    /**
     * Controller instance used for testing
     *
     * @var FilesController
     */
    protected $controller = null;

    /**
     * Test user
     *
     * @var UserEntity
     */
    private $user = null;

    /**
     * Get FileSystem
     *
     * @var FileSystem
     */
    private $fileSystem = null;

    /**
     * Test folders to cleanup
     *
     * @var FolderEntity[]
     */
    private $testFolders = [];

    /**
     * Test files to cleanup
     *
     * @var FileEntity[]
     */
    private $testFiles = [];

    /**
     * Common constants used
     *
     * @cons string
     */
    const TEST_USER = "test_files_controller";
    const TEST_USER_PASS = "testpass";

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();
        $loader = $sl->get(EntityLoader::class);

        // Create the controller
        $this->controller = new Netric\Controller\FilesController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;

        // Get FileSystem
        $this->fileSystem = $sl->get(FileSystem::class);

        // Make sure old test user does not exist
        $query = new \Netric\EntityQuery("user");
        $query->where('name')->equals(self::TEST_USER);
        $index = $this->account->getServiceManager()->get("EntityQuery_Index");
        $res = $index->executeQuery($query);
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $user = $res->getEntity($i);
            $loader->delete($user, true);
        }

        // Create a temporary user
        $user = $loader->create("user");
        $user->setValue("name", self::TEST_USER);
        $user->setValue("password", self::TEST_USER_PASS);
        $user->setValue("active", true);
        $loader->save($user);
        $this->user = $user;
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown()
    {
        // Clean-up test files
        foreach ($this->testFiles as $file) {
            $this->fileSystem->deleteFile($file);
        }

        // Delete all test folders in reverse order - in case they are children of each other
        $folders = array_reverse($this->testFolders);
        foreach ($folders as $folder) {
            $this->fileSystem->deleteFolder($folder);
        }

        // Remote the temp user
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();
        $entityLoader = $sl->get(EntityLoader::class);
        $entityLoader->delete($this->user, true);
    }

    /**
     * Try uploading a file into the FileSystem through the controller
     */
    public function testUpload()
    {
        /*
         * Add fake uploaded files. In normal execution this would fail since
         * it would fail PHP's is_uploaded_file but whe controller->testMode is true
         * it bypasses that test.
         */

        // First copy to a temp file since we'll delete the temp in the upload function
        $sourceFile = __DIR__ . "/fixtures/files-upload-test.txt";
        $tempFile = __DIR__ . "/fixtures/files-upload-test-tmp.txt";
        copy($sourceFile, $tempFile);

        $req = $this->controller->getRequest();
        $testUploadedFiles = array(
            array("tmp_name"=>$tempFile, "name"=>"files-upload-test.txt")
        );
        $req->setParam("files", $testUploadedFiles);
        $req->setParam("path", "/testUpload");

        /*
         * Now upload the file which should import the temp file,
         * then delete it since it will normally be working with HTTP_POST uploads
         * adn we want it to cleanup as it finishes processing each file.
         */
        $ret = $this->controller->postUploadAction();

        // Results are returned in an array
        $this->assertFalse(isset($ret['error']), "Error: " . var_export($ret, true));
        $this->assertNotEquals(-1, $ret[0]); // error
        $this->assertTrue(isset($ret[0]['id']));
        $this->assertTrue(isset($ret[0]['name']));
        $this->assertTrue(isset($ret[0]['ts_updated']));

        // Make sure we cleaned up the temp file
        $this->assertFalse(file_exists($tempFile));

        // Set created folder so we make sure we purge it
        $this->testFolders[] = $this->fileSystem->openFolder("/testUpload");

        // Open the file and make sure it was uploaded correctly
        $file = $this->fileSystem->openFileById($ret[0]['id']);
        $this->testFiles[] = $file; // For tearDown Cleanup

        // Test file
        $this->assertEquals("files-upload-test.txt", $file->getValue("name"));
        $this->assertEquals(filesize($sourceFile), $file->getValue("file_size"));
        $this->assertEquals($this->account->getUser()->getId(), $file->getValue("owner_id"));
    }

    /**
     * Try uploading a single file into the FileSystem through the controller using the data from client side
     */
    public function testUploadFilesFromClient()
    {
        /*
         * Add fake uploaded files. In normal execution this would fail since
         * it would fail PHP's is_uploaded_file but whe controller->testMode is true
         * it bypasses that test.
         */

        // First copy to a temp file since we'll delete the temp in the upload function
        $sourceFile = __DIR__ . "/fixtures/files-upload-test.txt";
        $tempFile = __DIR__ . "/fixtures/files-upload-test-tmp.txt";
        copy($sourceFile, $tempFile);

        $req = $this->controller->getRequest();

        // We are using files array index, since this is the post data format sent by the client side
        $testUploadedFiles['files'] = array(
            "name" => "files-upload-test.txt",
            "tmp_name" => $tempFile,
            "type" => "text/plain",
            "size" => "100",
            "error" => 0
        );

        $req->setParam("files", $testUploadedFiles);
        $req->setParam("path", "/testUpload");

        /*
         * Now upload the file which should import the temp file,
         * then delete it since it will normally be working with HTTP_POST uploads
         * adn we want it to cleanup as it finishes processing each file.
         */
        $ret = $this->controller->postUploadAction();

        // Results are returned in an array
        $this->assertFalse(isset($ret['error']), "Error: " . var_export($ret, true));
        $this->assertNotEquals(-1, $ret[0]); // error
        $this->assertTrue(isset($ret[0]['id']));
        $this->assertTrue(isset($ret[0]['name']));
        $this->assertTrue(isset($ret[0]['ts_updated']));

        // Make sure we cleaned up the temp file
        $this->assertFalse(file_exists($tempFile));

        // Set created folder so we make sure we purge it
        $this->testFolders[] = $this->fileSystem->openFolder("/testUpload");

        // Open the file and make sure it was uploaded correctly
        $file = $this->fileSystem->openFileById($ret[0]['id']);
        $this->testFiles[] = $file; // For tearDown Cleanup

        // Test file
        $this->assertEquals("files-upload-test.txt", $file->getValue("name"));
        $this->assertEquals(filesize($sourceFile), $file->getValue("file_size"));
        $this->assertEquals($this->account->getUser()->getId(), $file->getValue("owner_id"));
    }

    /**
     * Try uploading 2 files into the FileSystem through the controller using the data from client side
     */
    public function testUploadFilesFromClientMultipleFiles()
    {
        /*
         * Add fake uploaded files. In normal execution this would fail since
         * it would fail PHP's is_uploaded_file but whe controller->testMode is true
         * it bypasses that test.
         */

        // First copy to a temp file since we'll delete the temp in the upload function
        $sourceFile = __DIR__ . "/fixtures/files-upload-test.txt";
        $tempFile = __DIR__ . "/fixtures/files-upload-test-tmp.txt";
        copy($sourceFile, $tempFile);

        // First copy to a temp file since we'll delete the temp in the upload function
        $sourceFile2 = __DIR__ . "/fixtures/files-upload-test2.txt";
        $tempFile2 = __DIR__ . "/fixtures/files-upload-test-tmp2.txt";
        copy($sourceFile2, $tempFile2);

        $req = $this->controller->getRequest();

        // We are using files array index, since this is the post data format sent by the client side
        $testUploadedFiles['files'] = array(
            "name" => array("files-upload-test.txt", "files-upload-test2.txt"),
            "tmp_name" => array($tempFile, $tempFile2),
            "type" => array("text/plain", "text/plain"),
            "size" => array("100", "100"),
            "error" => array(0, 0)
        );

        $req->setParam("files", $testUploadedFiles);
        $req->setParam("path", "/testUpload");

        /*
         * Now upload the file which should import the temp file,
         * then delete it since it will normally be working with HTTP_POST uploads
         * adn we want it to cleanup as it finishes processing each file.
         */
        $ret = $this->controller->postUploadAction();

        // Results are returned in an array
        $this->assertFalse(isset($ret['error']), "Error: " . var_export($ret, true));
        $this->assertNotEquals(-1, $ret[0]); // error
        $this->assertTrue(isset($ret[0]['id']));
        $this->assertTrue(isset($ret[0]['name']));
        $this->assertTrue(isset($ret[0]['ts_updated']));

        // Check the result for the second file
        $this->assertNotEquals(-1, $ret[1]); // error
        $this->assertTrue(isset($ret[1]['id']));
        $this->assertTrue(isset($ret[1]['name']));
        $this->assertTrue(isset($ret[1]['ts_updated']));

        // Make sure we cleaned up the temp file
        $this->assertFalse(file_exists($tempFile));
        $this->assertFalse(file_exists($tempFile2));

        // Set created folder so we make sure we purge it
        $this->testFolders[] = $this->fileSystem->openFolder("/testUpload");

        // Open the file and make sure it was uploaded correctly
        $file = $this->fileSystem->openFileById($ret[0]['id']);
        $this->testFiles[] = $file; // For tearDown

        // Clean up for the second file
        $file2 = $this->fileSystem->openFileById($ret[1]['id']);
        $this->testFiles[] = $file2; // For tearDown Cleanup Cleanup

        // Test file
        $this->assertEquals("files-upload-test.txt", $file->getValue("name"));
        $this->assertEquals(filesize($sourceFile), $file->getValue("file_size"));
        $this->assertEquals($this->account->getUser()->getId(), $file->getValue("owner_id"));

        // Test the second file
        $this->assertEquals("files-upload-test2.txt", $file2->getValue("name"));
    }

    /**
     * Try downloading a file
     */
    public function testGetDownloadAction()
    {
        // Import a test file
        $fileToImport = __DIR__ . "/fixtures/files-upload-test.txt";
        $importedFile = $this->fileSystem->importFile($fileToImport, "/testdownload");
        $this->testFiles[] = $importedFile;
        $this->testFolders[] = $this->fileSystem->openFolder("/testdownload");

        // Set which file to download in the request
        $req = $this->controller->getRequest();
        $req->setParam("file_id", $importedFile->getId());

        /*
         * Now stream the file contents into $ret
         */
        $response = $this->controller->getDownloadAction();

        // Suppress the output into a buffer
        $response->suppressOutput(true);
        $response->stream();
        $headers = $response->getHeaders();

        // Make sure the contents match
        $this->assertEquals(file_get_contents($fileToImport), $response->getOutputBuffer());
        $this->assertTrue(isset($headers['Content-Type']));
        $this->assertTrue(isset($headers['Content-Disposition']));
        $this->assertTrue(isset($headers['Content-Length']));
    }

    /**
     * Try downloading a resized image file
     */
    public function testGetDownloadAction_ResizedImage()
    {
        // Import a test file
        $fileToImport = __DIR__ . "/../../data/image.png";
        $importedFile = $this->fileSystem->importFile($fileToImport, "/testdownload");
        $this->testFiles[] = $importedFile;
        $this->testFolders[] = $this->fileSystem->openFolder("/testdownload");

        // Set which file to download in the request and that it should be resized to 64 px
        $req = $this->controller->getRequest();
        $req->setParam("file_id", $importedFile->getId());
        $req->setParam("max_width",64);
        $req->setParam("max_height",64);

        // Now stream the file contents into $ret
        $response = $this->controller->getDownloadAction();

        // Create a temp file to store the resized image into
        $tempFilePath = __DIR__ . '/../../data/tmp/files_controller_temp.png';
        $outputStream = fopen($tempFilePath, 'w');

        // Suppress the output into a file
        $response->suppressOutput(true);
        $response->stream($outputStream);
        $headers = $response->getHeaders();
        fclose($outputStream);

        // Read the image size from disk
        $sizes = getimagesize($tempFilePath);
        //unlink($tempFilePath);

        // Get the newly created resized file entity (will copy $importedFile)
        $newFileRef = Netric\Entity\Entity::decodeObjRef($headers['X-Entity']);
        $resizedFile = $this->fileSystem->openFileById($newFileRef['id']);
        $this->testFiles[] = $resizedFile;

        // Make sure the returned entity is different than the uploaded one
        $this->assertNotEquals($importedFile->getId(), $resizedFile->getId());

        // Make sure the image is valid and resized
        $this->assertEquals(64, $sizes[0]);
        $this->assertEquals(64, $sizes[1]);
    }

    /**
     * Test that we can download a profile image for a user
     */
    public function testGetUserImageAction()
    {
        // Import a test profile image
        $fileToImport = __DIR__ . "/../../data/image.png";
        $importedFile = $this->fileSystem->importFile($fileToImport, "/testdownload");
        $this->testFiles[] = $importedFile;
        $this->testFolders[] = $this->fileSystem->openFolder("/testdownload");

        // Set the newly imported file as the user's profile pic
        $this->user->setValue('image_id', $importedFile->getId());
        $loader = $this->account->getServiceManager()->get(EntityLoader::class);
        $loader->save($this->user);

        // Set which file to download in the request and that it should be resized to 64 px
        $req = $this->controller->getRequest();
        $req->setParam("user_id", $this->user->getId());
        $req->setParam("max_width",64);
        $req->setParam("max_height",64);

        // Now stream the file contents into $ret
        $response = $this->controller->getUserImageAction();

        // Create a temp file to store the resized image into
        $tempFilePath = __DIR__ . '/../../data/tmp/files_controller_temp.png';
        $outputStream = fopen($tempFilePath, 'w');

        // Suppress the output into a file
        $response->suppressOutput(true);
        $response->stream($outputStream);
        $headers = $response->getHeaders();
        fclose($outputStream);

        // Get the newly created resized file entity (will copy $importedFile)
        $newFileRef = Netric\Entity\Entity::decodeObjRef($headers['X-Entity']);
        $resizedFile = $this->fileSystem->openFileById($newFileRef['id']);
        $this->testFiles[] = $resizedFile;

        // Make sure we didn't stream an empty file
        $fileSize = \filesize($tempFilePath);
        $this->assertGreaterThan(0, $fileSize);
    }
}
