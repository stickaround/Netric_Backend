<?php

namespace NetricTest\Application;

use Netric\Account\AccountSetup;
use Netric\Account\AccountSetupFactory;
use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class AccountSetupFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $serviceManager = Bootstrap::getAccount()->getServiceManager();

        $this->assertInstanceOf(
            AccountSetup::class,
            $serviceManager->get(AccountSetupFactory::class)
        );
    }
}
