<?php
/**
 * Test the module service facotry
 */
namespace NetricTest\Account\Module;

use PHPUnit\Framework\TestCase;

class ModuleServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Account\Module\ModuleService',
            $sm->get('Netric\Account\Module\ModuleService')
        );
    }
}
