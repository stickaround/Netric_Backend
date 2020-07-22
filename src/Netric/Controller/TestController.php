<?php

/**
 * This is just a simple test controller
 */

namespace Netric\Controller;

use Netric\Mvc\ControllerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Application\Response\ConsoleResponse;
use Netric\Request\HttpRequest;
use Netric\Request\ConsoleRequest;
use Netric\Mvc\AbstractFactoriedController;

/**
 * Class TestController just to test MVC functions and routes
 *
 * @package Netric\Controller
 */
class TestController extends AbstractFactoriedController implements ControllerInterface
{
    /**
     * For public tests of a GET request returning text
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getTestAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);
        // Buffer the output since it is small and this function is used in tests
        $response->suppressOutput(true);
        $response->write(['param' => 'test']);
        return $response;
    }

    /**
     * For public tests of a POST method returning JSON
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postTestAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);
        // Buffer the output since it is small and this function is used in tests
        $response->suppressOutput(true);
        // Simply echo out the json object sent in
        $response->write(json_decode($request->getBody()));
        return $response;
    }

    /**
     * For console requests
     *
     * @param ConsoleRequest $request Request object for this run
     * @return ConsoleResponse
     */
    public function consoleTestAction(ConsoleRequest $request): ConsoleResponse
    {
        $response = new ConsoleResponse();
        // Buffer the output since it is small and this function is used in tests
        $response->suppressOutput(true);
        $response->write("test");
        return $response;
    }
}
