<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Mail\Header;

class Cc extends AbstractAddressList
{
    protected $fieldName = 'Cc';
    protected static $type = 'cc';
}
