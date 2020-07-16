<?php
namespace Netric\Controller;

use Netric\Mvc\AbstractController;
use Netric\Application\Response\HttpResponse;
use Netric\Application\Response\ConsoleResponse;
use Netric\Application\Health\HealthCheckFactory;
use Netric\Application\Health\HealthCheck;
use Netric\Permissions\Dacl;
use Netric\Entity\ObjType\UserEntity;

/**
 * Perform various healthchecks
 */
class HealthController extends AbstractController
{
    /**
     * Override to allow anonymous users to access this controller for authentication
     *
     * @return \Netric\Permissions\Dacl
     */
    public function getAccessControlList()
    {
        // By default allow anonymous access to this controller
        // Since only health/ping is accessible via http
        $dacl = new Dacl();
        $dacl->allowGroup(UserEntity::GROUP_EVERYONE);
        return $dacl;
    }

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

        if ($this->testMode) {
            $response->suppressOutput(true);
        }


        // Get ServiceManager for the application
        $serviceLocator = $this->account->getServiceManager();

        // Get the HealthCheck service
        $healthCheck = $serviceLocator->get(HealthCheckFactory::class);

        if (!$healthCheck->isSystemHealthy()) {
            $response->setReturnCode(ConsoleResponse::STATUS_CODE_FAIL);
            $response->writeLine('FAIL: The system is unhealthy');
            $response->writeLine(var_export($healthCheck->getReportedErrors(), true));
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

        if ($this->testMode) {
            $response->suppressOutput(true);
        }

        // Get ServiceManager for the application
        $serviceLocator = $this->application->getServiceManager();

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
