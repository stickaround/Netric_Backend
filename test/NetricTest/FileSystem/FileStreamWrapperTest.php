<?php

/**
 * Test that we can wrap a file
 */

namespace NetricTest\FileSystem;

use Netric\FileSystem\FileSystem;
use Netric\FileSystem\FileStreamWrapper;
use Netric\EntityQuery;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType;
use Netric\EntityDefinition\ObjectTypes;
use PHPUnit\Framework\TestCase;
use Netric\FileSystem\FileSystemFactory;

class FileStreamWrapperTest extends TestCase
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
     * Entity loader
     *
     * @var EntityLoader
     */
    private $entityLoader = null;


    /**
     * Test files to cleanup
     *
     * @var ObjType\FileEntity[]
     */
    private $testFiles = [];

    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();

        $this->fileSystem = $sl->get(FileSystemFactory::class);
        $this->entityLoader = $sl->get(EntityLoaderFactory::class);
    }

    /**
     * Clean-up and test files
     */
    protected function tearDown(): void
    {
        foreach ($this->testFiles as $file) {
            $this->fileSystem->deleteFile($file, true);
        }
    }

    /**
     * Create a test file to work with
     *
     * @param string $name Name of the test file to create
     * @return \Netric\Entity\EntityInterface
     */
    private function createTestFile($name = "streamtest.txt")
    {
        $file = $this->entityLoader->create(ObjectTypes::FILE, $this->account->getAccountId());
        $file->setValue("name", $name);
        $this->entityLoader->save($file, $this->account->getSystemUser());
        $this->testFiles[] = $file;
        return $file;
    }

    /**
     * Check if we can read from a file using standard PHP streams
     */
    public function testRead()
    {
        $data = "my test contents";

        // Create a test file and write to it
        $testFile = $this->createTestFile();
        $bytesWritten = $this->fileSystem->writeFile($testFile, $data, $this->account->getSystemUser());
        $this->assertNotEquals(-1, $bytesWritten);

        // Now open a stream and read from it one byte at a time
        $buf = "";
        $stream = FileStreamWrapper::open($this->fileSystem, $testFile);
        while (!feof($stream)) {
            $ch = fread($stream, 1);
            $buf .= $ch;
        }
        $this->assertEquals($buf, $data);
    }

    /**
     * Make sure the context works with simultaneous reads from different files
     */
    public function testReadMulti()
    {
        $data = "my test contents";
        $data2 = "second test contents";

        // Create a test files and write to them
        $testFile = $this->createTestFile("streamtest1.txt");
        $this->fileSystem->writeFile($testFile, $data, $this->account->getSystemUser());
        $testFile2 = $this->createTestFile("streamtest2.txt");
        $this->fileSystem->writeFile($testFile2, $data2, $this->account->getSystemUser());

        // Open them both at once
        $stream1 = FileStreamWrapper::open($this->fileSystem, $testFile);
        $stream2 = FileStreamWrapper::open($this->fileSystem, $testFile2);

        // Read through stream 1
        $buf = "";
        while (!feof($stream1)) {
            $ch = fread($stream1, 1);
            $buf .= $ch;
        }
        $this->assertEquals($buf, $data);

        // Read through stream 2
        $buf = "";
        while (!feof($stream2)) {
            $ch = fread($stream2, 1);
            $buf .= $ch;
        }
        $this->assertEquals($buf, $data2);
    }
}
