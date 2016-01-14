<?php
/**
 * Main execution script for the update program
 */
use Netric\Application\Setup;

/**
 * Loop through each account and update it
 */
$accounts = $this->getAccounts();
foreach ($accounts as $account)
{
    $setup = new Setup();
    $setup->updateAccount($account);
}
