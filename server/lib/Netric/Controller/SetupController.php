<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */
namespace Netric\Controller;

use Netric\Application\Response\ConsoleResponse;
use Netric\Mvc;
use Netric\Entity\ObjType\UserEntity;
use Netric\Permissions\Dacl;
use Netric\Application\Setup\Setup;

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

        // First make sure they passed the username and password params to the command
        if (!$request->getParam("username") || !$request->getParam("password")) {
            throw new \InvalidArgumentException(
                "Please enter --username=myuser and --password=mypass arguments " .
                "for the default account before installing the application."
            );
        }

        // Create the system database if it does not exist
        if (!$application->initDb()) {
            throw new \RuntimeException("Could not create application database");
        }

        // Create the default account
        $config = $application->getConfig();
        if (!$application->createAccount($config->default_account, $request->getParam("username"), $request->getParam("password"))) {
            throw new \RuntimeException("Could not create default account");
        }

        $response = new ConsoleResponse();
        $response->writeLine(
            "-- Install Complete: " .
            "username=" . $request->getParam("username") . ", " .
            "password=" . $request->getParam("password") ." --"
        );
        return $response;
    }

    /**
     * Update account(s) and application to latest version
     */
    public function consoleUpdateAction()
    {
        $response = new ConsoleResponse();

        // Update the application database
        $response->write("Updating application");
        $applicationSetup = new Setup();
        if ($applicationSetup->updateApplication($this->getApplication()))
        {
            $response->write("\t\t[done]\n");
        }
        else
        {
            throw new \Exception("Failed to update application: " . $applicationSetup->getLastError()->getMessage());
        }

        // Loop through each account and update it
        $accounts = $this->getApplication()->getAccounts();
        foreach ($accounts as $account)
        {
            $response->write("Updating account " . $account->getName());
            $setup = new Setup();
            if ($setup->updateAccount($account))
            {
                $response->write("\t[done]\n");
            }
            else
            {
                throw new \Exception("Failed to update account: " . $setup->getLastError()->getMessage());
            }
        }

        $response->writeLine("-- Update Complete --");
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
