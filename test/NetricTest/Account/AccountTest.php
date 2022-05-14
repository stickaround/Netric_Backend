<?php

/**
 * Test core netric application class
 */

namespace NetricTest\Account;

use DateTime;
use Netric\Account\Account;
use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Application\Application;
use Ramsey\Uuid\Uuid;

/**
 * @group integration
 */
class AccountTest extends TestCase
{
    public function testGetServiceManager()
    {
        $account = Bootstrap::getAccount();

        $this->assertInstanceOf(ServiceLocatorInterface::class, $account->getServiceManager());
    }

    public function testGetApplication()
    {
        $account = Bootstrap::getAccount();
        $this->assertInstanceOf(Application::class, $account->getApplication());
    }

    public function testToAndFromArray()
    {
        $mockApp = $this->createMock(Application::class);
        $account = new Account($mockApp);

        $dataToTest = [
            'account_id' => Uuid::uuid4()->toString(),
            'name' => 'testcorp',
            'org_name' => 'Test Corp',
            'status' => Account::STATUS_ACTIVE,
            'status_name' => $account->getStatusName(),
            'main_account_contact_id' => Uuid::uuid4()->toString(),
            'billing_last_billed' => date('Y-m-d'),
            'billing_next_bill' => date('Y-m-d', strtotime('next month')),
            'billing_month_interval' => 1,
        ];

        $account->fromArray($dataToTest);
        $this->assertEquals($dataToTest, $account->toArray());
    }

    /**
     * Check that we set the next date appropriately based on settings
     *
     * @return void
     */
    public function testCalculateAndUpdateNextBillDate(): void
    {
        $mockApp = $this->createMock(Application::class);
        $account = new Account($mockApp);

        // Run without setting any of the bill dates - should start next month
        $setTo = $account->calculateAndUpdateNextBillDate();
        $this->assertEquals(date("Y-m-d", strtotime("+1 month")), $setTo->format("Y-m-d"));

        // Set the last bill to the past and make sure we step up only one month at a time
        $threeMonthsAgo = date("Y-m-d", strtotime("-3 months"));
        $account->setBillingLastBilled(new DateTime($threeMonthsAgo));
        $setTo = $account->calculateAndUpdateNextBillDate();
        $this->assertEquals(date("Y-m-d", strtotime("-2 months")), $setTo->format("Y-m-d"));

        // Now set interval to bill every 6 months
        $account->setBillingLastBilled(new DateTime());
        $account->setBillingMonthInterval(6);
        $setTo = $account->calculateAndUpdateNextBillDate();
        $this->assertEquals(date("Y-m-d", strtotime("+6 months")), $setTo->format("Y-m-d"));
    }
}
