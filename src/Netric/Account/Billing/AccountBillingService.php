<?php

declare(strict_types=1);

namespace Netric\Account\Billing;

use Netric\Account\Account;
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
    const PRICE_PER_USER = 10;

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
        IndexInterface $entityIndex
    ) {
        $this->log = $log;
        $this->entityLoader = $entityLoader;
        $this->mainAccountId = $mainAccountId;
        $this->paymentGateway = $paymentGateway;
        $this->entityIndex = $entityIndex;
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

        // TODO: check last billed as a safeguard to make sure we do not double bill people

        // Get the mainAccountContactId from $account
        $contactForAccount = $this->getContactForAccount($account);

        // Get the payment method for the contact
        $paymentProfile = $this->getDefaultPaymentProfile($this->mainAccountId, $contactForAccount->getEntityId());

        // Get the number of users for the account
        $numUsers = $this->getNumActiveUsers($account->getAccountId());

        // Create an invoice for the number of users
        $invoice = $this->createInvoice($account->getAccountId(), $contactForAccount->getEntityId(), $numUsers);

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
     * @return EntityInterface
     */
    public function getContactForAccount(Account $account): EntityInterface
    {
        $contactId = $account->getMainAccountContactId();
        if (!$contactId) {
            throw new RuntimeException(
                'No contact was set for this account: ' .
                    $account->getAccountId()
            );
        }

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
     * Get the default payment profile for a user
     *
     * @param string $accountId
     * @param string $contactId
     * @return EntityInterface
     */
    public function getDefaultPaymentProfile(string $accountId, string $contactId): EntityInterface
    {
        $query = new EntityQuery(ObjectTypes::SALES_PAYMENT_PROFILE, $accountId);
        $query->where('f_default')->equals(true);
        $query->andWhere('customer')->equals($contactId);
        $result = $this->entityIndex->executeQuery($query);
        if ($result->getTotalNum() < 1) {
            throw new RuntimeException(
                'Could not find a default payment profile for account=' .
                    $accountId .
                    ', contact_id=' .
                    $contactId
            );
        };
        return $result->getEntity(0);
    }

    /**
     * Determine how many active users we have with a simple query
     *
     * @param string $accountId
     * @return int Number of non-system active users
     */
    public function getNumActiveUsers(string $accountId): int
    {
        $query = new EntityQuery(ObjectTypes::USER, $accountId);
        $query->andWhere('active')->equals(true);
        $query->andWhere('name')->doesNotEqual(UserEntity::USER_ANONYMOUS);
        $query->andWhere('name')->doesNotEqual(UserEntity::USER_CURRENT);
        $query->andWhere('name')->doesNotEqual(UserEntity::USER_SYSTEM);
        $query->andWhere('name')->doesNotEqual(UserEntity::USER_WORKFLOW);
        $result = $this->entityIndex->executeQuery($query);
        return $result->getTotalNum();
    }

    /**
     * Create a new invoice
     *
     * @param string $accountId
     * @param string $contactId
     * @param int $numUsers
     * @return EntityInterface
     */
    private function createInvoice(string $accountId, string $contactId, int $numUsers): EntityInterface
    {
        // Get the system user
        $mainAccSystemUser = $this->entityLoader->getByUniqueName(
            ObjectTypes::USER,
            UserEntity::USER_SYSTEM,
            $this->mainAccountId
        );

        $invoice = $this->entityLoader->create(ObjectTypes::INVOICE, $accountId);
        $invoice->setValue('customer_id', $contactId);
        $invoice->setValue('name', 'Netric Account Usage');
        $invoice->setValue('amount', $numUsers * self::PRICE_PER_USER);
        $invoice->setValue('date_due', date('m/d/Y'));
        // TODO: Add sales and VAT tax (yikes)
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
            return "No payment profile set for this account: " . $account->getName();
        }

        return $result->getEntity(0)->getName();
    }

    /**
     * Updates the old payment profiles f_default value to false
     *
     * @param Account $account The account of the current tennant
     * @param string $contactId The contact that owns the payment profile
     */
    public function updateOtherPaymentProfile(Account $account, string $contactId, string $latestPaymentProfileId)
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
            $this->entityLoader->save($paymentProfile, $account->getSystemUser());
        }
    }

    /**
     * Gets the main account id that is set for this account billing.
     * 
     * @param Account $account The account of the current tennant
     * @param string $contactId The contact that owns the payment profile
     * @param CreditCard $card The credit card that will be using to bill the customer
     * @return string
     */
    public function savePaymentProfile(Account $account, string $contactId, CreditCard $card): string
    {
        $paymentProfile = null;
        try {
            $contact = $this->entityLoader->getEntityById($contactId, $account->getAccountId());
            $paymentProfile = $this->getDefaultPaymentProfile($this->mainAccountId, $contactId);
            $profileToken = $this->paymentGateway->createPaymentProfileCard($contact, $card);
        } catch (RuntimeException $ex) {
            $paymentProfile = $this->entityLoader->create(ObjectTypes::SALES_PAYMENT_PROFILE, $this->mainAccountId);
        }

        // Setup the payment profile details
        $paymentProfile->setValue('name', 'Card ending in ...' . substr($card->getCardNumber(), -4));
        $paymentProfile->setValue('token', $profileToken);
        $paymentProfile->setValue('f_default', true);
        $paymentProfile->setvalue('customer', $contactId);

        try {
            $paymentProfileId = $this->entityLoader->save($paymentProfile, $account->getSystemUser());
            $this->updateOtherPaymentProfile($account, $contactId, $paymentProfileId);

            return $paymentProfile->getName();
        } catch (RuntimeException $ex) {
            $errorMessage = "AccountBillingService::savePaymentProfile failed saving payment profile={$account->getAccountId()}. " . $ex->getMessage();
            $this->log->error($errorMessage);

            throw new RuntimeException($errorMessage);
        }
    }
}
