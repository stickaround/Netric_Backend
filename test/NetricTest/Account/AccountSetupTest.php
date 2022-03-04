<?php

namespace NetricTest\Account;

use Netric\Account\AccountContainer;
use Netric\Account\AccountSetup;
use Netric\Application\DataMapperInterface;
use Netric\Entity\EntityLoader;
use PHPUnit\Framework\TestCase;

/**
 * Test account setup functions
 */
class AccountSetupTest extends TestCase
{
    public function testGetUniqueAccountName()
    {
        $dataMapper = $this->getMockBuilder(DataMapperInterface::class)->getMock();
        $dataMapper->method('getAccountByName')->willReturn(null);
        $accountContainer = $this->createMock(AccountContainer::class);
        $entityLoader = $this->createMock(EntityLoader::class);
        $accountSetup = new AccountSetup(
            $dataMapper,
            $accountContainer,
            [],
            $entityLoader,
            "TEST-MAIN-UUID"
        );

        $uniqueName = $accountSetup->getUniqueAccountName('My Company!$%#-.');
        $this->assertEquals('mycompany', $uniqueName);
    }

    public function testGetUniqueAccountNameDuplicate()
    {
        $accData1 = ['id' => 1, 'name' => 'test'];
        $accData2 = ['id' => 2, 'name' => 'test2'];
        $accData3 = ['id' => 3, 'name' => 'notrelated'];

        $dataMapper = $this->getMockBuilder(DataMapperInterface::class)->getMock();;
        $dataMapper->method('getAccountByName')->willReturn($accData1);
        $dataMapper->method('getAccounts')->willReturn([$accData1, $accData2, $accData3]);

        $accountContainer = $this->createMock(AccountContainer::class);
        $entityLoader = $this->createMock(EntityLoader::class);

        $accountSetup = new AccountSetup(
            $dataMapper,
            $accountContainer,
            [],
            $entityLoader,
            "TEST-MAIN-UUID"
        );

        $uniqueName = $accountSetup->getUniqueAccountName('Test');
        $this->assertNotEquals('test', $uniqueName);
    }
}
