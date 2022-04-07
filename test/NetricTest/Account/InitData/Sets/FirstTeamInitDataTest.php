<?php

declare(strict_types=1);

namespace NetricTest\Account\InitData\Sets;

use Netric\Account\InitData\Sets\FirstTeamInitDataFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class FirstTeamInitDataTest extends TestCase
{
    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testSetInitialData()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $dataSet = $account->getServiceManager()->get(FirstTeamInitDataFactory::class);
        $this->assertTrue($dataSet->setInitialData($account));
    }
}
