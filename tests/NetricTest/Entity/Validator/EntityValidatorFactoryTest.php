<?php

/**
 * Test the Entity Validator service factory
 */
namespace NetricTest\Entity\Validator;

use Netric\Entity\Validator\EntityValidator;
use Netric\Entity\Validator\EntityValidatorFactory;
use PHPUnit\Framework\TestCase;

class FileSystemFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            EntityValidator::class,
            $sm->get(EntityValidatorFactory::class)
        );
    }
}
