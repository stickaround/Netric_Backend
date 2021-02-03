<?php

namespace Netric\Controller;

use Netric\Mvc;
use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Account\AccountContainerFactory;
use Netric\Account\AccountContainerInterface;
use Netric\Account\AccountSetup;
use Netric\Application\Response\HttpResponse;
use Netric\Application\Response\ConsoleResponse;
use Netric\Request\ConsoleRequest;
use Netric\Application\Setup\AccountUpdater;
use Netric\Application\Setup\Setup;
use Netric\Application\DatabaseSetup;
use Netric\Application\Application;
use Netric\Request\HttpRequest;
use Netric\Authentication\AuthenticationService;
use Netric\Entity\ObjType\UserEntity;
use Netric\Permissions\Dacl;
use Netric\Log\LogInterface;
use Netric\Console\BinScript;
use RuntimeException;
use InvalidArgumentException;

/**
 * Controller used for setting up netric - mostly from the command line
 */
class SetupController extends AbstractFactoriedController implements ControllerInterface
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
     * Service that has the netric account setup functions
     */
    private AccountSetup $accountSetup;

    /**
     * Service that has the database setup functions
     */
    private DatabaseSetup $dbSetup;

    /**
     * Logger for recording what is going on
     */
    private LogInterface $log;

    /**
     * The current application instance
     */
    private Application $application;

    /**
     * Initialize controller and all dependencies
     *
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param AccountSetup $accountSetup Service that has the netric account setup functions
     * @param DatabaseSetup $dbSetup Service that has the database setup functions
     * @param LogInterface $log Logger for recording what is going on
     * @param Application $application The current application instance
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        AccountSetup $accountSetup,
        DatabaseSetup $dbSetup,
        LogInterface $log,
        Application $application
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->accountSetup = $accountSetup;
        $this->dbSetup = $dbSetup;
        $this->log = $log;
        $this->application = $application;
    }

    /**
     * Get the currently authenticated account
     *
     * @return Account
     */
    private function getAuthenticatedAccount()
    {
        $authIdentity = $this->authService->getIdentity();
        if (!$authIdentity) {
            return null;
        }

        return $this->accountContainer->loadById($authIdentity->getAccountId());
    }

    /**
     * Install netric by initializing the application db and default account
     * 
     * @param HttpRequest $request Request object for this run
     * @return ConsoleResponse
     */
    public function consoleInstallAction(ConsoleRequest $request): ConsoleResponse
    {
        $response = new ConsoleResponse();

        // First make sure they passed the username and password params to the command
        if (
            !$request->getParam("account") ||
            !$request->getParam("email") ||
            !$request->getParam("username") ||
            !$request->getParam("password")
        ) {
            throw new InvalidArgumentException(
                "Required params\n" .
                    "--account=accountname\n" .
                    "--username=test\n" .
                    "--email=test@netric.com\n" .
                    "--password=mypass\n" .
                    "\n"
            );
        }

        // TODO: Find out a way to determine if netric is already installed

        // Create the database and update the schema        
        $this->dbSetup->updateDatabaseSchema();

        // Create account
        if (!$this->application->createAccount(
            $request->getParam("account"),
            $request->getParam("username"),
            $request->getParam("email"),
            $request->getParam("password")
        )) {
            throw new RuntimeException("Could not create default account");
        }

        // Let the user know we have created the account
        $response->writeLine(
            "-- Install Complete! You can log in with:\n" .
                "email=" . $request->getParam("email") .
                "\n" .
                "password=" . $request->getParam("password") .
                "\n"
        );
        return $response;
    }

    /**
     * Update account(s) and application to latest version
     * 
     * @return ConsoleResponse
     */
    public function consoleUpdateAction(): ConsoleResponse
    {
        $response = new ConsoleResponse();

        // Update the application database
        $this->log->info("SetupController:: Updating application.");
        $response->write("Updating application");

        $this->dbSetup->updateDatabaseSchema();

        //        $applicationSetup = new Setup();
        //        if (!$applicationSetup->updateApplication($this->getApplication())) {
        //            $log->error(
        //                "SetupController: Failed to update application: " .
        //                    $applicationSetup->getLastError()->getMessage()
        //            );
        //
        //            throw new \Exception(
        //                "Failed to update application: " .
        //                    $applicationSetup->getLastError()->getMessage()
        //            );
        //        }

        $response->write("\t\t[done]\n");

        // Loop through each account and update it
        $accounts = $this->application->getAccounts();
        foreach ($accounts as $account) {
            $response->write("Updating account {$account->getName()}. ");
            // $updater = new AccountUpdater($account);
            // if (!$updater->runUpdates($accounts)) {
            //     $log->error("SetupController: Failed to update account: " . $updater->getLastError()->getMessage());
            //     throw new \Exception("Failed to update account: " . $updater->getLastError()->getMessage());
            // }

            $response->write("\t[done]\n");
        }

        $response->writeLine("-- Update Complete --");
        return $response;
    }

    /**
     * Run a specific script
     * 
     * @param ConsoleRequest $request Request object for this run
     * @return ConsoleResponse
     */
    public function consoleRunAction(ConsoleRequest $request): ConsoleResponse
    {
        $rootPath = dirname(__FILE__) . "/../../../bin/scripts";
        $scriptName = $request->getParam("script");
        $script = new BinScript($this->application, $this->account);
        $script->run($rootPath . "/" . $scriptName);
        $response = new ConsoleResponse();
        $response->setReturnCode(0);
        return $response;
    }

    /**
     * Get the current version
     */
    public function getVersionAction()
    {
        $response = new ConsoleResponse();
        $response->write(2);
        return $response;
    }

    /**
     * Create a unique name for an account
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getGenerateUniqueAccountNameAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);
        $response->setContentType(HttpResponse::TYPE_JSON);

        if ($this->testMode) {
            $response->suppressOutput(true);
        }

        $originalName = $request->getParam("name");
        $uniqueName = $this->accountSetup->getUniqueAccountName($originalName);
        $response->write(['name' => $uniqueName]);
        return $response;
    }

    /**
     * Create a new account
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postCreateAccountAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);
        $response->setContentType(HttpResponse::TYPE_JSON);

        if ($this->testMode) {
            $response->suppressOutput(true);
        }

        $rawBody = $request->getBody();
        if (!$rawBody) {
            $response->write(['error' => 'Invalid params in body']);
            return $response;
        }

        $params = json_decode($rawBody, true);

        // Make sure that the account name is unique
        $accountName = isset($params['account_name']) ? $params['account_name'] : '';
        $accountName = $this->accountSetup->getUniqueAccountName($accountName);

        // Create the account        
        $createdAccount = $this->application->createAccount(
            $accountName,
            $params['username'],
            $params['email'],
            $params['password']
        );

        if (!$createdAccount) {
            $response->write(['error' => 'Failed to create account.']);
            return $response;
        }

        // Encode new account data and return it to the client for further processing
        $response->write($createdAccount->toArray());
        return $response;
    }
}
