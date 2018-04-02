<?php
namespace Netric\PaymentGateway;

use Netric\Entity\ObjType\CustomerEntity;
use Netric\Entity\ObjType\PaymentProfileEntity;
use Netric\PaymentGateway\PaymentMethod\PaymentMethodInterface;


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
    private $gatewayUrl = "https://secure2.authorize.net/gateway/transact.dll";

    /**
     * Test Gateway URL
     *
     * @var string
     */
    private $testGatewayUrl = "https://test.authorize.net/gateway/transact.dll";

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
     * Create a customer payment profile for a given gateway
     *
     * We always store credit card information with the gateway since we
     * do not want to accept liability for securing credit cards on our system.
     *
     * @param CustomerEntity $customer Provide the gateway with needed customer data
     * @param PaymentMethodInterface $paymentMethod Method used for this payment profile
     * @return string
     */
    public function createPaymentProfile(CustomerEntity $customer, PaymentMethodInterface $paymentMethod) : string
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
}