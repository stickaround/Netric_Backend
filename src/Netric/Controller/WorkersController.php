<?php

namespace Netric\Controller;

use Netric\Mvc;
use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Request\HttpRequest;
use Netric\Application\Response\ConsoleResponse;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Application;
use Netric\Authentication\AuthenticationService;
use Netric\Authentication\AuthenticationIdentity;
use Netric\Permissions\Dacl;
use Netric\WorkerMan\WorkerService;
use Netric\Request\RequestInterface;
use Netric\Log\LogInterface;
use Netric\WorkerMan\Worker\ScheduleRunnerWorker;
use Netric\Request\ConsoleRequest;

/**
 * Controller used for interacting with workers from the command line (or API)
 */
class WorkersController extends AbstractFactoriedController implements ControllerInterface
{
    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Service used to get the current user/account
     */
    private AuthenticationService $authService;

    /**
     * Worker for interacting with workers
     */
    private WorkerService $workerService;

    /**
     * Logger for recording what is going on
     */
    private LogInterface $log;

    /**
     * Initialize controller and all dependencies
     *
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param WorkerService $workerService Worker for interacting with workers
     * @param LogInterface $log Logger for recording what is going on
     * @param Application $application The current application instance
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        WorkerService $workerService,
        LogInterface $log,
        Application $application
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->workerService = $workerService;
        $this->log = $log;
        $this->application = $application;
    }

    /**
     * Optionally override the default worker service
     *
     * This will most likely be used in testing and automation
     *
     * @param WorkerService $workerService
     * @return void
     */
    public function setWorkerService(WorkerService $workerService)
    {
        $this->workerService = $workerService;
    }

    /**
     * Install netric by initializing the application db and default account
     *
     * Options:
     *  --deamon = 1 If set then we will not print any output
     *  --runtime = [seconds] The number of seconds to run before returning
     * 
     * @param ConsoleRequest | HttpRequest $request Request object for this run
     * @return ConsoleResponse
     */
    public function consoleProcessAction($request): ConsoleResponse
    {
        $response = new ConsoleResponse($this->log);

        /*
         * Check if we are suppressing output of the response.
         * This is most often used in unit tests.
         */
        if ($request->getParam("suppressoutput")) {
            $response->suppressOutput(true);
        }

        // Process the jobs for an hour
        $timeStart = time();
        if ($request->getParam("runtime") && is_numeric($request->getParam("runtime"))) {
            $timeExit = time() + (int) $request->getParam("runtime");
        } else {
            $timeExit = time() + (60 * 60); // 1 hour
        }
        $numProcessed = 0;

        // Process each job, one at a time
        while ($this->workerService->processJobQueue()) {
            // Increment the number of jobs processed
            $numProcessed++;

            // We break once per hour to restart the script (PHP was not meant to run forever)
            if (($timeStart + time()) >= $timeExit) {
                break;
            }

            // Check to see if the request has been sent a stop signal
            if ($request->isStopping()) {
                $response->writeLine("WorkersController->consoleProcessAction: Exiting job processor");
                break;
            }

            // Be nice to the CPU
            sleep(1);
        }

        $textToWrite = "WorkersController->consoleProcessAction: Processed $numProcessed jobs";
        if (!$request->getParam("daemon")) {
            $response->writeLine($textToWrite);
        } else {
            if ($numProcessed > 0) {
                $this->log->info($textToWrite);
            }
        }

        return $response;
    }

    /**
     * Action for scheduling workers
     * 
     * @param ConsoleRequest $request Request object for this run
     * @return ConsoleResponse
     */
    public function consoleScheduleAction(ConsoleRequest $request): ConsoleResponse
    {
        $config = $this->application->getConfig();
        $response = new ConsoleResponse($this->log);
        
        /*
         * Check if we are suppressing output of the response.
         * This is most often used in unit tests
         */
        if ($request->getParam("suppressoutput")) {
            $response->suppressOutput(true);
        }

        /*
         * Set the universal lock timeout which makes sure only one instance
         * of this is run within the specified number of seconds
         */
        $lockTimeout = ($request->getParam("locktimeout")) ? $request->getParam("locktimeout") : 120;

        // Set a lock name to assure we only have one instance of the scheduler running (per version)
        $uniqueLockName = 'WorkerScheduleAction-' . $config->version;

        // We only ever want one scheduler running so create a lock that expires in 2 minutes
        // if (!$this->application->acquireLock($uniqueLockName, $lockTimeout)) {
        //     $response->writeLine("WorkersController->consoleScheduleAction: Exiting because another instance is running");
        //     return $response;
        // }

        // Emit a background job for every account to run scheduled jobs every minute
        $this->queueScheduledJobs($request, $uniqueLockName, $lockTimeout);

        // // Make sure we release the lock so that the scheduler can always be run
        // $this->application->releaseLock($uniqueLockName);

        $exitMessage = "WorkersController->consoleScheduleAction: Exiting job scheduler";
        if (!$request->getParam("daemon")) {
            $response->writeLine($exitMessage);
        } else {
            $this->log->info($exitMessage);
        }

        return $response;
    }

    /**
     * The main scheduled jobs loop will schedule jobs to be run every minute
     *
     * This is essentially a heartbeat that emits a background job for every
     * account to run any scheduled jobs the account may have.
     *
     * @param RequestInterface $request
     * @param string $uniqueLockName
     * @return void
     */
    private function queueScheduledJobs(RequestInterface $request, $uniqueLockName)
    {
        $running = true;
        while ($running) {
            /*
             * Get all accounts - this function queries the DB each time so we
             * do not need to refresh since this is a long-lived process
             */
            $accounts = $this->application->getAccounts();
            foreach ($accounts as $account) {
                /*
                 * The ScheduleRunner worker will check for any scheduled jobs
                 * for each account and spawn more background processes. This helps
                 * us distribute the load. If ScheduledWork is taking too long, we
                 * can simply add more worker machines to the cluster
                 */
                $jobData = ['account_id' => $account->getAccountId()];
                $this->workerService->doWorkBackground(ScheduleRunnerWorker::class, $jobData);
            }

            // // Renew the lock to make sure we do not expire since it times out in 2 minutes
            // $application->extendLock($uniqueLockName);

            // Exit if we have received a stop signal or are only supposed to run once
            if ($request->isStopping() || $request->getParam('runonce')) {
                // Immediate break the main while loop
                $running = false;
            } else {
                // Sleep for a minute before checking for the next scheduled job
                sleep(60);
            }
        }

        return;
    }
}
