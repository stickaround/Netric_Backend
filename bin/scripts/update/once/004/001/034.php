<?php
/**
 * Get the entities from objects_* table and update its object reference.
 */
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\DataMapper\DataMapperFactory;
use Netric\Entity\EntityFactoryFactory;
use Netric\EntityDefinition\ObjectTypes;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityFactory = $serviceManager->get(EntityFactoryFactory::class);
$entityDm = $serviceManager->get(DataMapperFactory::class);

$objTypes = [
    ObjectTypes::APPROVAL,
    ObjectTypes::CALENDAR,
    ObjectTypes::CALENDAR_EVENT,
    ObjectTypes::CALENDAR_EVENT_PROPOSAL,
    ObjectTypes::CONTACT,
    ObjectTypes::CONTACT_PERSONAL,
    ObjectTypes::COMMENT,
    ObjectTypes::CONTENT_FEED,
    ObjectTypes::CONTENT_FEED_POST,
    ObjectTypes::DASHBOARD,
    ObjectTypes::DASHBOARD_WIDGET,
    ObjectTypes::DISCUSSION,
    ObjectTypes::DOCUMENT,
    ObjectTypes::FILE,
    ObjectTypes::FOLDER,
    ObjectTypes::HTML_TEMPLATE,
    ObjectTypes::HTML_SNIPPET,
    ObjectTypes::INVOICE,
    ObjectTypes::INVOICE_TEMPLATE,
    ObjectTypes::ISSUE,
    ObjectTypes::LEAD,
    ObjectTypes::MARKETING_CAMPAIGN,
    ObjectTypes::MEMBER,
    ObjectTypes::NOTE,
    ObjectTypes::NOTIFICATION,
    ObjectTypes::OPPORTUNITY,
    ObjectTypes::PAGE,
    ObjectTypes::PAGE_TEMPLATE,
    ObjectTypes::PHONE_CALL,
    ObjectTypes::PRODUCT,
    ObjectTypes::PRODUCT_FAMILY,
    ObjectTypes::PRODUCT_REVIEW,
    ObjectTypes::PROJECT,
    ObjectTypes::PROJECT_MILESTONE,
    ObjectTypes::REMINDER,
    ObjectTypes::REPORT,
    ObjectTypes::SALES_ORDER,
    ObjectTypes::SALES_PAYMENT,
    ObjectTypes::SALES_PAYMENT_PROFILE,
    ObjectTypes::SITE,
    ObjectTypes::USER,
    ObjectTypes::USER_TEAM
];

// Loop thru object types
forEach($objTypes as $objType) {
    // Do the same thing when updating the path
    $db->beginTransaction();

    // Do not timeout for this long query
    $db->query('set statement_timeout to 0');

    $result = $db->query("SELECT id, guid, field_data FROM objects_$objType");

    // Commit the transaction
    $db->commitTransaction();

    foreach ($result->fetchAll() as $rowData) {
        if (isset($rowData['id'])) {
            $entityData = json_decode($rowData['field_data'], true);
        
            if (empty($entityData['id'])) {
                $entityData['id'] = $rowData['id'];
            }

            if (empty($entityData['guid'])) {
                $entityData['guid'] = $rowData['guid'];
            }

            $entity = $entityFactory->create($objType);
            $entity->fromArray($entityData);
            
            // Just load the entity and the object references will be updated to guid.
            $entityDm->getById($entity, $rowData['id']);
        }
    }
}

