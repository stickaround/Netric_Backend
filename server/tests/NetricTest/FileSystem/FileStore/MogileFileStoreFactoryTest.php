<?php
/**
 * Test the AnsFileStoreFactory service
 */
namespace NetricTest\FileSystem;

use Netric;
use PHPUnit\Framework\TestCase;

class MogileFileStoreFactoryTest extends TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    private $account = null;


    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
    }

    public function testCreateService()
    {
        $sl = $this->account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\FileSystem\FileStore\FileStoreInterface',
            $sl->get('Netric/FileSystem/FileStore/MogileFileStore')
        );
    }
}