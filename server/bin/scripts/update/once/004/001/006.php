<?php
/**
 * Add the email_message application in the applications table
 *
 * This script will check if the email_message is existing in the applications table
 * If not, then it will add a new entry for email_message
 */
use Netric\Account\Module\Module;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get("Netric/Account/Module/DataMapper/DataMapper");

$emailMessageModule = $db->get("email_message");

// Check if the email message is not yet in the applications table
if(!$emailMessageModule)
{
    $module = new Module();

    $data = array(
        "id" => null,
        "name" => "email_message",
        "title" => "Email Message",
        "short_title" => "Email Message",
        "scope" => Module::SCOPE_EVERYONE,
        "system" => true,
        "icon" => "envelope-o",
        "default_route" => "all-emails"
    );

    // Import the email message data
    $module->fromArray($data);

    // Set the module as dirty so it can be saved
    $module->setDirty(true);

    // Save the email message module
    $result = $db->save($module);
}