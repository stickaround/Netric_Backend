<?php 

declare(strict_types=1);

namespace Netric\Curl;

/*
* Tool that use to transfer data to and from a server.
* By specifying the location (in the form of a URL) and the data you want to send.
*/
class HttpCaller
{
    /*
    * Store cURL handle for current session
    */
    private $curl = null;

    /*
    * Store current session error flag
    */
    private $error = false;

    /*
    * Store currect session last error number
    */
    private $errorCode = 0;
   
    /*
    * Store error for the current session.
    */
    private $errorMessage = null;
    
    /*
    * Store website address for session
    */
    private $url = null;
    
    /*
    * Store current session Response
    */
    private $response = null;

    /**
     * Initialize the cUrl session
     *
     * @throws \ErrorException
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new \ErrorException('cURL library is not loaded');
        }
        $this->curl = curl_init();
        $this->initialize();
    }

    /**
     * close the current session of cURL
     *
     * @access public
     */
    public function close()
    {
        if (is_resource($this->curl) || $this->curl instanceof \CurlHandle) {
            curl_close($this->curl);
        }
        $this->curl = null;
    }

    /**
     * After a cURL session has been created and all of the session's options have been set, this function should be call. 
     *
     * @access private
     *
     * @return mixed Returns the value provided by parseResponse.
     */
    private function exec()
    {
        $rawResponse = curl_exec($this->curl);
        $curlErrorCode = curl_errno($this->curl);
        $curlErrorMessage = curl_error($this->curl);
        
        $curlError = $curlErrorCode !== 0;

        // Ensure Curl::rawResponse is a string as curl_exec() can return false.
        // Without this, calling strlen($curl->rawResponse) will error when the
        if (!is_string($rawResponse)) {
            $rawResponse = '';
        }

        // Include additional error code information in error message when possible.
        if ($curlError) {
            $curlErrorMessage =
                curl_strerror($curlErrorCode) . (empty($curlErrorMessage) ? '' : ': ' . $curlErrorMessage
                );
        }

        // Get http status code from current session
        $httpStatusCode = $this->getInfo(CURLINFO_HTTP_CODE);

        // If http status code return 4xx (Client Error Codes) or 5xx (Server Error Codes) its means error else if return 2xx (Successful Codes) then success
        $httpError = in_array((int) floor($httpStatusCode / 100), [4, 5], true);

        // Error Flag set true if either current session last error return or http status code return 4xx or 5xx
        $this->error = $curlError || $httpError;

        // If error found, then set error number else zero
        $this->errorCode = $this->error ? ($curlError ? $curlErrorCode : $httpStatusCode) : 0;

        // set responses
        $this->response = $rawResponse;

        // If error found then set error Message
        $httpErrorMessage = '';
        if ($this->error) {
            if (isset($this->responseHeaders['Status-Line'])) {
                $httpErrorMessage = $this->responseHeaders['Status-Line'];
            }
        }
        $this->errorMessage = $curlError ? $curlErrorMessage : $httpErrorMessage;

        // Reset nobody setting possibly set from a HEAD request.
        $this->setOpt(CURLOPT_NOBODY, false);

        return $this->response;
    }

    /**
     * GET request for current session.
     *
     * @access public
     * @param  $url
     *
     * @return mixed Returns the value provided by exec.
     */
    public function get($url)
    {
        // set url
        $this->setUrl($url);

        // Set cUrl options
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->setOpt(CURLOPT_HTTPGET, true);
        return $this->exec();
    }

    /**
     * Get information regarding current session
     *
     * @access public
     * @param  $opt
     *
     * @return mixed
     */
    public function getInfo($opt = null)
    {
        $args = [];
        $args[] = $this->curl;

        if (func_num_args()) {
            $args[] = $opt;
        }

        return call_user_func_array('curl_getinfo', $args);
    }

    /**
     * Post request for current curl session 
     *
     * @access public
     * @param  $url
     * @param  $data
     * @return mixed Returns the value provided by exec.
     *
     */
    public function post($url, $data = '')
    {
        $this->setUrl($url);
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, $data);
        return $this->exec();
    }

    /**
     * Set an option for a cURL current session
     *
     * @access private
     * @param  $option
     * @param  $value
     *
     * @return boolean
     */
    private function setOpt($option, $value)
    {
        $required_options = [
            CURLOPT_RETURNTRANSFER => 'CURLOPT_RETURNTRANSFER',
        ];

        if (in_array($option, array_keys($required_options), true) && $value !== true) {
            trigger_error($required_options[$option] . ' is a required option', E_USER_WARNING);
        }

        $success = curl_setopt($this->curl, $option, $value);
        if ($success) {
            $this->options[$option] = $value;
        }
        return $success;
    }

    /**
     * Set Url for current session
     *
     * @access private
     * @param  $url
     * @param  $mixed_data
     */
    private function setUrl($url, $mixed_data = '')
    {
        $this->url = $url;
        $this->setOpt(CURLOPT_URL, $this->url);
    }

    /**
     * Reset all options of a curl session handle
     *
     * @access private
     */
    private function reset()
    {
        if (is_resource($this->curl) || $this->curl instanceof \CurlHandle) {
            curl_reset($this->curl);
        } else {
            $this->curl = curl_init();
        }

        $this->initialize();
    }

    /**
     * 
     * Get Error of current session
     */
    public function getError(){
        return $this->error;
    }

    /**
     * Get Error number of current session
     */
    public function getErrorCode(){
        return $this->errorCode;
    }

    /**
     * Get Error message of current session
    */
    public function getErrorMessage(){
        return $this->errorMessage;
    }

    /**
     * Initialize current session
     *
     * @access private
     * @param  $base_url
     */
    private function initialize($base_url = null)
    {
        $this->setOpt(CURLINFO_HEADER_OUT, true);

        $this->setOpt(CURLOPT_RETURNTRANSFER, true);

        if ($base_url !== null) {
            $this->setUrl($base_url);
        }
    }
}