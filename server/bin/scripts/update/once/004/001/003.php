<?php

/**
 * Transfer the email_accounts table to objects_email_account_act
 *
 * This script will simply loop thru email_accounts table and get each record
 * The record will be imported to the new email_account entity via ::fromArray
 * Then it will be saved using DataMapper::save()
 */
$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get("Netric/Db/Db");
$entityLoader = $serviceManager->get("Netric/EntityLoader");
$entityDefinitionLoader = $serviceManager->get("Netric/EntityDefinitionLoader");

$def = null;
try {
    $def = $entityDefinitionLoader->get("email_account");
} catch (Exception $ex) {
    $serviceManager->get("Log")->error("Could not load email_account definition");
    $def = null;
}

// Make sure that we have email_account entities
if ($def) {

    // Find all email_account entities
    $sql = "SELECT * FROM email_accounts";
    $results = $db->query($sql);
    $totalNum = $db->getNumRows($results);

    // Loop over total num - the results will paginate as needed
    for ($i = 0; $i < $totalNum; $i++) {

        // Get email_account details
        $row = $db->getRow($results, $i);

        // Create a new email_account entity
        $entity = $entityLoader->create('email_account');

        // Make sure to set the id to null, so the system will insert the record and create the new entity
        $row['id'] = null;

        // Import the email_account details
        $entity->fromArray($row);

        // Save the entity with the new email_account details
        $entityLoader->save($entity);
    }
}