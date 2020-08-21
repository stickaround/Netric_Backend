<?php

use Netric\Entity\EntityLoaderFactory;
use Netric\PaymentGateway\SystemPaymentGatewayFactory;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use Netric\EntityDefinition\ObjectTypes;
use RuntimeException;

/**
 * Perform entity maintenance
 */
$account = $this->getAccount();
if (!$account) {
    throw new \RuntimeException("This must be run only against a single account");
}

if ($accoun->getName() != 'aereus') {
    return true;
}

$AEREUS_CONTACT_ID = '00000000-0000-0000-0000-000006b2d6ec';

$paymentGateway = $account->getServiceManager()->get(SystemPaymentGatewayFactory::class);
$entityLoader = $account->getServiceManager()->get(EntityLoaderFactory::class);

// Get the aereus contact and update the info
$contact = $entityloader->getEntityById($AEREUS_CONTACT_ID, $account->getAccountId());
$contact->setValue('billing_first_name', 'Sky');
$contact->setValue('billing_last_name', 'Stebnicki');
$contact->setValue('billing_street', '1415 2nd Ave');
$contact->setValue('billing_street2', 'Unit 1410');
$contact->setValue('billing_city', 'Seattle');
$contact->setValue('billing_state', 'Washington');
$contact->setValue('billing_zip', '98101');
$entityLoader->save($entityLoader, $account->getSystemUser());

// Create the billing credit card
$card = new CreditCard();
$card->setCardNumber('4111111111111111');
$card->setExpiration(2025, 07);
$card->setCardCode('762');
$profileToken = $paymentGateway->createPaymentProfileCard($contact, $card);

// Add a payment profile to the aereus account to use for billing later
$paymentProfile = $entityLoader->create(
    ObjectTypes::SALES_PAYMENT_PROFILE,
    $account->getAccountId()
);
$paymentProfile->setValue('token', $profileToken);
$paymentProfile->setValue('f_default', true);
$paymentProfile->setvalue('customer', $contact->getEntityId());
$entityLoader->save($paymentProfile, $account->getSystemUser());

// Run a test transaction just to see if things are working
$result = $paymentGateway->chargeProfile($paymentProfile, 20);
echo var_export($result, true);
