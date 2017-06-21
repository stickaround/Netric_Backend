<?php
/**
 * Drop the async table
 */

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get("Netric/Db/Db");
$db->query("DROP TABLE IF EXISTS async_states;");
