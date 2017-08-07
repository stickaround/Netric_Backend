<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Mail\Header;

interface MultipleHeadersInterface extends HeaderInterface
{
    public function toStringMultipleHeaders(array $headers);
}
