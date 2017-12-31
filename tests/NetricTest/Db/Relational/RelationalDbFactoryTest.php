<?php
namespace NetricTest\Db\Relational;

use Netric\Db\Relational;
use PHPUnit\Framework\TestCase;

class RelationalDbFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\Db\Relational\RelationalDbInterface',
            $sm->get('Netric\Db\Relational\RelationalDb')
        );
    }
}