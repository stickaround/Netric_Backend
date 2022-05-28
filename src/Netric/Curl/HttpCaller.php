<?php declare(strict_types=1);

namespace Netric\Curl;

class HttpCaller
{
    public $curl = null;
    /*
    * Error variable
    */
    public $error = false;
    public $errorCode = 0;
    public $errorMessage = null;

    public $url = null;
    // Curl Response Variable
    public $response = null;

    /**
     * Construct
     *
     * @access public
     * @param  $base_url
     * @throws \ErrorException
     */
    public function __construct($base_url = null)
    {
        if (!extension_loaded('curl')) {
            throw new \ErrorException('cURL library is not loaded');
        }
        $this->curl = curl_init();
        $this->initialize($base_url);
    }

    /**
     * Close
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
     * Exec
     *
     * @access public
     * @param  $ch
     *
     * @return mixed Returns the value provided by parseResponse.
    */
    public function exec($ch = null)
    {
        if ($ch === null) {
            $rawResponse = curl_exec($this->curl);
            $curlErrorCode = curl_errno($this->curl);
            $curlErrorMessage = curl_error($this->curl);
        } 
        
        $curlError = $curlErrorCode !== 0;

        // Ensure Curl::rawResponse is a string as curl_exec() can return false.
        // Without this, calling strlen($curl->rawResponse) will error when the
        // strict types setting is enabled.
        if (!is_string($rawResponse)) {
            $rawResponse = '';
        }

        // Include additional error code information in error message when possible.
        if ($curlError) {
            $curlErrorMessage =
                curl_strerror($curlErrorCode) . (empty($curlErrorMessage) ? '' : ': ' . $curlErrorMessage
                );
        }

        $httpStatusCode = $this->getInfo(CURLINFO_HTTP_CODE);
        $httpError = in_array((int) floor($httpStatusCode / 100), [4, 5], true);
        $this->error = $curlError || $httpError;
        $this->errorCode = $this->error ? ($curlError ? $curlErrorCode : $httpStatusCode) : 0;

        $this->response = $rawResponse;

        $httpErrorMessage = '';
        if ($this->error) {
            if (isset($this->responseHeaders['Status-Line'])) {
                $httpErrorMessage = $this->responseHeaders['Status-Line'];
            }
        }
        $this->errorMessage = $curlError ? $curlErrorMessage : $httpErrorMessage;

        // Reset select deferred properties so that they may be recalculated.
        unset($this->effectiveUrl);
        unset($this->totalTime);

        // Reset nobody setting possibly set from a HEAD request.
        $this->setOpt(CURLOPT_NOBODY, false);

        return $this->response;
    }

    /**
     * Get
     *
     * @access public
     * @param  $url
     * @param  $data
     *
     * @return mixed Returns the value provided by exec.
     */
    public function get($url)
    {
        if (is_array($url)) {
            $data = $url;
            $url = (string)$this->url;
        }
        $this->setUrl($url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
        $this->setOpt(CURLOPT_HTTPGET, true);
        return $this->exec();
    }

    /**
     * Get Info
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
     * Post
     *
     * @access public
     * @param  $url
     * @param  $data
     * @return mixed Returns the value provided by exec.
     *
    */
    public function post($url, $data = '')
    {
        if (is_array($url)) {
            $data = $url;
            $url = (string)$this->url;
        }

        $this->setUrl($url);
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, $data);
        return $this->exec();
    }

    /**
     * Set Opt
     *
     * @access public
     * @param  $option
     * @param  $value
     *
     * @return boolean
    */
    public function setOpt($option, $value)
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
     * Set Url
     *
     * @access public
     * @param  $url
     * @param  $mixed_data
    */
    public function setUrl($url, $mixed_data = '')
    {
        $this->url = $url;
        $this->setOpt(CURLOPT_URL, $this->url);
    }

    /**
     * Reset
     *
     * @access public
    */
    public function reset()
    {
        if (is_resource($this->curl) || $this->curl instanceof \CurlHandle) {
            curl_reset($this->curl);
        } else {
            $this->curl = curl_init();
        }

        $this->initialize();
    }

    /**
     * Initialize
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