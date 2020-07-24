<?php
/**
 * Test saving files remotely to ans
 */
namespace NetricTest\FileSystem\FileStore;

use Netric;
use Netric\FileSystem\FileStore\MogileFileStore;
use Netric\FileSystem\FileStore\FileStoreInterface;
use MogileFs;
use Netric\Entity\EntityLoaderFactory;
use Netric\Config\ConfigFactory;

/**
 * Running this test requires we have an ANS service running
 *
 * @group integration
 */
class MogileFileStoreTest extends AbstractFileStoreTests
{
    /**
     * Handle to a constructed LocalFiletore
     *
     * @var MogileFs
     */
    private $mogileFileStore = null;

    /**
     * Temp path for saving files
     *
     * @var string
     */
    private $tmpPath = "";

    protected function setUp(): void
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->tmpPath = __DIR__ . "/tmp";

        $accId = $account->getAccountId();
        $entityLoader = $sm->get(EntityLoaderFactory::class);

        $config = $sm->get(ConfigFactory::class);

        $this->mogileFileStore = new MogileFileStore(
            $accId,
            $entityLoader,
            $this->tmpPath,
            $config->files->server,
            $config->files->account,
            7001
        );

        // Make directory if it does not exist
        if (!file_exists($this->tmpPath)) {
            mkdir($this->tmpPath);
        }
    }

    /**
     * Cleanup
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if (file_exists($this->tmpPath)) {
            $this->rrmdir($this->tmpPath);
        }
    }


    /**
     * Required for any FileStore implementation to constract and return a File Store
     *
     * @return FileStoreInterface
     */
    protected function getFileStore()
    {
        return $this->mogileFileStore;
    }

    /**
     * Recursively delete a directory and all it's children
     *
     * @param string $dir The path fo the directory to recursively deleted
     */
    private function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);

            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object)) {
                        $this->rrmdir($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }

            rmdir($dir);
        }
    }
}
