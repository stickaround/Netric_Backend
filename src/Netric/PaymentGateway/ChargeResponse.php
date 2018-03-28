<?php
namespace Netric\PaymentGateway;

/**
 * Response returned from a payment gateway when a charge is processed
 */
class ChargeResponse
{
    /**
     * Normalize response status codes
     */
    const STATUS_APPROVED = 1;
    const STATUS_DECLINED = 3;
    const STATUS_ERROR = 5;
    const STATUS_PENDING = 7;

    /**
     * Gateways return a unique ID to reference the charge transaction
     *
     * @var string
     */
    private $transactionId = "";

    /**
     * The status of the charge
     *
     * @var int self::STATUS_
     */
    private $status = self::STATUS_APPROVED;

    /**
     * Messages received from the gateway
     *
     * @var array ResponseMessage[]
     */
    private $messages = [];

    /**
     * Set the  gateway generated unique id of the charge transaction
     *
     * @param string $transactionId
     */
    public function setTransactionId(string $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * Get the gateway generated unique ID of the charge transaction
     *
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * Add message
     *
     * @param ResponseMessage $message
     */
    public function addMessage(ResponseMessage $message)
    {
        $this->messages[] = $message;
    }

    /**
     * Get any messages received from the gateway
     *
     * @return ResponseMessage[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Set the status of the charge
     *
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    /**
     * Get the status of the stranction
     *
     * @return int One of ChargeResponse::STATUS_* constants
     */
    public function getStatus(): int
    {
        return $this->status;
    }

}