<?php
namespace Netric\Controller;

use Netric\Entity\Entity;
use Netric\EntityDefinition\Field;
use Netric\Entity\EntityInterface;
use Netric\EntityQuery;
use Netric\EntityQuery\FormParser;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Mvc;
use Netric\Permissions\Dacl;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\FormsFactory;
use Netric\Entity\BrowserView\BrowserViewServiceFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Permissions\DaclLoaderFactory;
use Netric\EntityDefinition\DataMapper\DataMapperFactory as EntityDefinitionDataMapperFactory;
use Netric\Entity\DataMapper\DataMapperFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityGroupings\Group;

/**
 * Controller for interacting with entities
 */
class EntityController extends Mvc\AbstractAccountController
{
    /**
     * Test action used for automated tests
     *
     * @return string
     */
    public function getTestAction()
    {
        return $this->sendOutput("test");
    }

    /**
     * Get the definition (metadata) of an entity
     */
    public function getGetDefinitionAction()
    {
        $params = $this->getRequest()->getParams();
        if (!$params['obj_type']) {
            return $this->sendOutput(array("error" => "obj_type is a required param"));
        }

        // Get the service manager and current user
        $serviceManager = $this->account->getServiceManager();

        // Load the entity definition
        $defLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        
        try {
            // Get the definition data for this object type
            $def = $defLoader->get($params['obj_type']);

            if (!$def) {
                return $this->sendOutput(array("error" => $params['obj_type'] . " could not be loaded"));
            }
    
            $ret = $this->fillDefinitionArray($def);
            return $this->sendOutput($ret);
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }
    }

    /**
     * Query entities
     */
    public function postQueryAction()
    {
        $ret = array();
        $params = $this->getRequest()->getParams();

        if (!isset($params["obj_type"])) {
            return $this->sendOutput(array("error" => "obj_type must be set"));
        }

        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        $daclLoader = $this->account->getServiceManager()->get(DaclLoaderFactory::class);

        $query = new EntityQuery($params["obj_type"]);

        if (isset($params['offset'])) {
            $query->setOffset($params['offset']);
        }

        if (isset($params['limit'])) {
            $query->setLimit($params["limit"]);
        }

        // Parse values passed from POST or GET params
        FormParser::buildQuery($query, $params);

        try {
            // Execute the query
            $res = $index->executeQuery($query);
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }

        // Pagination
        // ---------------------------------------------
        $ret["total_num"] = $res->getTotalNum();
        $ret["offset"] = $res->getOffset();
        $ret["limit"] = $query->getLimit();

        // Set results
        $entities = array();
        for ($i = 0; $i < $res->getNum(); $i++) {
            $ent = $res->getEntity($i);

            // Export to array
            $entityData = $ent->toArray();

            // Put the current DACL in a special field to keep it from being overwritten when the entity is saved
            $dacl = $daclLoader->getForEntity($ent);
            $entityData["applied_dacl"] = $dacl->toArray();

            // Print full details
            $entities[] = $entityData;
        }
        $ret["entities"] = $entities;

        return $this->sendOutput($ret);
    }

    /**
     * GET pass-through for query
     */
    public function getQueryAction()
    {
        return $this->postQueryAction();
    }

    /**
     * POST pass-through for get action
     */
    public function postGetAction()
    {
        return $this->getGetAction();
    }

    /**
     * Retrieve a single entity
     */
    public function getGetAction()
    {
        $params = $this->getRequest()->getParams();
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $daclLoader = $this->account->getServiceManager()->get(DaclLoaderFactory::class);

        // Check if the parameters are posted via Post.
        $rawBody = $this->getRequest()->getBody();
        if ($rawBody) {
            $body = json_decode($rawBody, true);
            $params['obj_type'] = (isset($body['obj_type'])) ? $body['obj_type'] : null;
            $params['id'] = (isset($body['id'])) ? $body['id'] : null;
            $params['guid'] = (isset($body['guid'])) ? $body['guid'] : null;
            $params['uname'] = (isset($body['uname'])) ? $body['uname'] : null;
            $params['uname_conditions'] = (isset($body['uname_conditions'])) ? $body['uname_conditions'] : [];
        }

        // Get the entity utilizing whatever params were passed in
        $entity = null;

        try {
            if (!empty($params['guid'])) {
                // Retrieve the entity by guid
                $entity = $entityLoader->getByGuid($params['guid']);
            } elseif (isset($params['id']) && isset($params['obj_type'])) {
                // Retrieve the entity by obj_type and id
                $entity = $entityLoader->get($params['obj_type'], $params['id']);
            } elseif (!empty($params['uname'])) {
                // Retrieve the entity by a unique name and optional condition
                $entity = $entityLoader->getByUniqueName(
                    $params['obj_type'],
                    $params['uname'],
                    $params['uname_conditions']
                );
            } else {
                return $this->sendOutput(
                    array(
                        "error" => "guid, or obj_type + id, or uname are required params.",
                        "params" => $params
                    )
                );
            }
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }

        // Entity Could not be found - we might want to change this to a 404 status code
        if (!$entity) {
            return $this->sendOutput([]);
        }

        // If user is not allowed, then return an error
        if (!$this->checkIfUserIsAllowed($entity, Dacl::PERM_VIEW)) {
            return $this->sendOutput(
                array(
                    "error" => "You do not have permission to view this.",
                    "guid" => $entity->getValue('guid'),
                    "params" => $params
                )
            );
        }

        $entityData = $entity->toArray();

        // Put the current DACL in a special field to keep it from being overwritten when the entity is saved
        $dacl = $daclLoader->getForEntity($entity);
        $entityData["applied_dacl"] = $dacl->toArray();

        return $this->sendOutput($entityData);
    }

    /**
     * Save an entity
     */
    public function postSaveAction()
    {
        $rawBody = $this->getRequest()->getBody();
        if (!$rawBody) {
            return $this->sendOutput(array("error" => "Request input is not valid"));
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['obj_type'])) {
            return $this->sendOutput(array("error" => "obj_type is a required param"));
        }

        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        try {
            // Create a new entity to save
            $entity = $entityLoader->create($objData['obj_type']);

            // If editing an existing etity, then load it rather than using the new entity
            if (isset($objData['id']) && !empty($objData['id'])) {
                $entity = $entityLoader->get($objData['obj_type'], $objData['id']);
            }
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }

        // Parse the params
        $entity->fromArray($objData);

        // Save the entity
        $dataMapper = $this->account->getServiceManager()->get(DataMapperFactory::class);

        try {
            if (!$dataMapper->save($entity)) {
                return $this->sendOutput(array("error" => "Error saving: " . $dataMapper->getLastError()));
            }
        } catch(\RuntimeException $ex) {
            return $this->sendOutput(array("error" => "Error saving: " . $ex->getMessage()));
        }

        // Check to see if any new object_multi objects were sent awaiting save
        $this->savePendingObjectMultiObjects($entity, $objData);

        $entityData = $entity->toArray();

        // Put the current DACL in a special field to keep it from being overwritten when the entity is saved
        $daclLoader = $this->account->getServiceManager()->get(DaclLoaderFactory::class);
        $dacl = $daclLoader->getForEntity($entity);
        $entityData["applied_dacl"] = $dacl->toArray();

        // Return the saved entity
        return $this->sendOutput($entityData);
    }

    /**
     * PUT pass-through for save
     */
    public function putSaveAction()
    {
        return $this->postSaveAction();
    }

    /**
     * Remove an entity (or a list of entities)
     */
    public function getRemoveAction()
    {
        $ret = array();
        // objType is a required to determine what exactly we are deleting
        $objType = $this->request->getParam("obj_type");
        // IDs can either be a single entry or an array
        $ids = $this->request->getParam("id");

        // Check if raw body was sent
        if (!$objType && !$ids) {
            $rawBody = $this->getRequest()->getBody();
            $reqData = json_decode($rawBody, true);
            if ($reqData && is_array($reqData)) {
                $objType = $reqData['obj_type'];
                $ids = $reqData['ids'];
            }
        }

        // Convert a single id to an array so we can handle them all the same way
        if (!is_array($ids) && $ids) {
            $ids = array($ids);
        }

        if (!$objType) {
            return $this->sendOutput(array("error" => "obj_type is a required param"));
        }

        // Get the entity loader so we can initialize (and check the permissions for) each entity
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Get the datamapper to delete
        $dataMapper = $this->account->getServiceManager()->get(DataMapperFactory::class);
        
        try {
            foreach ($ids as $did) {
                $entity = $entityLoader->get($objType, $did);
    
                // Check first if we have permission to delete this entity
                if ($entity && $this->checkIfUserIsAllowed($entity, Dacl::PERM_DELETE)) {
                    // Proceed with the deleting this entity
                    if ($dataMapper->delete($entity)) {
                        $ret[] = $did;
                    }
                }
            }
        } catch(\RuntimeException $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }

        // Return what was deleted
        return $this->sendOutput($ret);
    }

    /**
     * POST pass-through for remove
     */
    public function postRemoveAction()
    {
        return $this->getRemoveAction();
    }

    /**
     * POST pass-through for get groupings action
     */
    public function postGetGroupingsAction()
    {
        return $this->getGetGroupingsAction();
    }

    /**
     * Get groupings for an object
     */
    public function getGetGroupingsAction()
    {
        $objType = $this->request->getParam("obj_type");
        $fieldName = $this->request->getParam("field_name");

        if (!$objType || !$fieldName) {
            return $this->sendOutput(array("error" => "obj_type & field_name are required params"));
        }

        // Get the groupingLoader that will be used to get the groupings model
        $groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);

        // Get the groupings for this $objType and $fieldName
        try {
            $groupings = $this->getGroupings($groupingLoader, $objType, $fieldName);
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }

        if (!$groupings) {
            return $this->sendOutput(array("error" => "No groupings found for specified obj_type and field"));
        }

        return $this->sendOutput(array(
            "obj_type" => $objType,
            "field_name" => $fieldName,
            "groups" => $groupings->toArray()
        ));
    }

    /**
     * Get all the entity defintions
     *
     */
    public function getAllDefinitionsAction()
    {
        // Get the service manager and current user
        $serviceManager = $this->account->getServiceManager();

        // Load the entity definition
        $definitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        $definitions = $definitionLoader->getAll();

        $ret = array();
        foreach ($definitions as $def) {
            $ret[] = $this->fillDefinitionArray($def);
        }

        if (sizeOf($ret) == 0) {
            return $this->sendOutput(array("Definitions could not be loaded"));
        }

        return $this->sendOutput($ret);
    }

    /**
     * Get the additional info (browser_mode, forms, views, default_view) for the object definition.
     *
     * @param EntityDefinition $def Definition of the object type
     *
     * @return array Object Type defintion with all the additional info of the object type
     */
    private function fillDefinitionArray(EntityDefinition $def)
    {
        $serviceManager = $this->account->getServiceManager();
        $user = $this->account->getUser();
        $daclLoader = $serviceManager->get(DaclLoaderFactory::class);

        $ret = $def->toArray();
        $ret["browser_mode"] = "table";

        // TODO: Get browser blank content

        // Get forms
        $entityForms = $serviceManager->get(FormsFactory::class);
        $ret['forms'] = $entityForms->getDeviceForms($def, $user);

        // Get views from browser view service
        $viewsService = $serviceManager->get(BrowserViewServiceFactory::class);
        $browserViews = $viewsService->getViewsForUser($def->getObjType(), $user);
        $ret['views'] = array();
        foreach ($browserViews as $view) {
            $ret['views'][] = $view->toArray();
        }

        // Return the default view
        $ret['default_view'] = $viewsService->getDefaultViewForUser($def->getObjType(), $user);

        // Add the currently applied DACL for this entity definition
        $defDacl = $daclLoader->getForEntityDefinition($def);
        $ret['applied_dacl'] = $defDacl->toArray();

        return $ret;
    }

    /**
     * @param EntityInterface $entity
     * @param array $objData
     */
    private function savePendingObjectMultiObjects(EntityInterface $entity, array $objData)
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dataMapper = $this->account->getServiceManager()->get(DataMapperFactory::class);
        $fields = $entity->getDefinition()->getFields();

        // Flag that will determine if we should save the $entity
        $entityShouldUpdate = false;

        // Loop thru fields to check if we have objects waiting to be saved
        foreach ($fields as $field) {
            switch ($field->type) {
                case Field::TYPE_OBJECT:
                case Field::TYPE_OBJECT_MULTI:
                    // Check for the corresponding *_new object field
                    $waitingObjectFieldName = $field->name . "_new";

                    // Verify if this *_new field is existing in the object fields definition
                    $waitingObjectData = (isset($objData[$waitingObjectFieldName])) ? $objData[$waitingObjectFieldName] : null;

                    if ($field->subtype // Make sure that this field has a subtype
                        && is_array($waitingObjectData)
                    ) {
                        // Since we have found objects waiting to be saved, then we will loop thru the field's data
                        foreach ($waitingObjectData as $data) {
                            $waitingObjectEntity = $entityLoader->create($field->subtype);

                            // Specify the object reference for the awaiting entity to be saved
                            $data['obj_reference'] = $entity->getObjType() . ":" . $entity->getId();

                            // Parse the awaiting entity data
                            $waitingObjectEntity->fromArray($data);

                            // Save the awaiting entity object
                            if (!$dataMapper->save($waitingObjectEntity)) {
                                return $this->sendOutput(array("error" => "Error saving object reference " . $field->name . ": " . $dataMapper->getLastError()));
                            }

                            // Set the reference for the $entity
                            $entity->addMultiValue($field->name, $waitingObjectEntity->getId(), $waitingObjectEntity->getName());

                            // Lets flag this to true so $entity will be saved after the looping thru the fields
                            $entityShouldUpdate = true;
                        }
                    }
                    break;
            }
        }

        if ($entityShouldUpdate) {
            $dataMapper->save($entity);
        }
    }

    /**
     * PUT pass-through for update entity definition
     */
    public function putUpdateEntityDefAction()
    {
        return $this->postUpdateEntityDefAction();
    }

    /**
     * Updates the entity definition
     */
    public function postUpdateEntityDefAction()
    {
        $rawBody = $this->getRequest()->getBody();

        $ret = array();
        if (!$rawBody) {
            return $this->sendOutput(array("error" => "Request input is not valid"));
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['obj_type'])) {
            return $this->sendOutput(array("error" => "obj_type is a required param"));
        } elseif ($objData['obj_type'] === "") {
            return $this->sendOutput(array("error" => "obj_type is empty."));
        }

        // Get the service manager and current user
        $serviceManager = $this->account->getServiceManager();

        // Load the entity definition
        $defLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        $dataMapper = $serviceManager->get(EntityDefinitionDataMapperFactory::class);

        try {
            // If we dont have definition id, then we will create a new entity definition
            if (!$objData['id']) {
                $def = new EntityDefinition($objData['obj_type']);
            } else {
                $def = $defLoader->get($objData['obj_type']);
            }

            // Import the $objData into the entity definition
            $def->fromArray($objData);

            // Save the entity definition
            $dataMapper->save($def);   
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }

        // Build the new entity definition and return the result
        $ret = $this->fillDefinitionArray($def);
        return $this->sendOutput($ret);
    }

    /**
     * PUT pass-through for delete entity definition
     */
    public function putDeleteEntityDefAction()
    {
        return $this->postDeleteEntityDefAction();
    }

    /**
     * Deletes the entity definition
     */
    public function postDeleteEntityDefAction()
    {
        $rawBody = $this->getRequest()->getBody();

        $ret = array();
        if (!$rawBody) {
            return $this->sendOutput(array("error" => "Request input is not valid"));
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['obj_type'])) {
            return $this->sendOutput(array("error" => "obj_type is a required param"));
        }

        // Get the service manager and current user
        $serviceManager = $this->account->getServiceManager();

        // Load the entity definition
        $defLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        $dataMapper = $serviceManager->get(EntityDefinitionDataMapperFactory::class);

        try {
            $def = $defLoader->get($objData['obj_type']);
            
            // Delete the entity definition
            $dataMapper->delete($def);   
            return $this->sendOutput(true);
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }
    }

    /**
     * POST pass-through for mass edit
     */
    public function postMassEditAction()
    {
        return $this->getMassEditAction();
    }

    /**
     * Function that will handle the mass editing of entities
     *
     * @return {array} Returns the array of updated entities
     */
    public function getMassEditAction()
    {
        $ret = array();

        $rawBody = $this->getRequest()->getBody();

        if (!$rawBody) {
            return $this->sendOutput(array("error" => "Request input is not valid"));
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        // Check if we have obj_type. If it is not defined, then return an error
        if (!isset($objData['obj_type'])) {
            return $this->sendOutput(array("error" => "obj_type is a required param"));
        }

        // Check if we have id. If it is not defined, then return an error
        if (!isset($objData['id'])) {
            return $this->sendOutput(array("error" => "id is a required param"));
        }

        // Check if we have entity_data. If it is not defined, then return an error
        if (!isset($objData['entity_data'])) {
            return $this->sendOutput(array("error" => "entity_data is a required param"));
        }

        $entityData = $objData['entity_data'];

        // IDs can either be a single entry or an array
        $ids = $objData['id'];

        // Convert a single id to an array so we can handle them all the same way
        if (!is_array($ids) && $ids) {
            $ids = array($ids);
        }

        // Get the entity loader so we can initialize (and check the permissions for) each entity
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Get the datamapper
        $dataMapper = $this->account->getServiceManager()->get(DataMapperFactory::class);

        try {
            foreach ($ids as $id) {
                // Load the entity that we are going to update
                $entity = $entityLoader->get($objData['obj_type'], $id);
    
                // Update the fields with the data. Make sure we only update the provided fields.
                $entity->fromArray($entityData, true);
    
                // Save the entity
                $dataMapper->save($entity);
    
                // Return the entities that were updated
                $ret[] = $entity->toArray();
            }   
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }

        // Return what was edited
        return $this->sendOutput($ret);
    }

    /**
     * POST pass-through for merge entities
     */
    public function postMergeEntitiesAction()
    {
        return $this->getMergeEntitiesAction();
    }

    /**
     * Function that will handle the merging of entities
     *
     * @return {array} Returns the array of updated entities
     */
    public function getMergeEntitiesAction()
    {
        $rawBody = $this->getRequest()->getBody();

        if (!$rawBody) {
            return $this->sendOutput(array("error" => "Request input is not valid"));
        }

        // Decode the json structure
        $requestData = json_decode($rawBody, true);

        // Check if we have obj_type. If it is not defined, then return an error
        if (!isset($requestData['obj_type'])) {
            return $this->sendOutput(array("error" => "obj_type is a required param"));
        }

        // Check if we have entity_data. If it is not defined, then return an error
        if (!isset($requestData['merge_data'])) {
            return $this->sendOutput(array("error" => "merge_data is a required param"));
        }

        $mergeData = $requestData['merge_data'];

        // Get the entity loader so we can initialize (and check the permissions for) each entity
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Get the datamapper
        $dataMapper = $this->account->getServiceManager()->get(DataMapperFactory::class);

        // Create the new entity where we merge all field values
        $mergedEntity = $entityLoader->create($requestData['obj_type']);

        try {
            /*
            * Let's save the merged entity initially so we can get its entity id.
            * We will use the merged entity id as our moved object id when we loop thru the mergedData
            */
            $mergedEntityId = $dataMapper->save($mergedEntity);    
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }

        $entityData = array();

        try {
            /*
            * The merge data contains entity ids and the array of field names that will be used to merge the entities
            * After we load the entity using the entityId, then we will loop thru the field names
            *  and get its field values so we can assign it to the newly created merged entity ($mergedEntity)
            *
            * $mergeData = array (
            *  entityId => array(fieldName1, fieldName2, fieldName3)
            * )
            */
            foreach ($mergeData as $entityId => $fields) {
                $entity = $entityLoader->get($requestData['obj_type'], $entityId);

                // Build the entity data and get the field values from the entity we want to merge
                foreach ($fields as $field) {
                    $fieldValue = $entity->getValue($field);
                    $entityData[$field] = $fieldValue;

                    // Let's check if the field value is an array, then we need to get its value names
                    if (is_array($fieldValue)) {
                        $entityData["{$field}_fval"] = $entity->getValueNames($field);
                    }
                }

                $entityDef = $entity->getDefinition();

                // Now set the original entity id to point to the new merged entity so future requests to the old id will load the new entity
                $dataMapper->setEntityMovedTo($entityDef, $entityId, $mergedEntityId);

                // Let's flag the original entity as deleted
                $dataMapper->delete($entity);
            }

            // Set the fields with the merged data.
            $mergedEntity->fromArray($entityData, true);

            // Now save the the entity where all merged data are set
            $dataMapper->save($mergedEntity);    
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }

        // Return the merged entity
        return $this->sendOutput($mergedEntity->toArray());
    }

    /**
     * Function that will handle the saving of groups
     *
     * @return {object} Returnt the group that was added/updated
     */
    public function postSaveGroupAction()
    {
        $rawBody = $this->getRequest()->getBody();
        $ret = array();

        if (!$rawBody) {
            return $this->sendOutput(array("error" => "Request input is not valid"));
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['obj_type'])) {
            return $this->sendOutput(array("error" => "obj_type is a required param"));
        }

        if (!isset($objData['field_name'])) {
            return $this->sendOutput(array("error" => "field_name is a required param"));
        }

        if (!isset($objData['action'])) {
            return $this->sendOutput(array("error" => "action is a required param"));
        }

        // Get the entity loader that will be used to get the groupings model
        $groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);

        // Get the groupings for this obj_type and field_name
        $groupings = $this->getGroupings($groupingLoader, $objData['obj_type'], $objData['field_name']);

        // $objData['action'] will determine what type of action we will execute
        switch ($objData['action']) {
            case 'add':
                // Create a new instance of group and add it in the groupings
                $group = new Group();
                $groupings->add($group);

                // Set the group data
                $group->fromArray($objData);

                break;
            case 'edit':
                // $objData['id'] is the Group Id where we need to check it first before updating the group
                if (isset($objData['id']) && !empty($objData['id'])) {
                    $group = $groupings->getById($objData['id']);
                } else {
                    return $this->sendOutput(array("error" => "Edit action needs group id to update the group."));
                }

                // Set the group data
                $group->fromArray($objData);

                break;
            case 'delete':
                // $objData['id'] is the Group Id where we need to check it first before deleting the group
                if (isset($objData['id']) && !empty($objData['id'])) {
                    $group = $groupings->getById($objData['id']);
                } else {
                    return $this->sendOutput(array("error" => "Delete action needs group id to update the group."));
                }

                // Now flag the group as deleted
                $groupings->delete($objData['id']);
                break;
            default:
                return $this->sendOutput(array("error" => "No action made for entity group."));
        }

        try {
            // Save the changes made to the groupings
            $groupingLoader->save($groupings);    
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }

        return $this->sendOutput($group->toArray());
    }

    /**
     * Get the groupings model
     *
     * @param {GroupingLoader} $groupingLoader The entity loader that we will be using to get the entity definition
     * @param {string} $objType The object type where we will be getting the groups
     * @param {string} $fieldName The name of the field we are working with
     * @return EntityGroupings Returns the instance of EntityGroupings Model
     */
    private function getGroupings(GroupingLoader $groupingLoader, $objType, $fieldName)
    {
        try {
            // Get the entity defintion of the $objType
            $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
            $def = $defLoader->get($objType);

            // If this is a private object then set the user id of the current user
            $userId = null;
            if ($def->isPrivate) {
                $userId = $this->account->getUser()->getId();
            }
            
            // Get all groupings for this object type
            $groupings = $groupingLoader->get($objType, $fieldName, $userId);

            // Return the groupings object
            return $groupings;   
        } catch (\Exception $ex) {
            return $this->sendOutput(array("error" => $ex->getMessage()));
        }
    }

    /**
     * Function that will check if user is allowed to access the entity
     *
     * @param Entity $entity The entity that we will be checking
     * @param $permission The permission to check
     */
    private function checkIfUserIsAllowed(Entity $entity, $permission)
    {

        // Check entity permission
        $daclLoader = $this->account->getServiceManager()->get(DaclLoaderFactory::class);
        $dacl = $daclLoader->getForEntity($entity);
        $user = $this->account->getUser();
        $isOwner = ($entity->getOwnerId() == $user->getId());

        return $dacl->isAllowed($user, $permission, $isOwner);
    }
}
