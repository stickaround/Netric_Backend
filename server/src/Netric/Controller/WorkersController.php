<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Controller;

use Netric\Mvc;
use Netric\Application\Response\ConsoleResponse;
use Netric\Permissions\Dacl;
use Netric\Entity\ObjType\UserEntity;

/**
 * Controller used for interacting with workers from the command line (or API)
 */
class WorkersController extends Mvc\AbstractController
{
    /**
     * Since the only methods in this class are console then we allow for anonymous
     *
     * @return Dacl
     */
    public function getAccessControlList()
    {
        $dacl = new Dacl();
        $dacl->allowGroup(UserEntity::GROUP_EVERYONE);
        return $dacl;
    }

    /**
     * Install netric by initializing the application db and default account
     *
     * Options:
     *  --deamon = 1 If set then we will not print any output
     *  --runtime = [seconds] The number of seconds to run before returning
     */
    public function consoleProcessAction()
    {
        $response = new ConsoleResponse();
        $request = $this->getRequest();
        $application = $this->getApplication();

        /*
         * Check if we are suppressing output of the response.
         * This is most often used in unit tests.
         */
        if ($request->getParam("suppressoutput")) {
            $response->suppressOutput(true);
        }

        // Get application level service locator
        $serviceManager = $application->getServiceManager();

        // Get the worker service
        $workerService = $serviceManager->get("Netric/WorkerMan/WorkerService");

        // Process the jobs for an hour
        $timeStart = time();
        if ($request->getParam("runtime") && is_numeric($request->getParam("runtime"))) {
            $timeExit = time() + (int) $request->getParam("runtime");
        } else {
            $timeExit = time() + (60 * 60); // 1 hour
        }
        $numProcessed = 0;

        // Process each job, one at a time
        while ($workerService->processJobQueue()) {

            // Increment the number of jobs processed
            $numProcessed++;

            // We break once per hour to restart the script (PHP was not meant to run forever)
            if (($timeStart + time()) >= $timeExit) {
                break;
            }

            // Check to see if the request has been sent a stop signal
            if ($request->isStopping()) {
                $response->writeLine("Exiting job processor");
                break;
            }

            // Be nice to the CPU
            sleep(1);
        }

        if (!$request->getParam("daemon")) {
            $response->writeLine("Processed $numProcessed jobs");
        } else {
            $application->getLog()->info("Processed $numProcessed jobs");
        }

        return $response;
    }

    /**
     * Action for scheduling workers
     */
    public function consoleScheduleAction()
    {
        $application = $this->getApplication();
        $config = $application->getConfig();
        $response = new ConsoleResponse();
        $request = $this->getRequest();

        // Set a lock name to assure we only have one instance of the scheduler running (per version)
        $uniqueLockName = 'WorkerScheduleAction-' . $config->version;

        // We only ever want one scheduler running so create a lock that expires in 2 minutes
        if (!$application->acquireLock($uniqueLockName, 120)) {
            $response->writeLine("Exiting because another instance is running");
            return $response;
        }

        $running = true;
        while ($running) {
            // TODO: handle looping and scheduling actions

            // Renew the lock to make sure we do not expire since it times out in 2 minutes
            $application->extendLock($uniqueLockName);

            // Exit if we have received a stop signal
            if ($request->isStopping()) {
                // Immediate break the main while loop
                $running = false;
            } else {
                // Sleep for a minute before checking for the next scheduled job
                sleep(60);
            }
        }

        // Make sure we release the lock so that the scheduler can always be run
        $application->releaseLock($uniqueLockName);
        $response->writeLine("Exiting job scheduler");
        return $response;
    }
}
