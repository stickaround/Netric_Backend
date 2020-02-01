<?php

namespace NetricTest\EntityGroupings;

use Netric;
use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityGroupings\GroupingLoaderFactory;

class LoaderFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            GroupingLoader::class,
            $sm->get(GroupingLoaderFactory::class)
        );
    }
}
