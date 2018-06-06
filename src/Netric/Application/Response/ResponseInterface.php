<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Application\Response;

/**
 * Defines an interface for responses from controllers
 */
interface ResponseInterface
{
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
    public function setContentType($contentType);

    /**
     * Set a header for the response
     *
     * This may or may not be supported by the specific response, like
     * a console response will just ignore the header completely.
     *
     * @param string $header The name of the header to set
     * @param string|int $value The value to set the header to
     */
    public function setHeader($header, $value);

    /**
     * Set a return code to the caller
     *
     * @param int $code
     * @param string $message Optional message to go with the code
     */
    public function setReturnCode($code, $message = "");

    /**
     * Get the return/response/status code of this request
     *
     * @return int
     */
    public function getReturnCode();

    /**
     * Send output in the response
     *
     * @param mixed $content The content to output
     */
    public function write($content);

    /**
     * Indicate if we should buffer output or print it immediate when $this->write is called
     *
     * @param bool $flag
     */
    public function suppressOutput($flag);

    /**
     * Get the output contents
     *
     * @return string
     */
    public function getOutputBuffer();

    /**
     * Print the contents of this response to stdout
     */
    public function printOutput();

    /**
     * Set a stream for a response
     *
     * @param resource $stream
     */
    //public function setStream($stream);
}
