<?php
/**
 * Add system types to the database
 */

use Netric\EntityDefinition;

$types = array(
    array("obj_type"=>"invoice", "title"=>"Invoice", "object_table"=>"customer_invoices", "revision"=>"0", "system"=>true),
    array("obj_type"=>"contact_personal", "title"=>"Personal Contact", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"content_feed", "title"=>"Content Feed", "object_table"=>"xml_feeds", "revision"=>"0", "system"=>true),
    array("obj_type"=>"discussion", "title"=>"Discussion", "object_table"=>"discussions", "revision"=>"0", "system"=>true),
    array("obj_type"=>"email_message", "title"=>"Email", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"content_feed_post", "title"=>"Feed Post", "object_table"=>"xml_feed_posts", "revision"=>"0", "system"=>true),
    array("obj_type"=>"project_milestone", "title"=>"Milestone", "object_table"=>"project_milestones", "revision"=>"0", "system"=>true),
    array("obj_type"=>"task", "title"=>"Task", "object_table"=>"project_tasks", "revision"=>"0", "system"=>true),
    array("obj_type"=>"email_thread", "title"=>"Email Thread", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"calendar_event", "title"=>"Event", "object_table"=>"calendar_events", "revision"=>"0", "system"=>true),
    array("obj_type"=>"report", "title"=>"Report", "object_table"=>"reports", "revision"=>"0", "system"=>true),
    array("obj_type"=>"user", "title"=>"User", "object_table"=>"users", "revision"=>"0", "system"=>true),
    array("obj_type"=>"comment", "title"=>"Comment", "object_table"=>"comments", "revision"=>"0", "system"=>true),
    array("obj_type"=>"activity", "title"=>"Activity", "object_table"=>"", "revision"=>"0", "system"=>true, "capped"=>"1000000"),
    array("obj_type"=>"lead", "title"=>"Lead", "object_table"=>"customer_leads", "revision"=>"0", "system"=>true),
    array("obj_type"=>"case", "title"=>"Case", "object_table"=>"project_bugs", "revision"=>"0", "system"=>true),
    array("obj_type"=>"project", "title"=>"Project", "object_table"=>"projects", "revision"=>"0", "system"=>true),
    array("obj_type"=>"note", "title"=>"Note", "object_table"=>"user_notes", "revision"=>"0", "system"=>true),
    array("obj_type"=>"time", "title"=>"Time Log", "object_table"=>"project_time", "revision"=>"0", "system"=>true),
    array("obj_type"=>"product_family", "title"=>"Product Family", "object_table"=>"product_families", "revision"=>"0", "system"=>true),
    array("obj_type"=>"opportunity", "title"=>"Opportunity", "object_table"=>"customer_opportunities", "revision"=>"0", "system"=>true),
    array("obj_type"=>"product", "title"=>"Product", "object_table"=>"products", "revision"=>"0", "system"=>true),
    array("obj_type"=>"invoice_template", "title"=>"Invoice Template", "object_table"=>"customer_invoice_templates", "revision"=>"0", "system"=>true),
    array("obj_type"=>"infocenter_document", "title"=>"IC Document", "object_table"=>"ic_documents", "revision"=>"0", "system"=>true),
    array("obj_type"=>"email_message_attachment", "title"=>"Email Attachment", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"calendar_event_proposal", "title"=>"Meeting Proposal", "object_table"=>"calendar_event_coord", "revision"=>"0", "system"=>true),
    array("obj_type"=>"customer", "title"=>"Customer", "object_table"=>"customers", "revision"=>"0", "system"=>true),
    array("obj_type"=>"approval", "title"=>"Approval Request", "object_table"=>"workflow_approvals", "revision"=>"0", "system"=>true),
    array("obj_type"=>"member", "title"=>"Member", "object_table"=>"members", "revision"=>"0", "system"=>true),
    array("obj_type"=>"sales_order", "title"=>"Order", "object_table"=>"sales_orders", "revision"=>"0", "system"=>true),
    array("obj_type"=>"product_review", "title"=>"Product Review", "object_table"=>"product_reviews", "revision"=>"0", "system"=>true),
    array("obj_type"=>"dashboard", "title"=>"Dashboard", "object_table"=>"dashboard", "revision"=>"0", "system"=>true),
    array("obj_type"=>"sales_payment", "title"=>"Payment", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"project_story", "title"=>"User Story", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"folder", "title"=>"Folder", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"file", "title"=>"File", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"calendar", "title"=>"Calendar", "object_table"=>"calendars", "revision"=>"0", "system"=>true),
    array("obj_type"=>"html_template", "title"=>"HTML Template", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"email_campaign", "title"=>"Email Campaign", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"marketing_campaign", "title"=>"Campaign", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"cms_site", "title"=>"Site", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"cms_page", "title"=>"Page", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"cms_page_template", "title"=>"Page Template", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"cms_snippet", "title"=>"Snippet", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"phone_call", "title"=>"Phone Call", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"status_update", "title"=>"Status Update", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"reminder", "title"=>"Reminder", "object_table"=>"", "revision"=>"0", "system"=>true),
    array("obj_type"=>"notification", "title"=>"Notification", "object_table"=>"", "revision"=>"0", "system"=>true, "capped"=>"200000"),
);

$account = $this->getAccount();
if (!$account)
    throw new \RuntimeException("This must be run only against a single account");

$entityDefinitionDataMapper = $account->getServiceManager()->get("EntityDefinition_DataMapper");

// Loop through each type and add it if it does not exist
foreach ($types as $objDefData)
{
    echo "Running for " . $objDefData['obj_type'] . "\n";
    $existing = $entityDefinitionDataMapper->fetchByName($objDefData['obj_type']);
    if (!$existing || !$existing->getId())
    {
        $def = new EntityDefinition($objDefData['obj_type']);
        $def->fromArray($objDefData);
        if (!$entityDefinitionDataMapper->save($def))
            throw new \RuntimeException("Could not save " . $entityDefinitionDataMapper->getLastError());

        $this->printLine("Added object type " . $objDefData['obj_type'] . ":" . $def->getId());
    }
}