<?php

declare(strict_types=1);

namespace NetricTest\Account\InitData\Sets;

use Netric\Account\InitData\Sets\GroupingsInitDataFactory;
use Netric\Account\InitData\Sets\GroupingsInitData;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class GroupingsInitDataFactoryTest extends TestCase
{
    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testSetInitialData()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $dataSet = $account->getServiceManager()->get(GroupingsInitDataFactory::class);
        $this->assertInstanceOf(GroupingsInitData::class, $dataSet);
    }
}
