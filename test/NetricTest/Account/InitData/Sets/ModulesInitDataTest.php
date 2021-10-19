<?php

declare(strict_types=1);

namespace NetricTest\Account\InitData\Sets;

use Netric\Account\InitData\Sets\ModulesInitDataFactory;
use Netric\Account\Module\ModuleServiceFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class ModulesInitDataTest extends TestCase
{
    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testSetInitialData()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $dataSet = $account->getServiceManager()->get(ModulesInitDataFactory::class);
        $this->assertTrue($dataSet->setInitialData($account));

        // Get the module service
        $moduleService = $account->getServiceManager()->get(ModuleServiceFactory::class);
        $module = $moduleService->getByName('settings', $account->getAccountId());

        // Check if sort_order of settings is equal to 20
        $this->assertEquals($module->getSortOrder(), 20);
    }
}
