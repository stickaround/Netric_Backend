<?php
namespace Netric\PaymentGateway;

use Netric\Entity\ObjType\CustomerEntity;
use Netric\Entity\ObjType\PaymentProfileEntity;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use Netric\PaymentGateway\PaymentMethod\BankAccount;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;


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
    function __construct(string $loginId, string $transactionKey, string $gatewayUrl = '')
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
    public function getLastError() : string
    {
        return $this->lastErrorMessage;
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
        // Get auth for connecting to the merchant gateway
        $merchantAuth = $this->getMerchantAuth();
        
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
        $paymentProfiles[] = $paymentProfile;

        // Create a new CustomerProfileType and add the payment profile object
        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setDescription($customer->getName());
        $customerProfile->setMerchantCustomerId($customer->getId());
        $customerProfile->setEmail($customer->getValue('email'));
        $customerProfile->setpaymentProfiles($paymentProfiles);

        // Assemble the complete transaction request
        $request = new AnetAPI\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setRefId($refId);
        $request->setProfile($customerProfile);

        // Create the controller and get the response
        $controller = new AnetController\CreateCustomerProfileController($request);
        $response = $controller->executeWithApiResponse($this->gatewayUrl);

        if (($response != null) && ($response->getMessages()->getResultCode() == self::RESPONSE_OK)) {
            // Get the most recently added payment profile
            $paymentProfiles = $response->getCustomerPaymentProfileIdList();
            return $this->encodeProfilesIntoToken(
                $response->getCustomerProfileId(),
                $paymentProfiles[0]
            );
        }
    
        // The response was not a success or it would have returned above
        $errorMessages = $response->getMessages()->getMessage();
        $this->lastErrorMessage = $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText();
        return ''; // empty on failure
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
        // Get auth for connecting to the merchant gateway
        $merchantAuth = $this->getMerchantAuth();
                
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

        // Create a new CustomerProfileType and add the payment profile object
        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setDescription($customer->getName());
        if ($customer->getId()) {
            $customerProfile->setMerchantCustomerId($customer->getId());
        }
        $customerProfile->setEmail($customer->getValue('email'));
        $customerProfile->setpaymentProfiles($paymentProfiles);

        // Assemble the complete transaction request
        $request = new AnetAPI\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setRefId($refId);
        $request->setProfile($customerProfile);

        // Create the controller and get the response
        $controller = new AnetController\CreateCustomerProfileController($request);
        $response = $controller->executeWithApiResponse($this->gatewayUrl);

        if (($response != null) && ($response->getMessages()->getResultCode() == self::RESPONSE_OK)) {
            // We could try to get all payment profiles here
            $paymentProfiles = $response->getCustomerPaymentProfileIdList();
            return $this->encodeProfilesIntoToken(
                $response->getCustomerProfileId(),
                $paymentProfiles[0]
            );
        }

        // The response was not a success or it would have returned above
        $errorMessages = $response->getMessages()->getMessage();
        $this->lastErrorMessage = $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText();
        return ''; // empty on failure
    }

    /**
     * Charge a payment to a remotely-stored profile
     *
     * @param PaymentProfileEntity $paymentProfile
     * @param float $amount Amount to charge the customer
     * @return ChargeResponse
     */
    public function chargeProfile(PaymentProfileEntity $paymentProfile, float $amount) : ChargeResponse
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
                $this->lastErrorMessage = getErrors()[0]->getErrorCode() . ': ' . $tresponse->getErrors()[0]->getErrorText();
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
     * @param CustomerEntity $customer
     * @param CreditCard $card
     * @param float $amount
     * @return ChargeResponse
     */
    public function chargeCard(CustomerEntity $customer, CreditCard $card, float $amount) : ChargeResponse
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
        $customerData->setId($customer->getId());
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
     * @param CustomerEntity $customer
     * @return AnetAPI\CustomerAddressType
     */
    private function getBillingAddressFromCustomer(CustomerEntity $customer) : AnetAPI\CustomerAddressType
    {
        $billTo = new AnetAPI\CustomerAddressType();
        if ($customer->getValue('first_name')) {
            $billTo->setFirstName($customer->getValue('first_name'));
        }

        if ($customer->getValue('last_name')) {
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

        if ($customer->getValue('billing_state')) {
            $billTo->setState($customer->getValue('billing_state'));
        }

        if ($customer->getValue('billing_zip')) {
            $billTo->setZip($customer->getValue('billing_zip'));
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
    private function encodeProfilesIntoToken(string $customerProfileId, string $paymentProfileId) : string
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
    private function decodeProfilesFromToken(string $token) : array
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
}