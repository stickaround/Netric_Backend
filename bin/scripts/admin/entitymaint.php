<?php
use Netric\Entity\EntityMaintainerService;
/**
 * Perform entity maintenance
 */
$account = $this->getAccount();
if (!$account)
    throw new \RuntimeException("This must be run only against a single account");

$entityMaintainerService = $account->getServiceManager()->get(EntityMaintainerService::class);

// Run all maintenance tasks
$entityMaintainerService->runAll();
