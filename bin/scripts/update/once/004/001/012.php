<?php
/**
 * There was a bug that meant capped was not being set. We fixed it, but it only applied
 * to new account. Existing account needed to be updated here.
 */
use Netric\Db\Relational\RelationalDbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$db->update("app_object_types", ["capped" => 1000000], ["name" => "activity"]);
$db->update("app_object_types", ["capped" => 100000], ["name" => "notification"]);
