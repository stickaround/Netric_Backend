<?php
/**
 * Update all the workflow conditions and rename the user_id field name to owner_id
 */
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\EntityFactoryFactory;
use Netric\WorkFlow\WorkFlowManager;
use Netric\WorkFlow\WorkFlowFactory;
use Netric\WorkFlow\DataMapper\DataMapperFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$workflowDm = $serviceManager->get(DataMapperFactory::class);

// Do the same thing when updating the path
$db->beginTransaction();

// Do not timeout for this long query
$db->query('set statement_timeout to 0');

$result = $db->query("SELECT * FROM objects_workflow");

// Commit the transaction
$db->commitTransaction();

foreach ($result->fetchAll() as $rowData) {
    $workflowData = json_decode($rowData['field_data'], true);

    $workflow = $serviceManager->get(WorkFlowFactory::class);
    $workflow->fromArray($workflowData);

    // Get the existing conditions
    $workflowConditions = $workflow->getConditions();

    // Clear the conditions so we can add the updated conditions later (renamed user_id to owner_id fields)
    $workflow->clearConditions();

    // Loop thru each conditions and look for user_id field name
    $updated = false;
    forEach($workflowConditions as $condition) {
      
        // Rename the condition from user_id to owner_id
        if ($condition->fieldName == "user_id") {
            $condition->fieldName = "owner_id";
            $updated = true;
        }

        $workflow->addCondition($condition);
    }

    // If this workflow's condition were updated, then we need to save the changes
    if ($updated) {
      $workflowDm->save($workflow);
    }
}

