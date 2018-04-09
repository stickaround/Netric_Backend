<?php
namespace Netric\Controller;

use \Netric\Mvc;
use Netric\Permissions\Dacl;
use Netric\Permissions\Dacl\Entry;
use Netric\Permissions\DaclLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Entity\ObjType\UserEntity;

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

        $params = $this->getRequest()->getParams();

        // Make sure we have the minimum required params
        if (!$params['obj_type'] || !$params['id']) {
            return $this->sendOutput(
                array(
                    "error" => "obj_type and id are required params",
                    "params" => $params
                )
            );
        }

        // Retrieve the entity by id
        $entity = $entityLoader->get($params['obj_type'], $params['id']);
        $dacl = $daclLoader->getForEntity($entity);

        $users = $dacl->getUsers();
        // Get the user details
        foreach ($users as $userId) {
            $userEntity = $entityLoader->get("user", $userId);
            $dacl->setUserName($userId, $userEntity->getName());
        }

        return $this->sendOutput($dacl->toArray());
    }

    /**
     * PUT pass-through for save
     */
    public function putSaveDaclEntriesAction()
    {
        return $this->postSaveDaclEntriesAction();
    }

    public function postSaveDaclEntriesAction()
    {
        $rawBody = $this->getRequest()->getBody();

        if (!$rawBody) {
            return $this->sendOutput(array("error" => "Request input is not valid"));
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        // Make sure we have the minimum required params
        if (!$objData['obj_type'] || !$objData['entity_id']) {
            return $this->sendOutput(
                array(
                    "error" => "obj_type and id are required params",
                    "params" => $params
                )
            );
        }

        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $daclLoader = $serviceManager->get(DaclLoaderFactory::class);
        $entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);

        // Retrieve the entity by id
        $entity = $entityLoader->get($objData['obj_type'], $objData['entity_id']);
        $dacl = $daclLoader->getForEntity($entity);
        $dacl->fromArray($objData);
        $entity->setValue("dacl", json_encode($dacl->toArray()));

        if ($entityDataMapper->save($entity)) {
            return $this->sendOutput($dacl->toArray());
        } else {
            return $this->sendOutput(array("error" => "Error saving: " . $entityDataMapper->getLastError()));
        }
    }
}