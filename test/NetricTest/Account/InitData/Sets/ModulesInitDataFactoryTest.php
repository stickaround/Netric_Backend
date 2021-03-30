<?php

declare(strict_types=1);

namespace NetricTest\Account\InitData\Sets;

use Netric\Account\InitData\Sets\ModulesInitData;
use Netric\Account\InitData\Sets\ModulesInitDataFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class ModulesInitDataFactoryTest extends TestCase
{
    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testSetInitialData()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $dataSet = $account->getServiceManager()->get(ModulesInitDataFactory::class);
        $this->assertInstanceOf(ModulesInitData::class, $dataSet);
    }
}
