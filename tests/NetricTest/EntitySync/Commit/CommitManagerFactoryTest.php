<?php

namespace NetricTest\EntitySync\Commit;

use Netric;

use PHPUnit\Framework\TestCase;

class CommitManagerFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\EntitySync\Commit\CommitManager',
            $sm->get('EntitySyncCommitManager')
        );

        $this->assertInstanceOf(
            'Netric\EntitySync\Commit\CommitManager',
            $sm->get('Netric\EntitySync\Commit\CommitManager')
        );
    }
}