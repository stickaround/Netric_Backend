<?php

/**
 * Add default workflows
 */

use Netric\WorkFlow\DataMapper\DataMapperFactory;

$account = $this->getAccount();
if (!$account) {
    throw new \RuntimeException("This must be run only against a single account");
}

// TODO: We will work on these when we have recreated the workflow engine
// // Get services
// $serviceLocator = $account->getServiceManager();
// $workFlowDataMapper = $serviceLocator->get(DataMapperFactory::class);
// $actionFactory = new Netric\WorkFlow\Action\ActionFactory($serviceLocator);

// // Get all WorkFlows up front
// $allWorkFlows = $workFlowDataMapper->getWorkFlows();

// // Get data for creating WorkFlows
// $workFlowsData = require(__DIR__ . "/../../../../data/account/workflows.php");

// // Loop through each workflow data entry and create it if it does not exist
// foreach ($workFlowsData as $workFlowData) {
//     $found = false;

//     // Check to see if it already exists
//     foreach ($allWorkFlows as $workFlow) {
//         if ($workFlow->getObjType() == $workFlowData['obj_type']
//             && $workFlow->getName() == $workFlowData['name']) {
//             $found = true;
//         }
//     }

//     // If not already exists, then add it
//     if (!$found) {
//         $workFlow = new Netric\WorkFlow\WorkFlow($actionFactory);
//         $workFlow->fromArray($workFlowData);
//         $workFlowDataMapper->save($workFlow);
//     }
// }