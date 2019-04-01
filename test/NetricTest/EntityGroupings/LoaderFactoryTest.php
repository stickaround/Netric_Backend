<?php

namespace NetricTest\EntityGroupings;

use Netric;
use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;
use Netric\EntityGroupings\Loader;
use Netric\EntityGroupings\LoaderFactory;

class LoaderFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            Loader::class,
            $sm->get(LoaderFactory::class)
        );
    }
}
