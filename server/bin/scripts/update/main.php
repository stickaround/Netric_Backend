<?php
/**
 * Main execution script for the update program
 */
use Netric\Application\Setup\Setup;

/*
 * Update the application database
 */
$applicationSetup = new Setup();
$applicationSetup->updateApplication($this->getApplication());

/**
 * Loop through each account and update it
 */
$accounts = $this->getAccounts();
foreach ($accounts as $account)
{
    $setup = new Setup();
    $setup->updateAccount($account);
}
