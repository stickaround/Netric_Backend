<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */
namespace Netric\Application\Response;

use Netric\Request\HttpRequest;
use Netric\Request\RequestInterface;

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
    private $contentType = self::TYPE_TEXT_HTML;
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
     * @var string
     */
    private $outputBuffer = "";

    /**
     * If we are streaming output then the stream will be set in this variable
     *
     * @var null
     */
    private $outputStream = null;

    /**
     * Constructor
     *
     * @param RequestInterface $request Original request so we can tailor the response
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
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
        if (!$this->headersSent) {
            $this->sendHeaders();
        }

        if ($this->supressOutput) {
            $this->outputBuffer = $content;
        } else {
            echo $content;
        }
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
     * Stream the contents to an output stream or stdout with echo
     *
     * @param resource|null $inputStream Optional stream to output to
     */
    public function stream($inputStream = null)
    {
        if ($this->supressOutput) {
            $this->outputBuffer = stream_get_contents($this->outputStream);
        } else if ($inputStream) {
            fwrite($inputStream, stream_get_contents($this->outputStream));
        } else {
            echo stream_get_contents($this->outputStream);
        }
    }

    /**
     * Print the contents of this response to stdout
     */
    public function printOutput()
    {
        // TODO: Check for byte range header stuff here if type=stream

        $this->printHeaders();

        if ($this->outputStream) {
            $this->stream();
        } else {
            echo $this->outputBuffer;
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
            header($name.': '.$value, false);
        }

        // Send status
        header(
            sprintf(
                'HTTP/%s %s %s', '1.0',
                $this->responseCode,
                $this->responseReason
            ),
            true,
            $this->responseCode
        );

        // TODO: Cookies

        $this->headersSent = true;
    }
}
