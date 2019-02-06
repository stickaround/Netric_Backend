<?php
/**
 * Test the FileSystem service
 */
namespace NetricTest\FileSystem;

use Netric\FileSystem\FileSystem;
use Netric\FileSystem\ImageResizer;
use Netric\Entity\ObjType;

use PHPUnit\Framework\TestCase;

class ImageResizerTest extends TestCase
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
     * Resizer service
     *
     * @var ImageResizer
     */
    private $imageResizer = null;


    /**
     * Test files to cleanup
     *
     * @var ObjType\FileEntity[]
     */
    private $testFiles = array();

    /**
     * Use a temp path in the tests directory to avoid permissions issues
     *
     * @var string
     */
    private $tempPath = __DIR__ . '/../../data/tmp';

    /**
     * Setup each test
     */
    protected function setUp(): void
{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();
        $this->fileSystem = $sl->get(FileSystem::class);
        $this->imageResizer = $sl->get(ImageResizer::class);
    }

    /**
     * Cleanup each test
     */
    protected function tearDown(): void
{
        // Clean-up test files
        foreach ($this->testFiles as $file) {
            $this->fileSystem->deleteFile($file, true);
        }
    }

    /**
     * Make sure we can resize a file
     */
    public function testResizeFile()
    {
        // Import local image that is 256x256
        $file = $this->fileSystem->createFile("%tmp%", "utest-image.png", true);
        $this->fileSystem->writeFile($file, file_get_contents(__DIR__ . '/../../data/image.png'));
        $this->testFiles[] = $file; // For cleanup

        // Resize the image
        $resizedFile = $this->imageResizer->resizeFile($file, 64, -1, $this->tempPath);
        $this->testFiles[] = $resizedFile;

        $this->assertGreaterThan(0, $resizedFile->getValue("file_size"));

        // Check the size of the new image to make sure it is valid and the right size
        $tmpDownloaded = __DIR__ . '/../../data/tmp/resized.png';
        file_put_contents($tmpDownloaded, $this->fileSystem->readFile($resizedFile));
        $sizes = getimagesize($tmpDownloaded);
        unlink($tmpDownloaded);
        $this->assertEquals(64, $sizes[0]);
        $this->assertEquals(64, $sizes[1]);
    }

    /**
     * Make sure we can resize a file by max height only
     */
    public function testResizeFileByMaxHeight()
    {
        // Import local image that is 256x256
        $file = $this->fileSystem->createFile("%tmp%", "utest-image.png", true);
        $this->fileSystem->writeFile($file, file_get_contents(__DIR__ . '/../../data/image.png'));
        $this->testFiles[] = $file; // For cleanup

        // Resize the image
        $resizedFile = $this->imageResizer->resizeFile($file, -1, 32, $this->tempPath);
        $this->testFiles[] = $resizedFile;

        $this->assertGreaterThan(0, $resizedFile->getValue("file_size"));

        // Check the size of the new image to make sure it is valid and the right size
        $tmpDownloaded = __DIR__ . '/../../data/tmp/resized.png';
        file_put_contents($tmpDownloaded, $this->fileSystem->readFile($resizedFile));
        $sizes = getimagesize($tmpDownloaded);
        unlink($tmpDownloaded);
        $this->assertEquals(32, $sizes[0]);
        $this->assertEquals(32, $sizes[1]);
    }

    /**
     * Make sure we can resize a file by max width only
     */
    public function testResizeFileByMaxWidth()
    {
        // Import local image that is 256x256
        $file = $this->fileSystem->createFile("%tmp%", "utest-image.png", true);
        $this->fileSystem->writeFile($file, file_get_contents(__DIR__ . '/../../data/image.png'));
        $this->testFiles[] = $file; // For cleanup

        // Resize the image
        $resizedFile = $this->imageResizer->resizeFile($file, 32, -1, $this->tempPath);
        $this->testFiles[] = $resizedFile;

        $this->assertGreaterThan(0, $resizedFile->getValue("file_size"));

        // Check the size of the new image to make sure it is valid and the right size
        $tmpDownloaded = __DIR__ . '/../../data/tmp/resized.png';
        file_put_contents($tmpDownloaded, $this->fileSystem->readFile($resizedFile));
        $sizes = getimagesize($tmpDownloaded);
        unlink($tmpDownloaded);
        $this->assertEquals(32, $sizes[0]);
        $this->assertEquals(32, $sizes[1]);
    }
}
