<?php

namespace NetricTest\Application;

use Netric\Application\DataMapperFactory;
use Netric\Application\DataMapperInterface;
use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $serviceManager = Bootstrap::getAccount()->getServiceManager();

        $this->assertInstanceOf(
            DataMapperInterface::class,
            $serviceManager->get(DataMapperFactory::class)
        );
    }

    public function testCreateServiceByAlias()
    {
        $serviceManager = Bootstrap::getAccount()->getServiceManager();

        $this->assertInstanceOf(
            DataMapperInterface::class,
            $serviceManager->get('Application_DataMapper')
        );
    }
}