<?php

declare(strict_types=1);

namespace NetricTest\Account\Billing;

use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Account\Billing\AccountBillingService;
use Netric\Account\Billing\AccountBillingServiceInterface;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\ContactEntity;
use Netric\Entity\ObjType\PaymentProfileEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery\Results;
use Netric\Log\LogInterface;
use Netric\PaymentGateway\PaymentGatewayInterface;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use Ramsey\Uuid\Uuid;

/**
 * Undocumented class
 */
class AccountBillingServiceTest extends TestCase
{
    /**
     * Mock dependencies
     */
    private LogInterface $mockLog;
    private EntityLoader $mockEntityLoader;
    private PaymentGatewayInterface $mockPaymentGateway;
    private IndexInterface $mockEntityIndex;
    private EntityInterface $mockInvoice;
    private AccountContainerInterface $mockAccountContainer;

    /**
     * Test values
     */
    const TEST_MAIN_ACCOUNT_ID = 'UUID-MAIN-ACCOUNT';
    const TEST_TENNANT_ACCOUNT_ID = 'UUID-TENNANT-ACCOUNT';
    const TEST_ACCOUNT_CONTACT_ID = 'UUID-CONTACTID-FOR-TENNANT';
    const NUM_USERS = 10;

    /**
     * Test service (with mock dependencies)
     */
    private AccountBillingServiceInterface $accountBilling;

    /**
     * Setup class with mocks that can later be configured
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->mockLog = $this->createMock(LogInterface::class);
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockPaymentGateway = $this->createMock(PaymentGatewayInterface::class);
        $this->mockEntityIndex = $this->createMock(IndexInterface::class);
        $this->mockInvoice = $this->createMock(EntityInterface::class);
        $this->mockAccountContainer = $this->createMock(AccountContainerInterface::class);

        // Construct the service with mocks so we can configure and test them later
        $this->accountBilling = new AccountBillingService(
            $this->mockLog,
            $this->mockEntityLoader,
            self::TEST_MAIN_ACCOUNT_ID,
            $this->mockPaymentGateway,
            $this->mockEntityIndex,
            $this->mockAccountContainer
        );

        /**
         * Now configure mocks
         */

        /*
         * Mock getting the contact in the main account that represents the tennant account
         * In netric each account has a customer/contact account in the main account
         * (aereus account) to make billing and support possible through netric itself
         */
        $mockContact = $this->createMock(ContactEntity::class);
        $mockContact->method('getEntityId')->willReturn(self::TEST_ACCOUNT_CONTACT_ID);
        $this->mockEntityLoader->method('getEntityById')
            ->with(self::TEST_ACCOUNT_CONTACT_ID, self::TEST_MAIN_ACCOUNT_ID)
            ->will($this->returnValue($mockContact));

        /*
         * Mock results for the two queries run:
         *  - First gets the default payment profile
         *  - Second gets the number of active users
         */
        $paymentProfile = $this->createMock(PaymentProfileEntity::class);
        $paymentProfile->method('getName')->willReturn('Card ending in ....1111');
        $result1 = $this->createMock(Results::class);
        $result1->method('getTotalNum')->willReturn(1);
        $result1->method('getEntity')->willReturn($paymentProfile);

        $result2 = $this->createMock(Results::class);
        $result2->method('getTotalNum')->willReturn(10);

        $this->mockEntityIndex->method('executeQuery')
            ->will($this->onConsecutiveCalls($result1, $result2));

        // Mock getting the system users of the main account
        $mockSystemUser = $this->createMock(UserEntity::class);
        $this->mockEntityLoader->method('getByUniqueName')
            ->with(ObjectTypes::USER, UserEntity::USER_SYSTEM, self::TEST_MAIN_ACCOUNT_ID)
            ->will($this->returnValue($mockSystemUser));

        $this->mockInvoice->method('getValue')->will(
            $this->returnValueMap([
                ['amount', self::NUM_USERS * AccountBillingService::PRICE_PER_USER] // Simulate $200 charge
            ])
        );
    }

    /**
     * Make sure a successful billing attempt works
     *
     * @return void
     */
    public function testBillAmountDue(): void
    {
        /*
         * Create a mock account that returns the test id and contact id
         * These would set when the account is creatd and when the user updates billing info
         */
        $mockAccount = $this->createMock(Account::class);
        $mockAccount->method('getName')->willReturn('test');
        $mockAccount->method('getAccountId')->willReturn(self::TEST_TENNANT_ACCOUNT_ID);
        $mockAccount->method('getMainAccountContactId')->willReturn(self::TEST_ACCOUNT_CONTACT_ID);


        // Mock Creation of the invoice
        $this->mockEntityLoader->method('create')
            ->with(ObjectTypes::INVOICE, self::TEST_MAIN_ACCOUNT_ID)
            ->will($this->returnValue($this->mockInvoice));

        // Make sure chargeProfile gets called
        $this->mockPaymentGateway->expects($this->once())
            ->method('chargeProfile')
            ->with(
                $this->isInstanceOf(PaymentProfileEntity::class),
                $this->equalTo(self::NUM_USERS * AccountBillingService::PRICE_PER_USER)
            );
        $this->accountBilling->billAmountDue($mockAccount);
    }

    /**
     * Make sure account billing returns false if there is no contact for the account
     *
     * @return void
     */
    public function testBillAmountDueMissingContactReturnsFalse(): void
    {
        /*
         * Create a mock account that returns the test id but the contact is not set so
         * getMainAccountContactId will return an empty string.
         */
        $mockAccount = $this->createMock(Account::class);
        $mockAccount->method('getAccountId')->willReturn(self::TEST_TENNANT_ACCOUNT_ID);

        // This should fail because we did not mock getContactForAccount()
        $this->assertFalse($this->accountBilling->billAmountDue($mockAccount));
    }

    /**
     * Make sure that we can get the default payment profile name
     *
     * @return void
     */
    public function testGetDefaultPaymentProfileName(): void
    {
        // Create a mock account
        $mockAccount = $this->createMock(Account::class);
        $mockAccount->method('getName')->willReturn('testaccount');

        $profileName = $this->accountBilling->getDefaultPaymentProfileName($mockAccount, self::TEST_ACCOUNT_CONTACT_ID);
        $this->assertEquals("Card ending in ....1111", $profileName);
    }

    /**
     * Make sure that we save a payment profile
     *
     * @return void
     */
    public function testSaveDefaultPaymentProfile(): void
    {
        // Create the billing credit card
        $card = new CreditCard();
        $card->setCardNumber('4111111111111111');
        $card->setExpiration(2025, 07);
        $card->setCardCode('762');

        $paymentProfileId = Uuid::uuid4()->toString();
        $this->mockEntityLoader->method('save')->willReturn($paymentProfileId);

        // Mock returning the profile token
        $this->mockPaymentGateway->method('createPaymentProfileCard')->willReturn("PAYMENTOKEN");

        $profileName = $this->accountBilling->saveDefaultPaymentProfile(self::TEST_ACCOUNT_CONTACT_ID, $card);
        $this->assertEquals("Card ending in ....1111", $profileName);
    }

    /**
     * Make sure that we can create a new payment profile if none exists already
     *
     * @return void
     */
    public function testSaveDefaultPaymentProfileShouldCreateNewPaymentProfile(): void
    {
        /// When calling getDefaultPaymentProfile it will return null
        $mockEntityResult = $this->createMock(Results::class);
        $mockEntityResult->method('getTotalNum')->willReturn(0);
        $this->mockEntityIndex->method('executeQuery')->willReturn($mockEntityResult);

        // Create the billing credit card
        $card = new CreditCard();
        $card->setCardNumber('4111111111111111');
        $card->setExpiration(2025, 07);
        $card->setCardCode('762');

        $paymentProfileId = Uuid::uuid4()->toString();
        $this->mockEntityLoader->method('save')->willReturn($paymentProfileId);

        // Mock the creating of payment profile entity since they mock query above returns none
        $paymentProfile = $this->createMock(PaymentProfileEntity::class);
        $this->mockEntityLoader->method('create')
            ->with(ObjectTypes::SALES_PAYMENT_PROFILE, self::TEST_MAIN_ACCOUNT_ID)
            ->will($this->returnValue($paymentProfile));

        // Mock returning the profile token
        $this->mockPaymentGateway->method('createPaymentProfileCard')->willReturn("PAYMENTOKEN");

        $profileName = $this->accountBilling->saveDefaultPaymentProfile(self::TEST_ACCOUNT_CONTACT_ID, $card);
        $this->assertEquals("Card ending in ....1111", $profileName);
    }
}
