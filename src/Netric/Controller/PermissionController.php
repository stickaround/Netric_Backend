<?php
namespace Netric\Controller;

use Netric\Mvc;
use Netric\Permissions\Dacl;
use Netric\Permissions\Dacl\Entry;
use Netric\Permissions\DaclLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityGroupings\LoaderFactory as EntityGroupingLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\EntityDefinition\DataMapper\DataMapperFactory as EntityDefinitionDataMapperFactory;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Controller for interaction with permission/security
 */
class PermissionController extends Mvc\AbstractAccountController
{
    /**
     * POST pass-through for get action
     */
    public function postGetDaclForEntityAction()
    {
        return $this->getGetDaclForEntityAction();
    }

    /**
     * Get the DACL data for entity
     */
    public function getGetDaclForEntityAction()
    {
        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $daclLoader = $serviceManager->get(DaclLoaderFactory::class);
        $defLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        $groupingLoader = $serviceManager->get(EntityGroupingLoaderFactory::class);

        $objData = $this->getRequest()->getParams();

        // Make sure we have the minimum required params
        if (empty($objData['obj_type'])) {
            return $this->sendOutput([
                "error" => "obj_type is a required param.",
                "params" => $objData
            ]);
        }

        // Set the Dacl based on the obj_type provided in the params
        $def = $defLoader->get($objData['obj_type']);
        $dacl = $daclLoader->getForEntityDefinition($def);

        // If id is set, then we will update the dacl and retrieve the entity by id
        if (!empty($objData['id'])) {
            $entity = $entityLoader->get($objData['obj_type'], $objData['id']);
            $dacl = $daclLoader->getForEntity($entity);
        }

        $retData = [
            "dacl" => $dacl->toArray(),
            "user_names" => [],
            "group_names" => []
        ];

        $users = $dacl->getUsers();
        // Get the user details
        foreach ($users as $userId) {
            $userEntity = $entityLoader->get(ObjectTypes::USER, $userId);

            if ($userEntity) {
                $retData["user_names"][$userId] = $userEntity->getName();
            }
        }

        $userGroups = $groupingLoader->get(ObjectTypes::USER, "groups");
        $groups = $userGroups->toArray();

        // Get the group details
        foreach ($groups as $groupDetails) {
            $retData["group_names"][$groupDetails["id"]] = $groupDetails["name"];
        }

        return $this->sendOutput($retData);
    }

    /**
     * PUT pass-through for save
     */
    public function putSaveDaclEntriesAction()
    {
        return $this->postSaveDaclEntriesAction();
    }

    /**
     * Save the Dacl Entries
     */
    public function postSaveDaclEntriesAction()
    {
        $rawBody = $this->getRequest()->getBody();

        if (!$rawBody) {
            return $this->sendOutput(["error" => "Request input is not valid"]);
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        // Make sure we have the minimum required params
        if (empty($objData['obj_type'])) {
            return $this->sendOutput([
                "error" => "obj_type is a required param",
                "params" => $objData
            ]);
        }

        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $daclLoader = $serviceManager->get(DaclLoaderFactory::class);
        $entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
        $defLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        $definitionDatamapper = $serviceManager->get(EntityDefinitionDataMapperFactory::class);

        // Retrieve the entity by id amd return the result
        if (!empty($objData['entity_id'])) {
            $entity = $entityLoader->get($objData['obj_type'], $objData['entity_id']);
            $dacl = $daclLoader->getForEntity($entity);
            $dacl->fromArray($objData);
            $entity->setValue("dacl", json_encode($dacl->toArray()));

            if ($entityDataMapper->save($entity)) {
                return $this->sendOutput($dacl->toArray());
            } else {
                return $this->sendOutput(["error" => "Error saving Dacl: " . $entityDataMapper->getLastError()]);
            }
        }

        $def = $defLoader->get($objData['obj_type']);
        $dacl = $daclLoader->getForEntityDefinition($def);
        $dacl->fromArray($objData);
        $def->setDacl($dacl);
        $definitionDatamapper->save($def);
        return $this->sendOutput($dacl->toArray());
    }
}
