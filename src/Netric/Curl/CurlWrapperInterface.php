<?php

namespace Netric\Curl;
/**
 * Interface for PHP cURL functions.
 **/
interface CurlWrapperInterface
{
    public function curl_init($url = null);
    public function curl_exec($ch);
    public function curl_errno($ch);
    public function curl_error($ch);
    public function curl_close($ch);
    public function curl_getinfo($ch, $opt = 0);
    public function curl_setopt_array($ch, $options);
    public function curl_setopt($ch, $option, $value);
    public function curl_version($age = CURLVERSION_NOW);
}