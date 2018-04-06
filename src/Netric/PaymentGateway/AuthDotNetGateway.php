<?php
namespace Netric\PaymentGateway;

use Netric\Entity\ObjType\CustomerEntity;
use Netric\Entity\ObjType\PaymentProfileEntity;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use Netric\PaymentGateway\PaymentMethod\BankAccount;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;


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
        // Create a Customer Profile Request
        //  1. (Optionally) create a Payment Profile
        //  2. (Optionally) create a Shipping Profile
        //  3. Create a Customer Profile (or specify an existing profile)
        //  4. Submit a CreateCustomerProfile Request
        //  5. Validate Profile ID returned
        // Set credit card information for payment profile
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($card->getCardNumber());
        $creditCard->setExpirationDate($card->getExpiration("YYYY-MM"));
        $creditCard->setCardCode($card->getCardCode());
        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        // Create the Bill To info for new payment type
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

        // Create a customer shipping address
        $customerShippingAddress = new AnetAPI\CustomerAddressType();
        $customerShippingAddress->setFirstName("James");
        $customerShippingAddress->setLastName("White");
        $customerShippingAddress->setCompany("Addresses R Us");
        $customerShippingAddress->setAddress(rand() . " North Spring Street");
        $customerShippingAddress->setCity("Toms River");
        $customerShippingAddress->setState("NJ");
        $customerShippingAddress->setZip("08753");
        $customerShippingAddress->setCountry("USA");
        $customerShippingAddress->setPhoneNumber("888-888-8888");
        $customerShippingAddress->setFaxNumber("999-999-9999");
        // Create an array of any shipping addresses
        $shippingProfiles[] = $customerShippingAddress;

        // Create a new CustomerPaymentProfile object
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType('individual');
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($paymentCreditCard);
        $paymentProfile->setDefaultpaymentProfile(true);
        $paymentProfiles[] = $paymentProfile;

        // Create a new CustomerProfileType and add the payment profile object
        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setDescription("Customer 2 Test PHP");
        $customerProfile->setMerchantCustomerId("M_" . time());
        $customerProfile->setEmail($customer->getValue('email'));
        $customerProfile->setpaymentProfiles($paymentProfiles);
        $customerProfile->setShipToList($shippingProfiles);

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
            //$paymentProfiles = $response->getCustomerPaymentProfileIdList();
            return $response->getCustomerProfileId();
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
        $bankAccount = new AnetAPI\BankAccountType();
        $bankAccount->setAccountType('checking');
        // see eCheck documentation for proper echeck type to use for each situation
        $bankAccount->setEcheckType('WEB');
        $bankAccount->setRoutingNumber('125000105');
        $bankAccount->setAccountNumber('1234567890');
        $bankAccount->setNameOnAccount('John Doe');
        $bankAccount->setBankName('Wells Fargo Bank NA');
        $paymentBankAccount = new AnetAPI\PaymentType();
        $paymentBankAccount->setBankAccount($bankAccount);

        // Create the Bill To info for new payment type
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

        // Create a customer shipping address
        $customerShippingAddress = new AnetAPI\CustomerAddressType();
        $customerShippingAddress->setFirstName("James");
        $customerShippingAddress->setLastName("White");
        $customerShippingAddress->setCompany("Addresses R Us");
        $customerShippingAddress->setAddress(rand() . " North Spring Street");
        $customerShippingAddress->setCity("Toms River");
        $customerShippingAddress->setState("NJ");
        $customerShippingAddress->setZip("08753");
        $customerShippingAddress->setCountry("USA");
        $customerShippingAddress->setPhoneNumber("888-888-8888");
        $customerShippingAddress->setFaxNumber("999-999-9999");
        // Create an array of any shipping addresses
        $shippingProfiles[] = $customerShippingAddress;

        // Create a new CustomerPaymentProfile object
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType('individual');
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($paymentBankAccount);
        $paymentProfile->setDefaultpaymentProfile(true);
        $paymentProfiles[] = $paymentProfile;

        // Create a new CustomerProfileType and add the payment profile object
        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setDescription("Customer 2 Test PHP");
        $customerProfile->setMerchantCustomerId("M_" . time());
        $customerProfile->setEmail($customer->getValue('email'));
        $customerProfile->setpaymentProfiles($paymentProfiles);
        $customerProfile->setShipToList($shippingProfiles);

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
            //$paymentProfiles = $response->getCustomerPaymentProfileIdList();
            return $response->getCustomerProfileId();
        }

        // The response was not a success or it would have returned above
        $errorMessages = $response->getMessages()->getMessage();
        $this->lastErrorMessage = $errorMessages[0]->getCode() . "  " . $errorMessages[0]->getText();
        return ''; // empty on failure
    }

    /**
     * Charge a payment profile
     *
     * @param PaymentProfileEntity $paymentProfile
     * @param float $amount Amount to charge the customer
     * @return ChargeResponse
     */
    public function chargeProfile(PaymentProfileEntity $paymentProfile, float $amount) : ChargeResponse
    {
        // TODO: Still working on making this good
        
        // Get auth for connecting to the merchant gateway
        $merchantAuth = $this->getMerchantAuth();
        
        // Set the transaction's refId
        $refId = 'ref' . time();
        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($profileid);
        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($paymentprofileid);
        $profileToCharge->setPaymentProfile($paymentProfile);
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setProfile($profileToCharge);
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        if ($response != null) {
            if ($response->getMessages()->getResultCode() == \SampleCode\Constants::RESPONSE_OK) {
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
                    echo " Transaction Response code : " . $tresponse->getResponseCode() . "\n";
                    echo "Charge Customer Profile APPROVED  :" . "\n";
                    echo " Charge Customer Profile AUTH CODE : " . $tresponse->getAuthCode() . "\n";
                    echo " Charge Customer Profile TRANS ID  : " . $tresponse->getTransId() . "\n";
                    echo " Code : " . $tresponse->getMessages()[0]->getCode() . "\n";
                    echo " Description : " . $tresponse->getMessages()[0]->getDescription() . "\n";
                } else {
                    echo "Transaction Failed \n";
                    if ($tresponse->getErrors() != null) {
                        echo " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                        echo " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                    }
                }
            } else {
                echo "Transaction Failed \n";
                $tresponse = $response->getTransactionResponse();
                if ($tresponse != null && $tresponse->getErrors() != null) {
                    echo " Error code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                    echo " Error message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                } else {
                    echo " Error code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
                    echo " Error message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
                }
            }
        } else {
            echo "No response returned \n";
        }
        return $response;
    }

    /**
     * Charge a credit or debit card directly
     *
     * @param CreditCard $card
     * @param float $amount
     * @return ChargeResponse
     */
    public function chargeCard(CreditCard $card, float $amount) : ChargeResponse
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
        // Create order information
        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber("10101");
        $order->setDescription("Golf Shirts");
        // Set the customer's Bill To address
        $customerAddress = new AnetAPI\CustomerAddressType();
        $customerAddress->setFirstName("Ellen");
        $customerAddress->setLastName("Johnson");
        $customerAddress->setCompany("Souveniropolis");
        $customerAddress->setAddress("14 Main Street");
        $customerAddress->setCity("Pecan Springs");
        $customerAddress->setState("TX");
        $customerAddress->setZip("44628");
        $customerAddress->setCountry("USA");
        // Set the customer's identifying information
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId("99999456654");
        $customerData->setEmail("EllenJohnson@example.com");
        // Add values for transaction settings
        $duplicateWindowSetting = new AnetAPI\SettingType();
        $duplicateWindowSetting->setSettingName("duplicateWindow");
        $duplicateWindowSetting->setSettingValue("60");
        // Add some merchant defined fields. These fields won't be stored with the transaction,
        // but will be echoed back in the response.
        $merchantDefinedField1 = new AnetAPI\UserFieldType();
        $merchantDefinedField1->setName("customerLoyaltyNum");
        $merchantDefinedField1->setValue("1128836273");
        $merchantDefinedField2 = new AnetAPI\UserFieldType();
        $merchantDefinedField2->setName("favoriteColor");
        $merchantDefinedField2->setValue("blue");
        // Create a TransactionRequestType object and add the previous objects to it
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setPayment($paymentOne);
        $transactionRequestType->setBillTo($customerAddress);
        $transactionRequestType->setCustomer($customerData);
        $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
        $transactionRequestType->addToUserFields($merchantDefinedField1);
        $transactionRequestType->addToUserFields($merchantDefinedField2);
        // Assemble the complete transaction request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        // Create the controller and get the response
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse($this->gatewayUrl);

        // Set the charge respose object to return
        $chargeResponse = new ChargeResponse();

        // Assume failure
        $chargeResponse->setStatus(ChargeResponse::STATUS_ERROR);
        
        // Using the below in the unit tests to figure out what is going on
        if ($response != null) {
           // Check to see if the API request was successfully received and acted upon
            if ($response->getMessages()->getResultCode() == self::RESPONSE_OK) {
               // Since the API request was successful, look for a transaction response
               // and parse it to display the results of authorizing the card
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
                    $chargeResponse->setStatus(ChargeResponse::STATUS_APPROVED);
                    $chargeResponse->setTransactionId($tresponse->getTransId());

                    // TODO: we might want to do something with an auth code or response code
                    // $tresponse->getAuthCode()
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
                } else {
                    echo "Transaction Failed \n";
                    if ($tresponse->getErrors() != null) {
                        echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                        echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                    }
                }
               // Or, print errors if the API request wasn't successful
            } else {
                echo "Transaction Failed With " . $response->getMessages()->getResultCode() . " \n";
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getErrors() != null) {
                    echo " Error Code  : " . $tresponse->getErrors()[0]->getErrorCode() . "\n";
                    echo " Error Message : " . $tresponse->getErrors()[0]->getErrorText() . "\n";
                } else {
                    echo " Error Code  : " . $response->getMessages()->getMessage()[0]->getCode() . "\n";
                    echo " Error Message : " . $response->getMessages()->getMessage()[0]->getText() . "\n";
                }
            }
        } else {
            echo "No response returned \n";
        }

        return $chargeResponse;
    }

    /**
     * Get merchant auth with the correct login and key
     *
     * @return void
     */
    private function getMerchantAuth()
    {
        $merchantAuth = new AnetAPI\MerchantAuthenticationType();
        $merchantAuth->setName($this->authLoginId);
        $merchantAuth->setTransactionKey($this->authTransKey);
        return $merchantAuth;
    }
}