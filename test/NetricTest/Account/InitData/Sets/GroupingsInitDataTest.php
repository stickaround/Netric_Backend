<?php

declare(strict_types=1);

namespace NetricTest\Account\InitData\Sets;

use Netric\Account\InitData\Sets\GroupingsInitDataFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class GroupingsInitDataTest extends TestCase
{
    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testSetInitialData()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $dataSet = $account->getServiceManager()->get(GroupingsInitDataFactory::class);
        $this->assertTrue($dataSet->setInitialData($account));
    }
}
