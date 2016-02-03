<?php
/**
 * Add default modules to each account
 */

$account = $this->getAccount();
if (!$account)
    throw new \RuntimeException("This must be run only against a single account");

// Get modules from data
$modules = include("data/account/modules.php");

foreach ($modules as $moduleData) {
    // TODO: Save the module
}