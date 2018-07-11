<?php
namespace NetricTest\Db\Relational;

use Netric\Db\Relational\RelationalDbInterface;
use PHPUnit\Framework\TestCase;

class RelationalDbFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            RelationalDbInterface::class,
            $sm->get('Netric\Db\Relational\RelationalDb')
        );
    }
}
