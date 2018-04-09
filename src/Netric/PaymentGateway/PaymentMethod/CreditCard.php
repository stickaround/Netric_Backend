<?php
namespace Netric\PaymentGateway\PaymentMethod;

class CreditCard
{
    /**
     * The card number
     *
     * @var string
     */
    private $cardNumber = "";

    /**
     * The month this card expires
     *
     * @var int
     */
    private $expirationMonth = null;

    /**
     * The year this card expires
     *
     * @var int
     */
    private $expirationYear = null;


    /**
     * Credit card code
     * 
     * @var string
     */
    private $cardCode = "";

    /**
     * Set the credit card number
     */
    public function setCardNumber($number)
    {
        $this->cardNumber = $number;
    }

    /**
     * Get the actual credit card number
     *
     * @return string
     */
    public function getCardNumber() : string
    {
        return $this->cardNumber;
    }

    /**
     * Set the expiration date
     *
     * @param string $year
     * @param string $month
     * @return void
     */
    public function setExpiration(int $year, int $month)
    {
        $this->expirationYear = $year;
        $this->expirationMonth = $month;
    }

    /**
     * Get a formatted expiration date
     *
     * @return string
     */
    public function getExpiration($format = 'YYYY-MM') : string
    {
        $merged = str_replace('YYYY', $this->expirationYear, $format);
        $merged = str_replace('MM', $this->expirationMonth, $merged);
        return $merged;
    }

    /**
     * Set the card verification code
     *
     * @param string $code
     * @return void
     */
    public function setCardCode(string $code)
    {
        $this->cardCode = $code;
    }

    /**
     * Get the verification code
     *
     * @return string
     */
    public function getCardCode() : string
    {
        return $this->cardCode;
    }
}