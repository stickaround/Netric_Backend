<?php

namespace NetricTest;

use Netric;

use PHPUnit_Framework_TestCase;

class ConfigFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\Config',
            $sm->get('Config')
        );

        $this->assertInstanceOf(
            'Netric\Config',
            $sm->get('Netric\Config')
        );
    }
}