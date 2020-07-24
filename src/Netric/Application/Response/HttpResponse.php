<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */
namespace Netric\Application\Response;

use Netric\Request\HttpRequest;
use Netric\Request\RequestInterface;
use DateTime;

/**
 * Defines an interface for responses from controllers
 */
class HttpResponse implements ResponseInterface
{
    /**
     * Set the mime content type of this response
     *
     * @var string
     */
    private $contentType = self::TYPE_JSON;
    const TYPE_TEXT_PLAIN = 'text/plain';
    const TYPE_TEXT_HTML = 'text/html';
    const TYPE_IMAGE_GIF = 'image/gif';
    const TYPE_IMAGE_JPEG = 'image/jpeg';
    const TYPE_IMAGE_PNG = 'image/png';
    const TYPE_IMAGE_BMP = 'image/bmp';
    const TYPE_JSON = 'application/json';
    const TYPE_BINARY = 'application/octet-stream';

    /**
     * Response codes to return to the caller
     *
     * @var int
     */
    private $responseCode = self::STATUS_CODE_OK;
    const STATUS_CODE_OK = 200;
    const STATUS_CODE_OK_PARTIAL = 206;
    const STATUS_CODE_NOT_MODIFIED = 301;
    const STATUS_CODE_TEMPORARY_REDIRECT = 307;
    const STATUS_CODE_BAD_REQUEST = 400;
    const STATUS_CODE_FORBIDDEN = 403;
    const STATUS_CODE_NOT_FOUND = 404;

    /**
     * Message that goes with the status code
     *
     * @var string
     */
    private $responseReason = "";

    /**
     * @var array Recommended Reason Phrases
     */
    protected $recommendedReasonPhrases = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /**
     * The client's request so we can tailor the response
     *
     * @var HttpRequest|null
     */
    private $request = null;

    /**
     * Headers to send to the client
     *
     * @var array
     */
    private $headers = [];

    /**
     * We can only send headers before the body, this flag keeps treack of when they are sent
     *
     * @var bool
     */
    private $headersSent = false;

    /**
     * If set to true, we will buffer all output written until sendBody is called
     *
     * @var bool
     */
    private $supressOutput = false;

    /**
     * If we are buffering output then it will be stored in this variable
     *
     * @var string|array
     */
    private $outputBuffer = null;

    /**
     * If we are streaming output then the stream will be set in this variable
     *
     * @var null
     */
    private $outputStream = null;

    /**
     * Flag to indicate if we should allow this response to be cached
     *
     * @var bool
     */
    private $isCacheable = false;

    /**
     * DateTime last modified
     *
     * @var DateTime null
     */
    private $lastModified = null;

    /**
     * Constructor
     *
     * @param RequestInterface $request Original request so we can tailor the response
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;

        // Check to see if we should suppress/buffer the output
        if ($request->getParam('buffer_output') === 1) {
            $this->suppressOutput(true);
        }
    }

    /**
     * Set the content type of this response
     *
     * If not set the response object will try to detect the content-type
     * from the returned value.
     *
     * @param $contentType
     * @return mixed
     * @throws Exception\ContentTypeNotSupportedException If invalid type used for this response
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        $this->setHeader('Content-Type', $contentType);
    }

    /**
     * Set the whole length of the body in bytes
     *
     * @param int $lengthInBytes
     */
    public function setContentLength($lengthInBytes)
    {
        $this->setHeader('Content-Length', $lengthInBytes);
    }

    /**
     * Get the content length
     *
     * @return int
     */
    public function getContentLength()
    {
        return (isset($this->headers['Content-Length'])) ? $this->headers['Content-Length'] : 0;
    }

    /**
     * Set the disposition and filename of this response
     *
     * @param string $disposition Can be 'inline' or 'attachment'
     * @param string $fileName The name of the file being sent
     */
    public function setContentDisposition($disposition, $fileName)
    {
        $safeFileName = str_replace("'", '', $fileName);
        $this->setHeader('Content-Disposition', "$disposition; filename=\"$safeFileName\"");
    }

    /**
     * Set a header for the response
     *
     * This may or may not be supported by the specific response, like
     * a console response will just ignore the header completely.
     *
     * @param string $header The name of the header to set
     * @param string|int $value The value to set the header to
     */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;
    }

    /**
     * Get all headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set a return code to the caller
     *
     * @param int $code
     * @param string $message Optional message to go with the code
     */
    public function setReturnCode($code, $message = "")
    {
        $this->responseCode = $code;

        // Get the default reason message if not set
        if (!$message && isset($this->recommendedReasonPhrases[$code])) {
            $message = $this->recommendedReasonPhrases[$code];
        }

        $this->responseReason = $message;
    }

    /**
     * Indicate if we should buffer output or print it immediate when $this->write is called
     *
     * @param bool $flag
     */
    public function suppressOutput($flag)
    {
        $this->supressOutput = $flag;
    }

    /**
     * Get the output contents
     *
     * @return string
     */
    public function getOutputBuffer()
    {
        return $this->outputBuffer;
    }

    /**
     * Get the return/response/status code of this request
     *
     * @return int
     */
    public function getReturnCode()
    {
        return $this->responseCode;
    }

    /**
     * Send output in the response
     *
     * @param mixed $content The content to output
     */
    public function write($content)
    {
        if ($this->supressOutput) {
            $this->outputBuffer = $content;
            return;
        }

        if (!$this->headersSent) {
            $this->printHeaders();
        }

        if (is_array($content)) {
            $content = json_encode($content);
        }

        echo $content;
    }

    /**
     * We can stream content to the requester
     *
     * @param resource $stream
     */
    public function setStream($stream)
    {
        $this->outputStream = $stream;
    }

    /**
     * Add headers that make make it possible to cache this response
     *
     * @param string $uniqueEtag Any unique tag that external proxies and caches can use to keep a copy
     */
    public function setCacheable($uniqueEtag)
    {
        $this->setHeader("Pragma", "public");
        $this->setHeader("Etag", $uniqueEtag);
        $this->isCacheable = true;
    }

    /**
     * Set the date and time this response was last modified
     *
     * @param DateTime $modified
     */
    public function setLastModified(DateTime $modified)
    {
        $this->lastModified = $modified;
        $this->setHeader(
            'Last-Modified',
            gmdate('D, d M Y H:i:s', $modified->getTimestamp() . ' GMT')
        );
    }

    /**
     * Stream the contents to an output stream or stdout with echo
     *
     * @param resource|null $inputStream Optional stream to output to
     */
    public function stream($inputStream = null)
    {
        // Set start and end points for the stream to default of -1 which will return the whole stream
        $begin = 0;
        $end = -1;

        /*
         * If this is a stream then handle multi-range
         * http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2k
         */
        if ($this->getContentLength()) {
            $contentLength = $this->getContentLength();

            if ($this->request->getParam('HTTP_RANGE')) {
                $httpRange = $this->request->getParam('HTTP_RANGE');
                $matches = null;
                if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $httpRange, $matches)) {
                    $begin = $matches[1];
                    $end = ($matches[2]) ? intval($matches[2]) : $contentLength;
                }

                // Set headers for the partial content
                if ($end) {
                    $this->setReturnCode(self::STATUS_CODE_OK_PARTIAL);
                    $this->setHeader('Content-Range', "bytes $begin-$end/$contentLength");
                    $this->setHeader('Content-Length', (($end - $begin) + 1));
                    $this->setHeader('Cache-Control', 'public, must-revalidate, max-age=0');
                    $this->setHeader('Pragma', 'no-cache');
                    $this->setHeader('Accept-Ranges', 'bytes');
                }
            }
            /*
            //header("Accept-Ranges: bytes"); // TODO: do we need this duplicate header?
            $this->setHeader('Accept-Range', '0-' . $contentLength . '/' . $contentLength);

            if ($this->request->getParam('HTTP_RANGE'))
            {
                // Extract the range string
                list(, $range) = explode('=', $this->request->getParam('HTTP_RANGE'), 2);

                // Make sure the client hasn't sent us a multibyte range
                if (strpos($range, ',') !== false) {

                    // (?) Shoud this be issued here, or should the first
                    // range be used? Or should the header be ignored and
                    // we output the whole content?
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $numBytes-$offset/$fileSize");
                    // (?) Echo some info to the client?
                    exit;
                }

                // If the range starts with an '-' we start from the beginning
                // If not, we forward the file pointer
                // And make sure to get the end byte if specified
                if ($range[0] == '-') {
                    // The n-number of the last bytes is requested
                    $offset = $contentLength - substr($range, 1);
                } else {
                    $range  = explode('-', $range);
                    $offset = $range[0];
                    $numBytes   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $fileSize - 1;
                }

                // Check the range and make sure it's treated according to the specs.
                // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
                // End bytes can not be larger than $end.
                $numBytes = (($numBytes + $offset) > $fileSize) ? $fileSize : $numBytes;

                // Validate the requested range and return an error if it's not correct.
                if ($offset > ($numBytes) || $c_start > $fileSize - 1 || $c_end >= $fullLength) {

                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $start-$end/$fullLength");
                    // (?) Echo some info to the client?
                    exit;
                }

                $start  = $c_start;
                $end    = $c_end;
                fseek($fp, $start);

                // Notify the client the byte range we'll be outputting
                header('HTTP/1.1 206 Partial Content');
                header("Content-Range: bytes $start-$end/$fullLength");
                header("Content-Length: " . (($end - $start) + 1));

            }
             */
        }

        $this->printHeaders();

        if ($inputStream) {
            fwrite($inputStream, stream_get_contents($this->outputStream, $end, $begin));
        } elseif ($this->supressOutput) {
            $this->outputBuffer = stream_get_contents($this->outputStream, $end, $begin);
        } else {
            echo stream_get_contents($this->outputStream, $end, $begin);
        }
    }

    /**
     * Print the contents of this response to stdout
     */
    public function printOutput()
    {
        // If this is cacheable and not modified, return without sending any data
        if ($this->isCacheable && $this->lastModified) {
            // Check if the file has been modified since the last time it was downloaded
            // And we are not trying to stream a segment of a file with HTTP_RANGE
            if ($this->request->getParam('HTTP_IF_MODIFIED_SINCE') &&
                !$this->request->getParam('HTTP_RANGE')) {
                $if_modified_since = strtotime(preg_replace('/;.*$/', '', $this->request->getParam('HTTP_IF_MODIFIED_SINCE')));
                if ($if_modified_since >= $this->lastModified->getTimestamp()) {
                    $this->setReturnCode(self::STATUS_CODE_NOT_MODIFIED, 'Not Modified');
                    $this->printHeaders();
                    return;
                }
            }
        }

        if ($this->outputStream) {
            $this->stream();
        } else {
            // Send headers before the body
            $this->printHeaders();

            if ($this->contentType === self::TYPE_JSON && is_array($this->outputBuffer)) {
                $this->printBodyJson();
            } elseif ($this->outputBuffer) {
                // Print plain text
                echo $this->outputBuffer;
            }
        }
    }

    /**
     * Output headers to the browser
     */
    private function printHeaders()
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return;
        }

        // Send headers
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, false);
        }

        // Send status
        header(
            sprintf(
                'HTTP/%s %s %s',
                '1.0',
                $this->responseCode,
                $this->responseReason
            ),
            true,
            $this->responseCode
        );

        // TODO: Cookies

        $this->headersSent = true;
    }

    /**
     * Print out the body as a json document
     */
    private function printBodyJson()
    {
        if (!is_array($this->outputBuffer)) {
            throw new \RuntimeException("JSON responses require an array");
        }

        $bodyContent = json_encode($this->outputBuffer);

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $bodyContent = json_encode(["error" => "Maximum stack depth exceeded"]);
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $bodyContent = json_encode(["error" => "Underflow or the modes mismatch"]);
                break;
            case JSON_ERROR_CTRL_CHAR:
                $bodyContent = json_encode(["error" => "Unexpected control character found"]);
                break;
            case JSON_ERROR_SYNTAX:
                $bodyContent = json_encode(["error" => "Syntax error, malformed JSON"]);
                break;
            case JSON_ERROR_UTF8:
                // Try to fix encoding
                foreach ($this->outputBuffer as $vname => $vval) {
                    if (is_string($vval)) {
                        $this->outputBuffer[$vname] = utf8_encode($vval);
                    }
                }
                $bodyContent = json_encode($this->outputBuffer);
                break;
            case JSON_ERROR_NONE:
            default:
                // All is good continue below
                break;
        }


        // Print the body
        echo $bodyContent;
    }
}
