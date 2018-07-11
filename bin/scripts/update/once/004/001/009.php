<?php
/**
 * Drop the async table
 */
use Netric\Db\DbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(DbFactory::class);
$db->query("DROP TABLE IF EXISTS async_states;");
