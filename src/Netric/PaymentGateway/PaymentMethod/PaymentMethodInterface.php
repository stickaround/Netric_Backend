<?php
namespace Netric\PaymentGateway\PaymentMethod;

interface PaymentMethodInterface
{
    /**
     * Get an associative array of all payment fields
     *
     * @return array
     */
    public function getFields() : array;

}