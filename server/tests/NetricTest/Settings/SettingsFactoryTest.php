<?php
/**
 * Test the Settings service factory
 */
namespace NetricTest\Settings;

use Netric\Settings;
use PHPUnit\Framework\TestCase;

class SettingsFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Settings\Settings',
            $sm->get('Netric\Settings\Settings')
        );
    }
}