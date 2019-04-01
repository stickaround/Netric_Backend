<?php

namespace NetricTest\EntitySync\Commit;

use Netric;

use PHPUnit\Framework\TestCase;
use Netric\EntitySync\Commit\CommitManager;
use Netric\EntitySync\Commit\CommitManagerFactory;

class CommitManagerFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            CommitManager::class,
            $sm->get(CommitManagerFactory::class)
        );
    }
}
