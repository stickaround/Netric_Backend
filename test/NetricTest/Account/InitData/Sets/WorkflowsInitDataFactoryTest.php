<?php

declare(strict_types=1);

namespace NetricTest\Account\InitData\Sets;

use Netric\Account\InitData\Sets\WorkflowsInitData;
use Netric\Account\InitData\Sets\WorkflowsInitDataFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class WorkflowsInitDataFactoryTest extends TestCase
{
    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testSetInitialData()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $dataSet = $account->getServiceManager()->get(WorkflowsInitDataFactory::class);
        $this->assertInstanceOf(WorkflowsInitData::class, $dataSet);
    }
}
