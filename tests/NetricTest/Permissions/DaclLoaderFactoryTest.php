<?php
namespace NetricTest\Permissions;

use Netric;
use PHPUnit\Framework\TestCase;

class DaclLoaderactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Permissions\DaclLoader',
            $sm->get('Netric\Permissions\DaclLoader')
        );
    }
}
