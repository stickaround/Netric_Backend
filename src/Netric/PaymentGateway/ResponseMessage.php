<?php
namespace Netric\PaymentGateway;

class ResponseMessage
{
    private $code = "";
    private $text = "";

    /**
     * ResponseMessage constructor.
     *
     * @param string $code
     * @param string $messageText
     */
    public function __construct(string $code, string $messageText)
    {
        $this->code = $code;
        $this->text = $messageText;
    }

    /**
     * Get the gateway-defined response code for this message
     *
     * @return string
     */
    public function getCode() : string
    {
        return $this->code;
    }

    /**
     * Get the full message text from the gateway
     *
     * @return string
     */
    public function getText() : string
    {
        return $this->text;
    }
}