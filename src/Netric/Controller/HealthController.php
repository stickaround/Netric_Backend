<?php

namespace Netric\Controller;

use Netric\Mvc;
use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Application\Response\ConsoleResponse;
use Netric\Application\Health\HealthCheckInterface;
use Netric\Log\LogInterface;

/**
 * Perform various healthchecks
 */
class HealthController extends AbstractFactoriedController implements ControllerInterface
{
    /**
     * Service that will check the health of the system
     */
    private HealthCheckInterface $healthCheck;

    /**
     * Logger for recording what is going on
     */
    private LogInterface $log;

    /**
     * Initialize controller and all dependencies
     *
     * @param HealthCheckInterface $healthCheck Service that will check the health of the system
     * @param LogInterface $log Logger for recording what is going on
     */
    public function __construct(
        HealthCheckInterface $healthCheck,
        LogInterface $log
    ) {
        $this->healthCheck = $healthCheck;
        $this->log = $log;
    }

    /**
     * For public ping of the server
     * 
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getPingAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);
        $response->setReturnCode(HttpResponse::STATUS_CODE_OK);
        return $response;
    }

    /**
     * For console requests
     * 
     * @return ConsoleResponse
     */
    public function consoleTestAction(): ConsoleResponse
    {
        $response = new ConsoleResponse($this->log);

        if ($this->testMode) {
            $response->suppressOutput(true);
        }

        if (!$this->healthCheck->isSystemHealthy()) {
            $response->setReturnCode(ConsoleResponse::STATUS_CODE_FAIL);
            $response->writeLine('FAIL: The system is unhealthy');
            $response->writeLine(var_export($this->healthCheck->getReportedErrors(), true));
            return $response;
        }

        $response->setReturnCode(ConsoleResponse::STATUS_CODE_OK);
        $response->writeLine('SUCCESS: The system is ok');
        return $response;
    }

    /**
     * Check to see if dependencies are online
     * 
     * @return ConsoleResponse
     */
    public function consoleTestDependenciesAction(): ConsoleResponse
    {
        $response = new ConsoleResponse($this->log);

        if ($this->testMode) {
            $response->suppressOutput(true);
        }

        if (!$this->healthCheck->areDependenciesLive()) {
            $response->setReturnCode(ConsoleResponse::STATUS_CODE_FAIL);
            $response->writeLine('FAIL: Not all dependencies are available');
            $response->writeLine(var_export($this->healthCheck->getReportedErrors(), true));
            return $response;
        }

        $response->setReturnCode(ConsoleResponse::STATUS_CODE_OK);
        $response->writeLine('SUCCESS: Critical dependencies are live');
        return $response;
    }
}
