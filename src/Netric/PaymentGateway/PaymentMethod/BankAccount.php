<?php
namespace Netric\PaymentGateway\PaymentMethod;

/**
 * Bank account payment method
 */
class BankAccount
{
    /**
     * Bank account types
     */
    const TYPE_CHECKING = 'checking';
    const TYPE_SAVINGS = 'savings';

    /**
     * Account type
     *
     * @var string
     */
    private $accountType = self::TYPE_CHECKING;

    /**
     * Bank routing number
     *
     * @var string
     */
    private $routingNumber = '';

    /**
     * Bank account number
     *
     * @var string
     */
    private $accountNumber = '';

    /**
     * The name (person or company) on the bank account
     *
     * @var string
     */
    private $nameOnAccount = '';

    /**
     * The name of the bank
     *
     * @var string
     */
    private $nameOfBank = '';

    /**
     * Set the type of bank account this is
     *
     * @param string $accountType
     * @return void
     */
    public function setAccountType(string $accountType)
    {
        $this->accountType = $accountType;
    }

    /**
     * Get the type of bank account
     *
     * @return string
     */
    public function getAccountType() : string
    {
        return $this->accountType;
    }

    /**
     * Set the bank routing number
     *
     * @param string $routingNumber
     * @return void
     */
    public function setRoutingNumber(string $routingNumber)
    {
        $this->routingNumber = $routingNumber;
    }

    /**
     * Get the bank routing number
     *
     * @return string
     */
    public function getRoutingNumber() : string
    {
        return $this->routingNumber;
    }

    /**
     * Set bank account number
     *
     * @param string $accountNumber
     * @return void
     */
    public function setAccountNumber(string $accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * Get bank account number
     *
     * @return string
     */
    public function getAccountNumber() : string
    {
        return $this->accountNumber;
    }

    /**
     * Set name on the bank account
     *
     * @param string $name
     * @return void
     */
    public function setNameOnAccount(string $name)
    {
        $this->nameOnAccount = $name;
    }

    /**
     * Get the name on the bank account
     *
     * @return string
     */
    public function getNameOnAccount() : string
    {
        return $this->nameOnAccount;
    }

    /**
     * Set the name of the bank where this acccount is
     *
     * @param string $bankName
     * @return void
     */
    public function setBankName(string $bankName)
    {
        $this->nameOfBank = $bankName;
    }

    /**
     * Get the name of the bank where this account is
     *
     * @return string
     */
    public function getBankName() : string
    {
        return $this->nameOfBank;
    }
}