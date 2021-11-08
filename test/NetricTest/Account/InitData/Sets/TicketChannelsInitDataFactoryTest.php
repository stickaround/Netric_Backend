<?php

declare(strict_types=1);

namespace NetricTest\Account\InitData\Sets;

use Netric\Account\InitData\Sets\TicketChannelsInitData;
use Netric\Account\InitData\Sets\TicketChannelsInitDataFactory;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class TicketChannelsInitDataFactoryTest extends TestCase
{
    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testSetInitialData()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $dataSet = $account->getServiceManager()->get(TicketChannelsInitDataFactory::class);
        $this->assertInstanceOf(TicketChannelsInitData::class, $dataSet);
    }
}
