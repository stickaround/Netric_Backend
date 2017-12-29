<?php
/**
 * Test the Entity Validator service factory
 */
namespace NetricTest\Entity\Validator;

use Netric;
use PHPUnit\Framework\TestCase;

class FileSystemFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Entity\Validator\EntityValidator',
            $sm->get('Netric\Entity\Validator\EntityValidator')
        );
    }
}