<?php
namespace Netric\PaymentGateway;

use Netric\Entity\ObjType\CustomerEntity;
use Netric\Entity\ObjType\PaymentProfileEntity;
use Netric\PaymentGateway\PaymentMethod\PaymentMethodInterface;

interface PaymentGatewayInterface
{
    /**
     * Create a customer payment profile for a given gateway
     *
     * We always store credit card information with the gateway since we
     * do not want to accept liability for securing credit cards on our system.
     *
     * @param CustomerEntity $customer Provide the gateway with
     *                          customer data like name and email
     * @param PaymentMethodInterface $paymentMethod Method used for this payment profile
     * @return string
     */
    public function createPaymentProfile(CustomerEntity $customer, PaymentMethodInterface $paymentMethod) : string;

    /**
     * Charge a payment profile
     *
     * @param PaymentProfileEntity $paymentProfile
     * @param float $amount Amount to charge the customer
     * @return string
     */
    public function chargeProfile(PaymentProfileEntity $paymentProfile, float $amount) : string;
}