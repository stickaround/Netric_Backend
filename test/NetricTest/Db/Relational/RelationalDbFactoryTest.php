<?php
namespace NetricTest\Db\Relational;

use Netric\Db\Relational\RelationalDbInterface;
use PHPUnit\Framework\TestCase;
use Netric\Db\Relational\RelationalDbFactory;

class RelationalDbFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            RelationalDbInterface::class,
            $sm->get(RelationalDbFactory::class)
        );
    }
}
