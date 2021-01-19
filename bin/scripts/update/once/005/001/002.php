<?php

/**
 * Get all the entities that have null value in sort_order column and update the value based on the ts_entered
 */
// TODO: removing this because it appears to be causing failures in production

// use Netric\Db\Relational\RelationalDbFactory;
// use Netric\Entity\EntityLoaderFactory;
// use Netric\Entity\EntityFactoryFactory;
// use Netric\EntityDefinition\ObjectTypes;

// $account = $this->getAccount();
// $user = $account->getAuthenticatedUser();
// $serviceManager = $account->getServiceManager();
// $db = $serviceManager->get(RelationalDbFactory::class);
// $entityFactory = $serviceManager->get(EntityFactoryFactory::class);
// $entityLoader = $serviceManager->get(EntityLoaderFactory::class);


// $db->beginTransaction();

// // Do not timeout for this long query
// $db->query('set statement_timeout to 0');

// $result = $db->query("UPDATE entity 
//     SET sort_order=cast(extract(EPOCH FROM(ts_entered - '1/1/2000'::timestamp)) as integer) 
//     WHERE sort_order IS NULL and ts_entered is not null");

// // Commit the transaction
// $db->commitTransaction();
