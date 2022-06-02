<?php

declare(strict_types=1);

namespace Netric\Account\Billing;

use DateTime;
use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Log\LogInterface;
use Netric\PaymentGateway\ChargeResponse;
use Netric\PaymentGateway\PaymentGatewayInterface;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use RuntimeException;

/**
 * The account billing service is used to manage billing for each netric tennant (account)
 */
class AccountBillingService implements AccountBillingServiceInterface
{
    /**
     * The flat fee we charge per user per month
     */
    const PRICE_PER_USER = 19;

    /**
     * We want to log everything when it comes to billing
     */
    private LogInterface $log;

    /**
     * Used to get entities
     */
    private EntityLoader $entityLoader;

    /**
     * The main tennant account where billing and support for all other accounts will be handled
     */
    private string $mainAccountId;

    /**
     * The payment gateway used to charge the user
     */
    private PaymentGatewayInterface $paymentGateway;

    /**
     * Index for querying entities
     */
    private IndexInterface $entityIndex;

    /**
     * Loading accounts
     *
     * @var AccountContainerInterface
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Constructor for any dependencies
     *
     * @param LogInterface $log
     * @param Entityloader $entityLoader
     */
    public function __construct(
        LogInterface $log,
        Entityloader $entityLoader,
        string $mainAccountId,
        PaymentGatewayInterface $paymentGateway,
        IndexInterface $entityIndex,
        AccountContainerInterface $accountContainer
    ) {
        $this->log = $log;
        $this->entityLoader = $entityLoader;
        $this->mainAccountId = $mainAccountId;
        $this->paymentGateway = $paymentGateway;
        $this->entityIndex = $entityIndex;
        $this->accountContainer = $accountContainer;
    }

    /**
     * Loop through all active accounts and bill them if there is anything due bill them
     *
     * @return void
     */
    public function billAllDueAccounts(): void
    {
        $allActiveAccounts = $this->accountContainer->getAccountsToBeBilled();
        foreach ($allActiveAccounts as $accountId) {
            $account = $this->accountContainer->loadById($accountId);

            // This will only bill if there is an amount due
            $this->billAmountDue($account);
        }
    }

    /**
     * If an account is due for billing, bill them
     *
     * @param Account $account
     * @return bool True if success (either billed or skipped), otherwise false
     */
    public function billAmountDue(Account $account): bool
    {
        // If netric is running in an instance with no main account
        // then we don't charge montly fees.
        if (!$this->mainAccountId) {
            $this->log->info(
                "AccountBillingService::billAmountDue skipping for " .
                    $account->getName() .
                    ":" .
                    $account->getAccountId() .
                    " - because there is no mainAccountIt"
            );
            return true;
        }

        // Get the mainAccountContactId from $account
        $contactForAccount = $this->getContactForAccount($account);
        if (!$contactForAccount) {
            $this->log->warning(
                "AccountBillingService::billAmountDue skipping for " .
                    $account->getName() .
                    ":" .
                    $account->getAccountId() .
                    " - because the contact has not been set"
            );

            // Update account to past due so that on next login the user
            // is required to update the billing information.
            $account->setStatus(Account::STATUS_PASTDUE);
            $this->accountContainer->updateAccount($account);
            return false;
        }

        // Get the payment method for the contact
        $paymentProfile = $this->getDefaultPaymentProfile($contactForAccount->getEntityId());
        if (!$paymentProfile) {
            $this->log->warning(
                "AccountBillingService::billAmountDue skipping for " .
                    $account->getName() .
                    ":" .
                    $account->getAccountId() .
                    " - because there is no paymnet profile set"
            );

            // Update account to past due so that on next login the user
            // is required to update their payment details
            $account->setStatus(Account::STATUS_PASTDUE);
            $this->accountContainer->updateAccount($account);
            return false;
        }

        // Get the number of users for the account
        $numUsers = $this->getNumActiveUsers($account->getAccountId());

        // Create an invoice for the number of users
        $invoice = $this->createInvoice($contactForAccount->getEntityId(), $numUsers);

        // Charge the gateway for the invoice amount
        $chargeResponse = $this->paymentGateway->chargeProfile($paymentProfile, (int) $invoice->getValue('amount'));
        if ($chargeResponse->getStatus() != ChargeResponse::STATUS_APPROVED) {
            // Log it for debugging
            $this->log->error(
                'AccountBillingService::billAmountDue failed to bill for account=' .
                    $account->getAccountId() .
                    ', status=' .
                    $chargeResponse->getStatus() .
                    ', messages=' .
                    $chargeResponse->getMessagesText()
            );

            /*
             * TODO: Handle the failure gracefully
             * 1. Send an email to the account owner letting them know it failed
             * 2. Update the account to force them to update billing
             * 3. Try again in 24 hours, for 3 days
             */
            $account->setStatus(Account::STATUS_PASTDUE);
            $this->accountContainer->updateAccount($account);

            return false;
        }

        $this->log->info(
            "Successfully billed " .
                $account->getName() .
                ":" .
                $account->getAccountId() .
                " - transaction id: " .
                $chargeResponse->getTransactionId()
        );

        // Success! Now mark the invoice as paid

        // Set last billed to now
        $account->setBillingLastBilled(new DateTime());

        // Calculate the next time this account should be billed
        $nextBill = $account->calculateAndUpdateNextBillDate();
        $this->log->info(
            "AccountBillingService->billAmountDue: Set next bill date for " .
                $account->getName() .
                " to " . $nextBill->format("Y-m-d")
        );

        // Put the account in good standing
        $account->setStatus(Account::STATUS_ACTIVE);

        // Save changes
        $this->accountContainer->updateAccount($account);

        return true;
    }

    /**
     * Get the contact ID assoicated with the account we are billing
     *
     * The contact belongs to the $this->mainAccountId which can get a bit confusing,
     * but this is because all billing happens under one netric account, for all other
     * accounts so we can utilize the invoicing and billing capabilities of our own system.
     *
     * This may not exist for an account, and if that is the case the billing will fail which
     * means the account admin needs to log in and update billing details - where the contact
     * gets created for the account along with the payment profile.
     *
     * @param Account $account
     * @return EntityInterface|NULL if not found
     */
    public function getContactForAccount(Account $account): ?EntityInterface
    {
        $contactId = $account->getMainAccountContactId();
        if (!$contactId) {
            return null;
        }

        // Load the contact entity in the main account
        // This should never fail, but just in case throw an exception
        $contact = $this->entityLoader->getEntityById($contactId, $this->mainAccountId);
        if (!$contact) {
            throw new RuntimeException(
                'Contact ID: ' . $contactId .
                    ' was not found in account: ' .
                    $account->getAccountId()
            );
        }

        return $contact;
    }

    /**
     * Get the default payment profile for a contact in the mainAccount
     *
     * @param string $accountId
     * @return EntityInterface|null PaymentProfile interface if default is saved, otherwise null
     */
    private function getDefaultPaymentProfile(string $contactId): ?EntityInterface
    {
        $query = new EntityQuery(ObjectTypes::SALES_PAYMENT_PROFILE, $this->mainAccountId);
        $query->where('f_default')->equals(true);
        $query->andWhere('customer')->equals($contactId);
        $result = $this->entityIndex->executeQuery($query);
        if ($result->getTotalNum() < 1) {
            return null;
        };
        return $result->getEntity(0);
    }

    /**
     * Get the system user for the main account
     *
     * This is needed for any entity update operations in the main account.
     *
     * @return UserEntity
     */
    private function getMainAccountSystemUser(): UserEntity
    {
        return $this->entityLoader->getByUniqueName(
            ObjectTypes::USER,
            UserEntity::USER_SYSTEM,
            $this->mainAccountId
        );
    }

    /**
     * Determine how many active users we have in a given account
     *
     * @param string $accountId
     * @return int Number of non-system active users
     */
    public function getNumActiveUsers(string $accountId): int
    {
        $query = new EntityQuery(ObjectTypes::USER, $accountId);
        $query->andWhere('active')->equals(true);
        // We only charge for internal users, not public, system, or meta users
        $query->andWhere('type')->doesNotEqual(UserEntity::TYPE_PUBLIC);
        $query->andWhere('type')->doesNotEqual(UserEntity::TYPE_SYSTEM);
        $query->andWhere('type')->doesNotEqual(UserEntity::TYPE_META);
        $result = $this->entityIndex->executeQuery($query);
        return $result->getTotalNum();
    }

    /**
     * Create a new invoice
     *
     * @param string $contactId
     * @param int $numUsers
     * @return EntityInterface
     */
    private function createInvoice(string $contactId, int $numUsers): EntityInterface
    {
        // Get the system user
        $mainAccSystemUser = $this->getMainAccountSystemUser();

        $invoice = $this->entityLoader->create(ObjectTypes::INVOICE, $this->mainAccountId);
        $invoice->setValue('customer_id', $contactId);
        $invoice->setValue('name', 'Netric Account Usage');
        $invoice->setValue('amount', $numUsers * self::PRICE_PER_USER);
        $invoice->setValue('date_due', date('m/d/Y'));

        // TAXES:
        // Since this is a digital service, we do not need to charge a
        // sales tax. However, once we start working in Europe we'll want to
        // add a VAT tax based on the location.
        $this->entityLoader->save($invoice, $mainAccSystemUser);

        return $invoice;
    }

    /**
     * Get the name of the default payment profile for a contact
     *
     * @param Account $account The account of the current tennant
     * @param string $contactId The contact that owns the payment profile
     * @return string
     */
    public function getDefaultPaymentProfileName(Account $account, string $contactId): string
    {
        $query = new EntityQuery(ObjectTypes::SALES_PAYMENT_PROFILE, $this->mainAccountId);
        $query->where('f_default')->equals(true);
        $query->andWhere('customer')->equals($contactId);
        $result = $this->entityIndex->executeQuery($query);

        if ($result->getTotalNum() < 1) {
            return "No payment profile set for this account";
        }

        return $result->getEntity(0)->getName();
    }

    /**
     * Updates the old payment profiles f_default value to false
     *
     * @param string $contactId The contact that owns the payment profile
     */
    private function setAllOtherPaymentProfilesNotDefault(string $contactId, string $latestPaymentProfileId)
    {
        $query = new EntityQuery(ObjectTypes::SALES_PAYMENT_PROFILE, $this->mainAccountId);
        $query->where('f_default')->equals(true);
        $query->andWhere('customer')->equals($contactId);
        $query->andWhere('entity_id')->doesNotEqual($latestPaymentProfileId);
        $result = $this->entityIndex->executeQuery($query);

        $num = $result->getNum();
        for ($idx = 0; $idx < $num; $idx++) {
            $paymentProfile = $result->getEntity($idx);
            $paymentProfile->setValue("f_default", false);
            $this->entityLoader->save($paymentProfile, $this->getMainAccountSystemUser());
        }
    }

    /**
     * Save a default payment profile for a contact
     *
     * @param string $contactId The contact that owns the payment profile in the mainAccount (not an entity in the current tennant)
     * @param CreditCard $card The credit card that will be using to bill the customer
     * @return string The name of the payment profile
     */
    public function saveDefaultPaymentProfile(string $contactId, CreditCard $card): string
    {
        $paymentProfile = null;
        $contact = $this->entityLoader->getEntityById($contactId, $this->mainAccountId);

        // Get the default payment profile, or create a new one if none exist
        $paymentProfile = $this->getDefaultPaymentProfile($contactId);
        if (!$paymentProfile) {
            $paymentProfile = $this->entityLoader->create(ObjectTypes::SALES_PAYMENT_PROFILE, $this->mainAccountId);
        }

        // Create a payment profile with the paymentGateway
        $profileToken = $this->paymentGateway->createPaymentProfileCard($contact, $card);
        if (!$profileToken) {
            $errorMessage = "AccountBillingService::saveDefaultPaymentProfile failed on createPaymentProfileCard" .
                " for account={$this->mainAccountId}, contact=$contactId: " .
                $this->paymentGateway->getLastError();
            $this->log->error($errorMessage);

            // Exit ungracefully because this should never happen
            throw new RuntimeException($errorMessage);
        }

        // Get the system user for saving
        $mainAccSystemUser = $this->getMainAccountSystemUser();

        // Setup the payment profile details
        $paymentProfile->setValue('name', 'Card ending in ...' . substr($card->getCardNumber(), -4));
        $paymentProfile->setValue('token', $profileToken);
        $paymentProfile->setValue('f_default', true);
        $paymentProfile->setvalue('customer', $contactId);

        /*
         * Save the profile. This will throw an exception if it fails - that should
         * not happen but if it does we'll log it and then pass along the exception
         */
        try {
            $paymentProfileId = $this->entityLoader->save($paymentProfile, $mainAccSystemUser);
        } catch (RuntimeException $ex) {
            $errorMessage = "AccountBillingService::saveDefaultPaymentProfile failed to save the profile" .
                " for account={$this->mainAccountId}, contact=$contactId: " .
                $ex->getMessage();
            $this->log->error($errorMessage);

            // Exit ungracefully because this should never happen
            throw new RuntimeException($errorMessage);
        }

        // Make sure any other payment profiles setup are not set as default
        $this->setAllOtherPaymentProfilesNotDefault($contactId, $paymentProfileId);

        // Return a friendly name
        return $paymentProfile->getName();
    }
}
