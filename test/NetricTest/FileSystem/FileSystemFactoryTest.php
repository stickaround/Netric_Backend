<?php

/**
 * Test the FileSystem service factory
 */

namespace NetricTest\FileSystem;

use Netric;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class FileSystemFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\FileSystem\FileSystem',
            $sm->get('Netric\FileSystem\FileSystem')
        );
    }
}
