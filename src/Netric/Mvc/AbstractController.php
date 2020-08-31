<?php

/**
 * Main abstract class for controllers in netric
 *
 * netric uses a custom controller class to expose actions to ajax requests. This base class is essentially used
 * to define how basic controllers should function.
 *
 * @copyright Copyright (c) 2003-2014 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\Mvc;

use Netric\Account\Account;
use Netric\Application\Application;
use Netric\Permissions\Dacl;
use Netric\Entity\ObjType\UserEntity;
use Netric\Request\RequestFactory;
use Netric\Request\RequestInterface;
use Netric\Application\Response\HttpResponse;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityGroupings\GroupingLoaderFactory;

/**
 * Main abstract class for controllers in netric
 */
abstract class AbstractController
{
    /**
     * Application instance for the request
     *
     * @var Application
     */
    protected $application = null;

    /**
     * Reference to current netric account
     *
     * @var Account
     */
    public $account = null;

    /**
     * Get request interface
     *
     * @var RequestInterface
     */
    protected $request = null;

    /**
     * If set to true then all 'echo' statements should be ignored for unit tests
     *
     * @var bool
     */
    public $testMode = false;

    /**
     * If we are running in debug or testing mode, this variable can be used to test output
     *
     * @var string
     */
    public $debugOutputBuf = "";

    /**
     * Output format will default to raw which allows the action to encode
     *
     * @var string
     */
    public $output = "json";

    /**
     * class constructor. All calls to a controller class require a reference to $ant and $user classes
     *
     * @param Application $application The current application instance
     * @param Account $account The tenant we are running under
     */
    function __construct(Application $application, Account $account = null)
    {
        $this->application = $application;
        $this->account = $account;
        $this->request = $application->getServiceManager()->get(RequestFactory::class);
        $this->init();
    }

    /**
     * Empty method to be optionally overridden by controller implementations
     */
    protected function init()
    {
    }

    /**
     * Get the request object
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get application instance this is working under
     *
     * @return \Netric\Application\Application
     */
    protected function getApplication()
    {
        return $this->application;
    }

    /**
     * Determine what users can access actions in the concrete controller
     *
     * This can easily be overridden in derrived controllers to allow custom access per controller
     * or each action can handle its own access controll list if desired.
     *
     * @return Dacl
     */
    public function getAccessControlList()
    {
        $dacl = new Dacl();

        // By default allow authenticated users to access a controller
        if ($this->account) {
            $groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
            $userGroups = $groupingLoader->get(ObjectTypes::USER . '/groups', $this->account->getAccountId());
            $usersGroup = $userGroups->getByName(UserEntity::GROUP_USERS);
            $dacl->allowGroup($usersGroup->getGroupId());
        }

        return $dacl;
    }

    /**
     * Print data to the browser. If debug, just cache data
     *
     * @param string $data The data to data to the browser or store in buffer
     * @return string
     */
    protected function sendOutput($data)
    {
        $data = $this->utf8Converter($data);

        switch ($this->output) {
            case 'xml':
                return $this->sendOutputXml($data);
                break;
            case 'json':
                return $this->sendOutputJson($data);
                break;
            case 'raw':
                return $this->sendOutputRaw($data);
                break;
        }

        return $data;
    }

    /**
     * Send raw output
     *
     * @param string $data
     * @return array
     */
    protected function sendOutputRaw($data)
    {
        if (!$this->testMode) {
            echo $data;
        }

        return $data;
    }

    /**
     * Print data to the browser. If debug, just cache data
     *
     * @param array $data The data to output to the browser or store in buffer
     * @return string JSON encoded string
     */
    protected function sendOutputJson($data)
    {
        if ($this->testMode) {
            $this->request->setParam("buffer_output", 1);
        }

        $response = new HttpResponse($this->request);
        $response->setContentType(HttpResponse::TYPE_JSON);
        $response->write($data);

        if ($this->testMode) {
            return $data;
        }

        return $response;
    }

    /**
     * Print data to the browser in xml format
     *
     * @param array $data The data to output to the browsr
     */
    protected function sendOutputXml($data)
    {
        $this->setContentType("xml");
        $enc = json_encode($data);

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= "<response>";
        if (is_array($data)) {
            $xml .= $this->makeXmlFromArray($data);
        } else {
            if ($data === true) {
                $data = "1";
            } elseif ($data === false) {
                $data = "0";
            }

            $xml .= $this->escapeXml($data);
        }
        $xml .= "</response>";

        if (!$this->testMode) {
            echo $xml;
        }

        return $xml;
    }

    /**
     * Set headers for this response so the data type is correct
     *
     * @param string $output The data to output to the browser or store in buffer
     */
    protected function setContentType($type = "html")
    {
        // If in debug mode then we are not sending any output to the browser
        if ($this->testMode) {
            return;
        }

        switch ($type) {
            case 'xml':
                header('Cache-Control: no-cache, must-revalidate');
                header("Content-type: text/xml");
                break;
            case 'json':
                header('Cache-Control: no-cache, must-revalidate');
                //header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Content-type: application/json');
                break;

            default:
                // Use the php defaults if no type or html is set
        }
    }

    /**
     * Recurrsively convert array to xml
     *
     * @param array $data The data to convert to xml
     * @return string
     */
    private function makeXmlFromArray($data)
    {
        if (!is_array($data)) {
            if ($data === true) {
                return "1";
            } elseif ($data === false) {
                return '0';
            }

            // Return the string
            return $this->escapeXml($data);
        }

        $ret = "";

        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $key = "item";
            }

            $ret .= "<" . $key . ">";
            if (is_array($val)) {
                $ret .= $this->makeXmlFromArray($val);
            } else {
                // Escape
                $val = $this->escapeXml($val);
                $ret .= $val;
            }

            $ret .= "</" . $key . ">";
        }

        return $ret;
    }

    /**
     * Escape XML
     *
     * @param string $string The string to escape for xml
     * @return string The escaped string
     */
    private function escapeXml($string)
    {
        return str_replace(
            ["&", "<", ">", "\"", "'"],
            ["&amp;", "&lt;", "&gt;", "&quot;", "&apos;"],
            $string
        );
    }

    /**
     * Recursively convert strings in array to UTF-8
     *
     * @param array $array
     * @return array
     */
    private function utf8Converter($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        array_walk_recursive($array, function (&$item, $key) {
            if (is_string($item) && !mb_detect_encoding($item, 'utf-8', true)) {
                $item = utf8_encode($item);
            }
        });

        return $array;
    }
}
