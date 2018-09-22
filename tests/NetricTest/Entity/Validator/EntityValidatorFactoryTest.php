<?php

/**
 * Test the Entity Validator service factory
 */
namespace NetricTest\Entity\Validator;

use Netric\Entity\Validator\EntityValidator;
use Netric\Entity\Validator\EntityValidatorFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;

class FileSystemFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            EntityValidator::class,
            $sm->get(EntityValidatorFactory::class)
        );
    }
}
