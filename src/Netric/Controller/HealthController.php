<?php
namespace Netric\Controller;

use Netric\Mvc\AbstractController;
use Netric\Application\Response\HttpResponse;
use Netric\Application\Response\ConsoleResponse;
use Netric\Application\Health\HealthCheckFactory;
use Netric\Application\Health\HealthCheck;

/**
 * Perform various healthchecks
 */
class HealthController extends AbstractController
{
    /**
     * For public ping of the server
     */
    public function getPingAction()
    {
        $request = $this->getRequest();
        $response = new HttpResponse($request);
        $response->setReturnCode(HttpResponse::STATUS_CODE_OK);
        return $response;
    }

    /**
     * For console requests
     */
    public function consoleTestAction()
    {
        $response = new ConsoleResponse($this->application->getLog());

        // First check to see if an account exists (requires setup to be run)
        if (!$this->application->getAccount()) {
            $response->setReturnCode(ConsoleResponse::STATUS_CODE_FAIL);
            $response->writeLine('FAIL: Setup has not been run');
            return $response;
        }

        // Get ServiceManager for the application
        $serviceLocator = $this->application->getAccount()->getServiceManager();

        // Get the HealthCheck service
        $healthcheck = $serviceLocator->get(HealthCheckFactory::class);

        if (!$healthcheck->isSystemHealthy()) {
            $response->setReturnCode(ConsoleResponse::STATUS_CODE_FAIL);
            $response->writeLine('FAIL: The system is unhealthy');
            $response->writeLine(var_export($healthcheck->getReportedErrors(), true));
            return $response;
        }

        $response->setReturnCode(ConsoleResponse::STATUS_CODE_OK);
        $response->writeLine('SUCCESS: The system is ok');
        return $response;
    }

    /**
     * Check to see if dependencies are online
     */
    public function consoleTestDependenciesAction()
    {
        $response = new ConsoleResponse($this->application->getLog());

        // First check to see if an account exists (requires setup to be run)
        if (!$this->application->getAccount()) {
            $response->setReturnCode(ConsoleResponse::STATUS_CODE_FAIL);
            $response->writeLine('FAIL: Setup has not been run');
            return $response;
        }

        // Get ServiceManager for the application
        $serviceLocator = $this->application->getAccount()->getServiceManager();

        // Get the HealthCheck service
        $healthcheck = $serviceLocator->get(HealthCheckFactory::class);

        if (!$healthcheck->areDependenciesLive()) {
            $response->setReturnCode(ConsoleResponse::STATUS_CODE_FAIL);
            $response->writeLine('FAIL: Not all dependencies are available');
            $response->writeLine(var_export($healthcheck->getReportedErrors(), true));
            return $response;
        }

        $response->setReturnCode(ConsoleResponse::STATUS_CODE_OK);
        $response->writeLine('SUCCESS: Critical dependencies are live');
        return $response;
    }
}
