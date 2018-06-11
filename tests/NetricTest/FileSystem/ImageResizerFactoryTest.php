<?php
namespace NetricTest\FileSystem;

use Netric\FileSystem\ImageResizer;
use PHPUnit\Framework\TestCase;

class ImageResizerFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            ImageResizer::class,
            $sm->get('Netric\FileSystem\ImageResizer')
        );
    }
}