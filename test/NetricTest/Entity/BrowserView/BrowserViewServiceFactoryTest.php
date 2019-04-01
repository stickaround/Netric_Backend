<?php
/**
 * Makes sure our service factory works
 */

namespace NetricTest\Entity\BrowserView;

use NetricTest;
use Netric;
use PHPUnit\Framework\TestCase;
use Netric\Entity\BrowserView\BrowserViewService;
use Netric\Entity\BrowserView\BrowserViewServiceFactory;

class BrowserViewServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $bvs = $sm->get(BrowserViewServiceFactory::class);
        $this->assertInstanceOf(BrowserViewService::class, $bvs);
    }
}
