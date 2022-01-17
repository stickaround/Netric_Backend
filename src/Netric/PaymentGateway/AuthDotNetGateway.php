<?php

namespace Netric\PaymentGateway;

use \net\authorize\api\constants\ANetEnvironment;
use Netric\Entity\ObjType\ContactEntity;
use Netric\Entity\ObjType\PaymentProfileEntity;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use Netric\PaymentGateway\PaymentMethod\BankAccount;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use RuntimeException;

/**
 * Process payments through the Authorize.net payment gateway
 */
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
     * Response code constants
     */
    const RESPONSE_OK = 'Ok';

    /**
     * Store the last error message
     *
     * @var string
     */
    private $lastErrorMessage = '';

    /**
     * Class constructor
     *
     * @param string $loginId the unique authorize.net login
     * @param string $transactionKey the assigned transaction key from authorize.net
     * @param string $gatewayUrl Optional override of the production url to hit (used mostly for tests)
     */
    public function __construct(string $loginId, string $transactionKey, string $gatewayUrl = '')
    {
        $this->authLoginId = $loginId;
        $this->authTransKey = $transactionKey;
        if (!empty($gatewayUrl)) {
            $this->gatewayUrl = $gatewayUrl;
        }
    }

    /**
     * Get the last error message received from the gateway
     *
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastErrorMessage;
    }

    /**
     * Create a customer payment profile using a credit card
     *
     * We always store credit card information with the gateway since we
     * do not want to accept liability for securing credit cards on our system.
     *
     * If you try to create a profile that already exists, this will return the
     * profile token.
     *
     * @param ContactEntity $customer Provide the gateway with needed customer data
     * @param CreditCard $card Credit card
     * @return string Encoded profile string with the customer and payment profile
     */
    public function createPaymentProfileCard(ContactEntity $customer, CreditCard $card): string
    {
        // Get auth for connecting to the merchant gateway
        $merchantAuth = $this->getMerchantAuth();

        // Create or get the customer profile id from authorize.net
        // They store each customer with individual profiles attached
        $customerProfileId = $this->createOrGetCustomerProfileId($customer);

        // Set the transaction's refId
        $refId = 'ref' . time();

        // Set credit card information for payment profile
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($card->getCardNumber());
        $creditCard->setExpirationDate($card->getExpiration("YYYY-MM"));
        $creditCard->setCardCode($card->getCardCode());
        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        // Create the Bill To info for new payment type
        $billTo = $this->getBillingAddressFromCustomer($customer);

        // Create a new CustomerPaymentProfile object
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType('individual');
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($paymentCreditCard);
        $paymentProfile->setDefaultpaymentProfile(true);

        // Assemble the complete transaction request
        $request = new AnetAPI\CreateCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);

        // Add an existing profile id to the request
        $request->setCustomerProfileId($customerProfileId);
        $request->setPaymentProfile($paymentProfile);
        $request->setValidationMode(($this->gatewayUrl === ANetEnvironment::SANDBOX) ? "testMode" : "liveMode");

        // Create the controller and get the response
        $controller = new AnetController\CreateCustomerPaymentProfileController($request);
        $response = $controller->executeWithApiResponse($this->gatewayUrl);

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return $this->encodeProfilesIntoToken(
                $customerProfileId,
                $response->getCustomerPaymentProfileId()
            );
        }

        $errorMessages = $response->getMessages()->getMessage();
        // Check for duplicate
        if ($errorMessages[0]->getCode() === "E00039") {
            // TODO: just get the existing profile id and return it
        }

        // There's another problem, return empty and set the local error
        $this->lastErrorMessage = "Could not create the profile: " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText();
        return '';
    }

    /**
     * Create a customer payment profile using a bank account
     *
     * We always store bank account information with the gateway since we
     * do not want to accept liability for securing bank accounts on our system.
     *
     * @param ContactEntity $customer Provide the gateway with needed customer data
     * @param BankAccount $bankAccount Bank account details such as routing number and account number
     * @return string
     */
    public function createPaymentProfileBankAccount(ContactEntity $customer, BankAccount $bankAccount): string
    {
        // Get auth for connecting to the merchant gateway
        $merchantAuth = $this->getMerchantAuth();

        // Create or get the customer profile id from authorize.net
        // They store each customer with individual profiles attached
        $customerProfileId = $this->createOrGetCustomerProfileId($customer);

        // Set the transaction's refId
        $refId = 'ref' . time();
        // Create a Customer Profile Request
        $apiBankAccount = new AnetAPI\BankAccountType();
        $apiBankAccount->setAccountType($bankAccount->getAccountType());
        // TODO: see eCheck documentation for proper echeck type to use for each situation
        $apiBankAccount->setEcheckType('WEB');
        $apiBankAccount->setRoutingNumber($bankAccount->getRoutingNumber());
        $apiBankAccount->setAccountNumber($bankAccount->getAccountNumber());
        $apiBankAccount->setNameOnAccount($bankAccount->getNameOnAccount());
        $apiBankAccount->setBankName($bankAccount->getBankName());
        $paymentBankAccount = new AnetAPI\PaymentType();
        $paymentBankAccount->setBankAccount($apiBankAccount);

        // Create the Bill To info for new payment type
        $billTo = $this->getBillingAddressFromCustomer($customer);

        // Create a new CustomerPaymentProfile object
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType('individual');
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($paymentBankAccount);
        $paymentProfile->setDefaultpaymentProfile(true);
        $paymentProfiles[] = $paymentProfile;

        // Assemble the complete transaction request
        $request = new AnetAPI\CreateCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);

        // Add an existing profile id to the request
        $request->setCustomerProfileId($customerProfileId);
        $request->setPaymentProfile($paymentProfile);
        //$request->setValidationMode(($this->gatewayUrl === ANetEnvironment::SANDBOX) ? "testMode" : "liveMode");

        // Create the controller and get the response
        $controller = new AnetController\CreateCustomerPaymentProfileController($request);
        $response = $controller->executeWithApiResponse($this->gatewayUrl);

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return $this->encodeProfilesIntoToken(
                $customerProfileId,
                $response->getCustomerPaymentProfileId()
            );
        }

        $errorMessages = $response->getMessages()->getMessage();
        // Check for duplicate
        if ($errorMessages[0]->getCode() === "E00039") {
            // TODO: just get the existing profile id and return it
        }

        // There's another problem, return empty and set the local error
        $this->lastErrorMessage = "Could not create the profile: " . $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText();
        return '';
    }

    /**
     * Either create a new customer profile on the gateway, or get an existing
     *
     * @return string
     */
    private function createOrGetCustomerProfileId(ContactEntity $customer): string
    {
        // Get auth for connecting to the merchant gateway
        $merchantAuth = $this->getMerchantAuth();

        // Set the transaction's refId
        $refId = 'ref' . time();

        // Create a new CustomerProfileType and add the payment profile object
        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setDescription($customer->getName());
        $customerProfile->setMerchantCustomerId(
            $this->getUniqueIdFromCustomer($customer)
        );
        $customerProfile->setEmail($customer->getValue('email'));

        // Assemble the complete transaction request
        $request = new AnetAPI\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setRefId($refId);
        $request->setProfile($customerProfile);

        // Create the controller and get the response
        $controller = new AnetController\CreateCustomerProfileController($request);
        $response = $controller->executeWithApiResponse($this->gatewayUrl);

        if (($response != null) && ($response->getMessages()->getResultCode() == self::RESPONSE_OK)) {
            // Get the profile id
            return $response->getCustomerProfileId();
        }

        // The response was not a success or it would have returned above
        $errorMessages = $response->getMessages()->getMessage();

        // E00039 means the profile already exists, just return it
        if ($errorMessages[0]->getCode() == "E00039") {
            // extract the profile id out of the message (a bit of a hack but the only way I could find)
            return filter_var($errorMessages[0]->getText(), FILTER_SANITIZE_NUMBER_INT);
        }

        // Soemthing went terribly wrong, throw an exception
        throw new RuntimeException($errorMessages[0]->getText(), $errorMessages[0]->getCode());
    }

    /**
     * Delete a payment profile
     *
     * @param $profileToken
     * @return bool false on failure, true on success
     */
    public function deleteProfile(string $profileToken): bool
    {
        $profiles = $this->decodeProfilesFromToken($profileToken);

        // Get auth for connecting to the merchant gateway
        $merchantAuth = $this->getMerchantAuth();

        // Delete the account
        $refId = 'ref' . time();
        $request = new AnetApi\DeleteCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setCustomerProfileId($profiles['customer_profile_id']);
        $request->setRefId($refId);

        $controller = new AnetController\DeleteCustomerProfileController($request);
        $response = $controller->executeWithApiResponse($this->gatewayUrl);

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return true;
        }

        // Failed
        $errorMessages = $response->getMessages()->getMessage();
        $this->lastErrorMessage = "Failed to delete profile: " .
            $errorMessages[0]->getCode() . "  " .
            $errorMessages[0]->getText();
        return false;
    }
    /**
     * Charge a payment to a remotely-stored profile
     *
     * @param PaymentProfileEntity $paymentProfile
     * @param float $amount Amount to charge the customer
     * @return ChargeResponse
     */
    public function chargeProfile(PaymentProfileEntity $paymentProfile, float $amount): ChargeResponse
    {
        // Get profile IDs from the token
        $profiles = $this->decodeProfilesFromToken($paymentProfile->getValue('token'));

        // Get auth for connecting to the merchant gateway
        $merchantAuth = $this->getMerchantAuth();

        // Set the transaction's refId
        $refId = 'ref' . time();
        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($profiles['customer_profile_id']);
        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($profiles['payment_profile_id']);
        $profileToCharge->setPaymentProfile($paymentProfile);
        $transRequestType = new AnetAPI\TransactionRequestType();
        $transRequestType->setTransactionType("authCaptureTransaction");
        $transRequestType->setAmount($amount);
        $transRequestType->setProfile($profileToCharge);
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setRefId($refId);
        $request->setTransactionRequest($transRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        $apiResponse = $controller->executeWithApiResponse($this->gatewayUrl);

        // Total failure
        if ($apiResponse === null) {
            throw new \RuntimeException('The gateway did not respond');
        }

        // Get transaction response
        $tresponse = $apiResponse->getTransactionResponse();

        // Check for a non-ok response
        if ($apiResponse->getMessages()->getResultCode() != self::RESPONSE_OK) {
            $chargeResponse = new ChargeResponse(ChargeResponse::STATUS_DECLINED);
            $chargeResponse->addMessage(
                new ResponseMessage(
                    $apiResponse->getMessages()->getMessage()[0]->getCode(),
                    $apiResponse->getMessages()->getMessage()[0]->getText()
                )
            );

            if ($tresponse && $tresponse->getErrors() !== null) {
                $this->lastErrorMessage = $tresponse->getErrors()[0]->getErrorCode() . ': ' . $tresponse->getErrors()[0]->getErrorText();
            }

            return $chargeResponse;
        }

        // This should not happen, but check to see if there is no transaction details
        if (!$tresponse === null) {
            throw new \RuntimeException('The gateway did not respond');
        }

        // Get transaction details
        $chargeResponse = new ChargeResponse(ChargeResponse::STATUS_APPROVED);
        $chargeResponse->setTransactionId($tresponse->getTransId());
        $chargeResponse->setAuthorizationCode($tresponse->getAuthCode());
        // TODO: we might want to do something with an auth code or response code
        // $tresponse->getResponseCode()

        // Add messages to the repsonse
        $messages = $tresponse->getMessages();
        foreach ($messages as $tmessage) {
            $chargeResponse->addMessage(
                new ResponseMessage(
                    $tmessage->getCode(),
                    $tmessage->getDescription()
                )
            );
        }
        return $chargeResponse;
    }

    /**
     * Charge a credit or debit card directly
     *
     * @param ContactEntity $customer
     * @param CreditCard $card
     * @param float $amount
     * @return ChargeResponse
     */
    public function chargeCard(ContactEntity $customer, CreditCard $card, float $amount): ChargeResponse
    {
        // Get auth for connecting to the merchant gateway
        $merchantAuth = $this->getMerchantAuth();

        // Set the transaction's refId
        $refId = 'ref' . time();

        // Create the payment data for a credit card
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($card->getCardNumber());
        $creditCard->setExpirationDate($card->getExpiration("YYYY-MM"));
        $creditCard->setCardCode($card->getCardCode());

        // Add the payment data to a paymentType object
        $paymentOne = new AnetAPI\PaymentType();
        $paymentOne->setCreditCard($creditCard);

        // Set the customer's Bill To address
        $customerAddress = $this->getBillingAddressFromCustomer($customer);

        // Set the customer's identifying information
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($customer->getEntityId());
        $customerData->setEmail($customer->getValue('email'));

        // Add values for transaction settings
        $duplicateWindowConf = new AnetAPI\SettingType();
        $duplicateWindowConf->setSettingName("duplicateWindow");
        $duplicateWindowConf->setSettingValue("60");

        // Create a TransactionRequestType object and add the previous objects to it
        $transRequestType = new AnetAPI\TransactionRequestType();
        $transRequestType->setTransactionType("authCaptureTransaction");
        $transRequestType->setAmount($amount);
        $transRequestType->setPayment($paymentOne);
        $transRequestType->setBillTo($customerAddress);
        $transRequestType->addToTransactionSettings($duplicateWindowConf);

        // Assemble the complete transaction request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setRefId($refId);
        $request->setTransactionRequest($transRequestType);

        // Create the controller and get the response
        $controller = new AnetController\CreateTransactionController($request);
        $apiResponse = $controller->executeWithApiResponse($this->gatewayUrl);

        // Total failure
        if ($apiResponse === null) {
            throw new \RuntimeException('The gateway did not respond');
        }

        // Get transaction response
        $tresponse = $apiResponse->getTransactionResponse();

        // Check for a non-ok response
        if ($apiResponse->getMessages()->getResultCode() != self::RESPONSE_OK) {
            $chargeResponse = new ChargeResponse(ChargeResponse::STATUS_DECLINED);
            $chargeResponse->addMessage(
                new ResponseMessage(
                    $apiResponse->getMessages()->getMessage()[0]->getCode(),
                    $apiResponse->getMessages()->getMessage()[0]->getText()
                )
            );

            if ($tresponse && $tresponse->getErrors() !== null) {
                $this->lastErrorMessage = $tresponse->getErrors()[0]->getErrorCode() . ': ' . $tresponse->getErrors()[0]->getErrorText();
            }

            return $chargeResponse;
        }

        // This should not happen, but check to see if there is no transaction details
        if (!$tresponse === null) {
            throw new \RuntimeException('The gateway did not respond');
        }

        // Get transaction details
        $chargeResponse = new ChargeResponse(ChargeResponse::STATUS_APPROVED);
        $chargeResponse->setTransactionId($tresponse->getTransId());
        $chargeResponse->setAuthorizationCode($tresponse->getAuthCode());
        // TODO: we might want to do something with an auth code or response code
        // $tresponse->getResponseCode()

        // Add messages to the repsonse
        $messages = $tresponse->getMessages();
        foreach ($messages as $tmessage) {
            $chargeResponse->addMessage(
                new ResponseMessage(
                    $tmessage->getCode(),
                    $tmessage->getDescription()
                )
            );
        }
        return $chargeResponse;
    }

    /**
     * Get an existing profile from the gateway
     *
     * @param ContactEntity $customer
     * @return void
     */
    private function getExistingRemoteProfile(ContactEntity $customer)
    {
        // Get auth for connecting to the merchant gateway
        $merchantAuth = $this->getMerchantAuth();

        $request = new AnetAPI\GetCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setMerchantCustomerId(
            $this->getUniqueIdFromCustomer($customer)
        );
        $request->setEmail($customer->getValue('email'));
        $controller = new AnetController\GetCustomerProfileController($request);
        $response = $controller->executeWithApiResponse($this->gatewayUrl);
        echo "Reponse for " . $this->getUniqueIdFromCustomer($customer) . " : " . var_export($response, true);
        if (($response != null) && ($response->getMessages()->getResultCode() == self::RESPONSE_OK)) {
            //echo "GetCustomerProfile SUCCESS : " .  "\n";
            $profileSelected = $response->getProfile();
            //$paymentProfilesSelected = $profileSelected->getPaymentProfiles();
            //echo "Profile Has " . count($paymentProfilesSelected). " Payment Profiles" . "\n";
            return $profileSelected->getCustomerProfileId();
        }

        return '';

        // Get all existing customer profile ID's
        // $request = new AnetAPI\GetCustomerProfileIdsRequest();
        // $request->setMerchantAuthentication($merchantAuth);
        // $controller = new AnetController\GetCustomerProfileIdsController($request);
        // $response = $controller->executeWithApiResponse($this->gatewayUrl);
        // if (($response != null) && ($response->getMessages()->getResultCode() == self::RESPONSE_OK)) {
        //     echo "GetCustomerProfileId's SUCCESS: " . "\n";
        //     $profileIds[] = $response->getIds();
        //     echo "There are " . count($profileIds[0]) . " Customer Profile ID's for this Merchant Name and Transaction Key" . "\n";
        // } else {
        //     echo "GetCustomerProfileId's ERROR :  Invalid response\n";
        //     $errorMessages = $response->getMessages()->getMessage();
        //     echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
        // }
    }

    /**
     * Get existing payment profiles for a user
     *
     * @return array
     */
    // private function getExistingRemotePaymentProfiles(ContactEntity $customer): array
    // {
    //     // Get auth for connecting to the merchant gateway
    //     $merchantAuth = $this->getMerchantAuth();

    //     $request = new AnetAPI\GetCustomerProfileRequest();
    //     $request->setMerchantAuthentication($merchantAuth);
    //     $request->setMerchantCustomerId(
    //         $this->getUniqueIdFromCustomer($customer)
    //     );
    //     $controller = new AnetController\GetCustomerProfileController($request);
    //     $response = $controller->executeWithApiResponse($this->gatewayUrl);
    //     if (($response != null) && ($response->getMessages()->getResultCode() == self::RESPONSE_OK)) {
    //         $profileSelected = $response->getProfile();
    //         return $profileSelected->getPaymentProfiles();
    //     }

    //     return [];
    // }

    /**
     * Get merchant auth with the correct login and key
     *
     * @return ANetApi\MerchantAuthenticationType
     */
    private function getMerchantAuth()
    {
        $merchantAuth = new AnetAPI\MerchantAuthenticationType();
        $merchantAuth->setName($this->authLoginId);
        $merchantAuth->setTransactionKey($this->authTransKey);
        return $merchantAuth;
    }

    /**
     * Create a billing address object for AuthDotNet from a customer entity
     *
     * @param ContactEntity $customer
     * @return AnetAPI\CustomerAddressType
     */
    private function getBillingAddressFromCustomer(ContactEntity $customer): AnetAPI\CustomerAddressType
    {
        $billTo = new AnetAPI\CustomerAddressType();

        if ($customer->getValue('billing_first_name')) {
            $billTo->setFirstName($customer->getValue('billing_first_name'));
        } elseif ($customer->getValue('first_name')) {
            $billTo->setFirstName($customer->getValue('first_name'));
        }

        if ($customer->getValue('billing_last_name')) {
            $billTo->setLastName($customer->getValue('billing_last_name'));
        } elseif ($customer->getValue('last_name')) {
            $billTo->setLastName($customer->getValue('last_name'));
        }

        if ($customer->getValue('company')) {
            $billTo->setCompany($customer->getValue('company'));
        }

        if ($customer->getValue('billing_street')) {
            $billTo->setAddress($customer->getValue('billing_street'));
        }

        if ($customer->getValue('billing_city')) {
            $billTo->setCity($customer->getValue('billing_city'));
        }

        if ($customer->getValue('billing_district')) {
            $billTo->setState($customer->getValue('billing_district'));
        }

        if ($customer->getValue('billing_postal_code')) {
            $billTo->setZip($customer->getValue('billing_postal_code'));
        };
        $billTo->setCountry("USA");
        // $billTo->setPhoneNumber("888-888-8888");
        // $billTo->setfaxNumber("999-999-9999");
        return $billTo;
    }

    /**
     * Encode cusotmer profile id and payment profile id into a token string
     *
     * @param string $customerProfileId
     * @param string $paymentProfileId
     * @return string
     */
    private function encodeProfilesIntoToken(string $customerProfileId, string $paymentProfileId): string
    {
        $tokenData = [
            'customer_profile_id' => $customerProfileId,
            'payment_profile_id' => $paymentProfileId,
        ];
        return json_encode($tokenData);
    }


    /**
     * Decodes a token to get get customer profile id and payment profile id
     *
     * @param string $token
     * @return array Associative array with customer_profile_id and payment_profile_id
     */
    private function decodeProfilesFromToken(string $token): array
    {
        $profiles = json_decode($token, true);
        // Make sure we have profiles above and throw an exception if not
        if (empty($profiles) || empty($profiles['customer_profile_id']) || empty($profiles['payment_profile_id'])) {
            throw new \RuntimeException(
                "The profile token passed in is invalid: " . $token
            );
        }
        return $profiles;
    }

    /**
     * We have to convert our 36 character uuid into a 20 char unique id
     *
     * @param ContactEntity $customerEntity
     * @return string
     */
    private function getUniqueIdFromCustomer(ContactEntity $customerEntity): string
    {
        $uuid = $customerEntity->getEntityId();
        return substr(md5($uuid), 0, 20);
    }
}
