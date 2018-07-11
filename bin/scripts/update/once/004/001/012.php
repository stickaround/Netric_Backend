<?php
/**
 * There was a bug that meant capped was not being set. We fixed it, but it only applied
 * to new account. Existing account needed to be updated here.
 */
use Netric\Db\DbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(DbFactory::class);

$db->query("UPDATE app_object_types SET capped='1000000' WHERE name='activity'");
$db->query("UPDATE app_object_types SET capped='100000' WHERE name='notification'");
