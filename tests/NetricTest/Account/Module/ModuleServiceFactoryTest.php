<?php
/**
 * Test the module service facotry
 */
namespace NetricTest\Account\Module;

use PHPUnit\Framework\TestCase;
use Netric\Account\Module\ModuleService;
use Netric\Account\Module\ModuleServiceFactory;

class ModuleServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            ModuleService::class,
            $sm->get(ModuleServiceFactory::class)
        );
    }
}
