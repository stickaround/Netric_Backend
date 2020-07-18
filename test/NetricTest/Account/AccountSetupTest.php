<?php

namespace NetricTest\Account;

use Netric\Account\AccountSetup;
use Netric\Application\Application;
use Netric\Application\DataMapperInterface;
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
        $accountSetup = new AccountSetup($dataMapper);

        $uniqueName = $accountSetup->getUniqueAccountName('My Company!$%#_-.');
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

        $accountSetup = new AccountSetup($dataMapper);

        $uniqueName = $accountSetup->getUniqueAccountName('Test');
        $this->assertEquals('test3', $uniqueName);
    }
}
