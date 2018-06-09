<?php

namespace NetricTest\EntityGroupings;

use Netric;

use PHPUnit\Framework\TestCase;

class LoaderFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\EntityGroupings\Loader',
            $sm->get('EntityGroupings_Loader')
        );

        $this->assertInstanceOf(
            'Netric\EntityGroupings\Loader',
            $sm->get('Netric\EntityGroupings\Loader')
        );
    }
}
