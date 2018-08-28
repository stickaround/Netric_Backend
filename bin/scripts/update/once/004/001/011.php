<?php
/**
 * We hd a bug in 003.php that caused email accounts to be duplicated every time
 * the system/update script ran...
 *
 * This script will look for those duplicate email accounts and delete them.
 *
 * When email was delivered for each fo these accounts it was duplicated because
 * email_account synchronizes independently so it is safe to delete all the messages
 * in the duplicate accounts.
 */
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Log\LogFactory;
use Netric\EntityQuery;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
$entityIndex = $serviceManager->get(IndexFactory::class);
$log = $serviceManager->get(LogFactory::class);

// Find all the duplicates
$sql = "SELECT address, owner_id, count(*) FROM objects_email_account_act
        GROUP BY address, owner_id HAVING count(*) > 1;";

$result = $db->query($sql);
foreach ($result->fetchAll() as $row) {

    // Loop through the duplicates and delete all but hte first
    $query = new EntityQuery("email_account");
    $query->where("address")->equals($row['address']);
    $query->orderBy("id");
    $ret = $entityIndex->executeQuery($query);
    // Skip over the first one with $j=1, we will keep it and delete all the rest
    for ($j = 1; $j < $ret->getTotalNum(); $j++) {
        $emailAccount = $ret->getEntity($j);

        // First delete all messages in this account
        $messageQuery = new \Netric\EntityQuery("email_message");
        $messageQuery->where("email_account")->equals($emailAccount->getId());
        $messageRet = $entityIndex->executeQuery($messageQuery);
        $numMessagesToDelete= $messageRet->getTotalNum();
        for ($m = 0; $m < $numMessagesToDelete; $m++) {
            $emailMessage = $messageRet->getEntity($m);

            // Make sure the message still exists
            if ($emailMessage) {
                $entityLoader->delete($emailMessage, true);
            }

            $log->info(
                "Update 004.001.011 deleted email message $m of $numMessagesToDelete for " .
                $emailAccount->getValue("address") . ":" . $emailAccount->getId()
            );
        }

        // Now delete the account
        $entityLoader->delete($emailAccount);

        $log->info(
            "Update 004.001.011 deleted email account " .
            $emailAccount->getValue("address") . ":" . $emailAccount->getId() .
            " and $numMessagesToDelete messages"
        );
    }
}
