<?php
/**
 * This script is used just to update the billing details for the Aereus
 * account until we have a good management page for this in settings.
 */
use Netric\Entity\EntityLoaderFactory;
use Netric\PaymentGateway\SystemPaymentGatewayFactory;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Account\AccountContainerFactory;
use RuntimeException;

$AEREUS_ACCOUNT_ID = '00000000-0000-0000-0000-00000000000c';
$AEREUS_CONTACT_ID = '00000000-0000-0000-0000-000006b2d6ec';

/**
 * Update the billing information for the aereus account, including adding
 * a credit card for payment details.
 */
$accountContainer = $this->getApplication()->getServiceManager()->get(AccountContainerFactory::class);
$account = $accountContainer->loadById($AEREUS_ACCOUNT_ID);
if (!$account) {
    // The aereus account could not be loaded
    throw new RuntimeException('Aereus account could not be found');
}

$paymentGateway = $account->getServiceManager()->get(SystemPaymentGatewayFactory::class);
$entityLoader = $account->getServiceManager()->get(EntityLoaderFactory::class);

// Get the aereus contact and update the info
$contact = $entityLoader->getEntityById($AEREUS_CONTACT_ID, $account->getAccountId());
$contact->setValue('billing_first_name', 'Sky');
$contact->setValue('billing_last_name', 'Stebnicki');
$contact->setValue('billing_street', '3860 Blossom Street');
$contact->setValue('billing_street2', '');
$contact->setValue('billing_city', 'Kissimmee');
$contact->setValue('billing_state', 'Florida');
$contact->setValue('billing_zip', '34746');
$entityLoader->save($contact, $account->getSystemUser());
echo "Saved billing details for aereus account";

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
$paymentProfile->setValue('name', 'Visa ending in ...1111');
$paymentProfile->setValue('token', $profileToken);
$paymentProfile->setValue('f_default', true);
$paymentProfile->setvalue('customer', $contact->getEntityId());
$entityLoader->save($paymentProfile, $account->getSystemUser());

// Below was the saved profile with Sky's card
// {"customer_profile_id":"2024121765","payment_profile_id":"2043916084"}

// Run a test transaction just to see if things are working
$result = $paymentGateway->chargeProfile($paymentProfile, 20);
echo var_export($result, true);
