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
     * Application health checker
     *
     * @var HealthCheck
     */
    private $healthCheck = null;

    /**
     * Override initialization
     */
    protected function init()
    {
        // Get ServiceManager for the account
        $serviceLocator = $this->account->getServiceManager();

        // Get the HealthCheck service
        $this->healthCheck = $serviceLocator->get(HealthCheckFactory::class);
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
        $response = new ConsoleResponse($this->account->getApplication()->getLog());

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
}
