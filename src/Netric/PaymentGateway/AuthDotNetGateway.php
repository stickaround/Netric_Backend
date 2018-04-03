<?php
namespace Netric\PaymentGateway;

use Netric\Entity\ObjType\CustomerEntity;
use Netric\Entity\ObjType\PaymentProfileEntity;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use Netric\PaymentGateway\PaymentMethod\BankAccount;


class AuthDotNetGateway implements PaymentGatewayInterface
{
    /**
     * Authorize.net login - set in constructor
     *
     * @var string
     */
    private $authLoginId;

    /**
     * Authorize.net private key - set in constructor
     *
     * @var string
     */
    private $authTransKey;

    /**
     * Gateway URL
     *
     * @var string
     */
    private $gatewayUrl = "https://api.authorize.net/xml/v1/request.api";

    /**
     * Test Gateway URL
     *
     * @var string
     */
    private $testGatewayUrl = "https://apitest.authorize.net/xml/v1/request.api";

    /**
     * Last Transaction Id
     *
     * @var string
     */
    public $respTransId = null;

    /**
     * Last Transaction reason
     *
     * @var string
     */
    public $respReason = null;

    /**
     * Full text from response
     *
     * @var string
     */
    public $respFull = null;

    /**
     * Class constructor
     *
     * @param string $loginId the unique authorize.net login
     * @param string $transactionKey the assigned transaction key from authorize.net
     */
    function __construct($loginId, $transactionKey)
    {
        $this->authLoginId = $loginId;
        $this->authTransKey = $transactionKey;
    }

    /**
     * Create a customer payment profile using a credit card
     *
     * We always store credit card information with the gateway since we
     * do not want to accept liability for securing credit cards on our system.
     *
     * @param CustomerEntity $customer Provide the gateway with needed customer data
     * @param CreditCard $card Credit card
     * @return string
     */
    public function createPaymentProfileCard(CustomerEntity $customer, CreditCard $card) : string
    {

    }

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
    public function createPaymentProfileBankAccount(CustomerEntity $customer, BankAccount $bankAccount) : string
    {

    }

    /**
     * Charge a payment profile
     *
     * @param PaymentProfileEntity $paymentProfile
     * @param float $amount Amount to charge the customer
     * @return string Transaction ID which can be used to reverse/refund
     */
    public function chargeProfile(PaymentProfileEntity $paymentProfile, float $amount) : string
    {

    }

    /**
     * Charge a credit or debit card directly
     *
     * @param CreditCard $card
     * @param float $amount
     * @return string Transaction ID which can be used to reverse/refund
     */
    public function chargeCard(CreditCard $card, float $amount) : string
    {

    }
}