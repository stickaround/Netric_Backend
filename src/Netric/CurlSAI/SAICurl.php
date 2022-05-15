<?php

namespace Netric\CurlSAI;
/**
 * Implements the cURL interface by simply delegating calls to the built-in cURL functions..
 * See http://www.php.net/manual/en/book.curl.php
 **/
class SAICurl implements SAICurlInterface
{
    /*
    * Close a cURL session
    */
    public function curl_close($ch)
    {
        curl_close($ch);
    }

    /*
    * Copy a cURL handle along with all of its preferences
    */
    public function curl_copy_handle($ch)
    {
        return curl_copy_handle($ch);
    }

    /*
    * Return the last error number
    */
    public function curl_errno($ch)
    {
        return curl_errno($ch);
    }

    /*
    * Return a string containing the last error for the current session
    */
    public function curl_error($ch)
    {
        return curl_error($ch);
    }

    /*
    * Perform a cURL session
    */
    public function curl_exec($ch)
    {
        return curl_exec($ch);
    }

    /*
    * Get information regarding a specific transfer
    */
    public function curl_getinfo($ch, $opt = 0)
    {
        return curl_getinfo($ch, $opt);
    }

    /*
    * Initialize a cURL session
    */
    public function curl_init($url = null)
    {
        return curl_init($url);
    }

    /*
    * Close a set of cURL handles
    */
    public function curl_multi_add_handle($mh, $ch)
    {
        return curl_multi_add_handle($mh, $ch);
    }

    /*
    * Close a set of cURL handles
    */
    public function curl_multi_close($mh)
    {
        curl_multi_close($mh);
    }

    /*
    * Run the sub-connections of the current cURL handle
    */
    public function curl_multi_exec($mh, &$still_running)
    {
        return curl_multi_exec($mh, $still_running);
    }

    /*
    * Return the content of a cURL handle if CURLOPT_RETURNTRANSFER is set
    */
    public function curl_multi_getcontent($ch)
    {
        return curl_multi_getcontent($ch);
    }

    /*
    * read multi stack informationals
    */
    public function curl_multi_info_read($mh, &$msgs_in_queue = null)
    {
        return curl_multi_info_read($mh, $msgs_in_queue);
    }

    /*
    * Returns a new cURL multi handle
    */
    public function curl_multi_init()
    {
        return curl_multi_init();
    }

    /*
    * remove an easy handle from a multi session
    */
    public function curl_multi_remove_handle($mh, $ch)
    {
        return curl_multi_remove_handle($mh, $ch);
    }

    /*
    * Wait for activity on any curl_multi connection
    */
    public function curl_multi_select($mh, $timeout = 1.0)
    {
        return curl_multi_select($mh, $timeout);
    }

    /*
    * Set multiple options for a cURL transfer
    */
    public function curl_setopt_array($ch, $options)
    {
        return curl_setopt_array($ch, $options);
    }

    /*
    * Set an option for a cURL transfer
    */
    public function curl_setopt($ch, $option, $value)
    {
        return curl_setopt($ch, $option, $value);
    }

    /*
    * Gets cURL version information
    */
    public function curl_version($age = CURLVERSION_NOW)
    {
        return curl_version($age);
    }
}
