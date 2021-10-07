<?php

declare(strict_types=1);

namespace NetricTest\Mail;

use PHPUnit\Framework\TestCase;
use Netric\Mail\DeliveryService;
use Netric\Mail\DeliveryServiceFactory;

/**
 * @group integration
 */
class DeliveryServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sl = $account->getServiceManager();
        $this->assertInstanceOf(
            DeliveryService::class,
            $sl->get(DeliveryServiceFactory::class)
        );
    }
}
