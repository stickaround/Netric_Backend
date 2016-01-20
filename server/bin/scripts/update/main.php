<?php
/**
 * Main execution script for the update program
 */
use Netric\Application\Setup\Setup;

/*
 * Update the application database
 */
$applicationSetup = new Setup();
if ($applicationSetup->updateApplication($this->getApplication()))
{
    $this->printLine("Finished updating application");
}
else
{
    throw new \Exception("Failed to update application: " . $applicationSetup->getLastError()->getMessage());
}

/*
 * Loop through each account and update it
 */
$accounts = $this->getAccounts();
foreach ($accounts as $account)
{
    $setup = new Setup();
    if ($setup->updateAccount($account))
    {
        $this->printLine("Updated account: " . $account->getName());
    }
    else
    {
        throw new \Exception("Failed to update account: " . $setup->getLastError()->getMessage());
    }
}
