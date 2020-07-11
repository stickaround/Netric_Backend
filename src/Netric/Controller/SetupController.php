<?php

namespace Netric\Controller;

use Netric\Application\Response\ConsoleResponse;
use Netric\Mvc;
use Netric\Entity\ObjType\UserEntity;
use Netric\Permissions\Dacl;
use Netric\Application\Setup\Setup;
use Netric\Console\BinScript;
use Netric\Application\Response\HttpResponse;
use Netric\Account\AccountSetupFactory;
use Netric\Application\DatabaseSetupFactory;
use Netric\Log\LogFactory;
use RuntimeException;
use InvalidArgumentException;

/**
 * Controller used for setting up netric - mostly from the command line
 */
class SetupController extends Mvc\AbstractController
{
    /**
     * Install netric by initializing the application db and default account
     */
    public function consoleInstallAction()
    {
        $request = $this->getRequest();
        $application = $this->getApplication();
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
        $serviceManager = $this->getApplication()->getServiceManager();
        $dbSetup = $serviceManager->get(DatabaseSetupFactory::class);
        $dbSetup->updateDatabaseSchema();

        // Create account
        if (!$application->createAccount(
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
     */
    public function consoleUpdateAction()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        $log = $serviceManager->get(LogFactory::class);

        $response = new ConsoleResponse();

        // Update the application database
        $log->info("SetupController:: Updating application.");
        $response->write("Updating application");
        $applicationSetup = new Setup();
        if (!$applicationSetup->updateApplication($this->getApplication())) {
            $log->error(
                "SetupController: Failed to update application: " .
                    $applicationSetup->getLastError()->getMessage()
            );

            throw new \Exception(
                "Failed to update application: " .
                    $applicationSetup->getLastError()->getMessage()
            );
        }

        $response->write("\t\t[done]\n");

        // Loop through each account and update it
        $accounts = $this->getApplication()->getAccounts();
        foreach ($accounts as $account) {
            $response->write("Updating account {$account->getName()}. ");
            $setup = new Setup();
            if (!$setup->updateAccount($account)) {
                $log->error("SetupController: Failed to update account: " . $setup->getLastError()->getMessage());
                throw new \Exception("Failed to update account: " . $setup->getLastError()->getMessage());
            }

            $response->write("\t[done]\n");
        }

        $response->writeLine("-- Update Complete --");
        return $response;
    }

    /**
     * Run a specific script
     */
    public function consoleRunAction()
    {
        $rootPath = dirname(__FILE__) . "/../../../bin/scripts";
        $scriptName = $this->getRequest()->getParam("script");
        $script = new BinScript($this->account->getApplication(), $this->account);
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
        return $this->sendOutput(2);
    }

    /**
     * Create a unique name for an account
     *
     * @return HttpResponse
     */
    public function getGenerateUniqueAccountNameAction()
    {
        $response = new HttpResponse($this->getRequest());
        $response->setContentType(HttpResponse::TYPE_JSON);

        if ($this->testMode) {
            $response->suppressOutput(true);
        }

        $originalName = $this->getRequest()->getParam("name");
        $serviceManager = $this->getApplication()->getServiceManager();
        $accountSetup = $serviceManager->get(AccountSetupFactory::class);
        $uniqueName = $accountSetup->getUniqueAccountName($originalName);
        $response->write(['name' => $uniqueName]);
        return $response;
    }

    /**
     * Create a new account
     *
     * @return HttpResponse
     */
    public function postCreateAccountAction()
    {
        $response = new HttpResponse($this->getRequest());
        $response->setContentType(HttpResponse::TYPE_JSON);

        if ($this->testMode) {
            $response->suppressOutput(true);
        }

        $rawBody = $this->getRequest()->getBody();

        if (!$rawBody) {
            $response->write(['error' => 'Invalid params in body']);
            return $response;
        }

        $params = json_decode($rawBody, true);

        // Make sure that the account name is unique
        $accountName = isset($params['account_name']) ? $params['account_name'] : '';
        $serviceManager = $this->getApplication()->getServiceManager();
        $accountSetup = $serviceManager->get(AccountSetupFactory::class);
        $accountName = $accountSetup->getUniqueAccountName($accountName);

        // Create the account
        $application = $this->getApplication();
        $createdAccount = $application->createAccount(
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
}
