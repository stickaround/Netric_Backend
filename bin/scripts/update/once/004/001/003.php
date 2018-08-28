<?php
/**
 * Transfer the email_accounts table to objects_email_account_act
 *
 * This script will simply loop thru email_accounts table and get each record
 * The record will be imported to the new email_account entity via ::fromArray
 * Then it will be saved using DataMapper::save()
 */
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Crypt\VaultServiceFactory;
use Netric\Db\Relational\RelationalDbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
$entityIndex = $serviceManager->get(IndexFactory::class);
$vaultService = $serviceManager->get(VaultServiceFactory::class);
$blockCypher = new \Netric\Crypt\BlockCipher($vaultService->getSecret("EntityEnc"));

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
    $result = $db->query($sql);

    foreach ($result->fetchAll() as $row) {
        // Make sure the account does not already exist
        $query = new \Netric\EntityQuery("email_account");
        $query->where("address")->equals($row['address']);
        $ret = $entityIndex->executeQuery($query);
        if ($ret->getNum()) {
            // skip acount since it was already imported
            continue;
        }

        $entity = $entityLoader->create("email_account");

        // Make sure to set the id to null, so the system will insert the record and create the new entity
        $oldid = $row['id'];
        $row['id'] = null;
        $row['owner_id'] = $row['user_id'];

        // Decrypt the password
        $row['password'] = $blockCypher->decrypt($row['password']);

        // Import the email_account details
        $entity->fromArray($row);

        // Save the entity with the new email_account details
        $newid = $entityLoader->save($entity);

        // Update all email messages
        $db->update("objects_email_message_act", ["email_account" => $newid], ["email_account" => $oldid]);
        $db->update("objects_email_message_del", ["email_account" => $newid], ["email_account" => $oldid]);
    }
}
