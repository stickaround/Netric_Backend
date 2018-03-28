<?php
namespace Netric\PaymentGateway;

interface PaymentGatewayInterface
{
    public function createPaymentProfile() : string;
    public function chargeProfile(string $customerProfileId, string $paymentProfileId, float $amount) : string;
}