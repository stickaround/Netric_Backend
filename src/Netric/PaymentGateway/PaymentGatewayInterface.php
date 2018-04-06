<?php
namespace Netric\PaymentGateway;

use Netric\Entity\ObjType\CustomerEntity;
use Netric\Entity\ObjType\PaymentProfileEntity;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use Netric\PaymentGateway\PaymentMethod\BankAccount;

interface PaymentGatewayInterface
{
    /**
     * Create a customer payment profile using a credit or debit card
     *
     * We always store credit card information with the gateway since we
     * do not want to accept liability for securing credit cards on our system.
     *
     * @param CustomerEntity $customer Provide the gateway with needed customer data
     * @param CreditCard $card Credit card
     * @return string
     */
    public function createPaymentProfileCard(CustomerEntity $customer, CreditCard $card) : string;

    /**
     * Create a customer payment profile using a bank account
     *
     * We always store bank account information with the gateway since we
     * do not want to accept liability for securing bank accounts on our system.
     *
     * @param CustomerEntity $customer Provide the gateway with needed customer data
     * @param BankAccount $bankAccount Bank account details such as routing number and account number
     * @return string
     */
    public function createPaymentProfileBankAccount(CustomerEntity $customer, BankAccount $bankAccount) : string;

    /**
     * Charge a payment profile
     *
     * @param PaymentProfileEntity $paymentProfile
     * @param float $amount Amount to charge the customer
     * @return ChargeResponse
     */
    public function chargeProfile(PaymentProfileEntity $paymentProfile, float $amount) : ChargeResponse;

    /**
     * Charge a credit or debit card directly
     *
     * @param CreditCard $card
     * @param float $amount
     * @return ChargeResponse
     */
    public function chargeCard(CreditCard $card, float $amount) : ChargeResponse;

    /**
     * If a gateway operation fails, it will store the error
     *
     * @return string
     */
    public function getLastError() : string;
}