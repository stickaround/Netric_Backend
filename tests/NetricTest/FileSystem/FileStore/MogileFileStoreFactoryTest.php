<?php
/**
 * Test the AnsFileStoreFactory service
 */
namespace NetricTest\FileSystem;

use Netric\FileSystem\FileStore\MogileFileStore;
use Netric\FileSystem\FileStore\MogileFileStoreFactory;

use PHPUnit\Framework\TestCase;

class MogileFileStoreFactoryTest extends TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    private $account = null;


    protected function setUp(): void
{
        $this->account = \NetricTest\Bootstrap::getAccount();
    }

    public function testCreateService()
    {
        $sl = $this->account->getServiceManager();
        $this->assertInstanceOf(
            MogileFileStore::class,
            $sl->get(MogileFileStoreFactory::class)
        );
    }
}
