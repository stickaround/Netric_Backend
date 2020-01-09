<?php

/**
 * Remove the email/smpt_* settings in settings table
 */
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Db\Relational\Exception\DatabaseQueryException;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$log = $account->getApplication()->getLog();
$db = $serviceManager->get(RelationalDbFactory::class);

try {
    $db->query("DELETE FROM settings WHERE name LIKE 'email/smtp%'");
} catch (DatabaseQueryException $ex) {
    $log->error("UpdatOnce004.001.028:: Error running Delete Query - " . $ex->getMessage());
}