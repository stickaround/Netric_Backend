<?php

namespace Netric\Controller;

use Netric\Mvc;
use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Account\AccountContainerFactory;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Authentication\AuthenticationService;
use Netric\Entity\Entity;
use Netric\Entity\EntityLoader;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\FormParser;
use Netric\EntityGroupings\Group;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Entity\BrowserView\BrowserViewService;
use Netric\Entity\Forms;
use Netric\Permissions\Dacl;
use Netric\Permissions\DaclLoader;
use Ramsey\Uuid\Uuid;
use Exception;

/**
 * Controller for interacting with entities
 */
class EntityController extends AbstractFactoriedController implements ControllerInterface
{
    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Service used to get the current user/account
     */
    private AuthenticationService $authService;

    /**
     * Handles the loading and saving of entities
     */
    private EntityLoader $entityLoader;

    /**
     * Handles the loading and saving of entity definition
     */
    private EntityDefinitionLoader $entityDefinitionLoader;

    /**
     * Handles the loading and saving of groupings
     */
    private GroupingLoader $groupingLoader;

    /**
     * Manages the entity browser views
     */
    private BrowserViewService $browserViewService;

    /**
     * Manages the entity forms
     */
    private Forms $forms;

    /**
     * Handles the loading and saving of dacl permissions
     */
    private DaclLoader $daclLoader;

    /**
     * Initialize controller and all dependencies
     *
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param EntityLoader $this->entityLoader Handles the loading and saving of entities
     * @param EntityDefinitionLoader $entityDefinitionLoader Handles the loading and saving of entity definition
     * @param GroupingLoader $this->groupingLoader Handles the loading and saving of groupings
     * @param BrowserViewService $browserViewService Manages the entity browser views
     * @param Forms $forms Manages the entity forms
     * @param DaclLoader $this->daclLoader Handles the loading and saving of dacl permissions     
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        EntityLoader $entityLoader,
        EntityDefinitionLoader $entityDefinitionLoader,
        GroupingLoader $groupingLoader,
        BrowserViewService $browserViewService,
        Forms $forms,
        DaclLoader $daclLoader
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->entityLoader = $entityLoader;
        $this->entityDefinitionLoader = $entityDefinitionLoader;
        $this->groupingLoader = $groupingLoader;
        $this->browserViewService = $browserViewService;
        $this->forms = $forms;        
        $this->daclLoader = $daclLoader;
    }

    /**
     * Get the currently authenticated account
     *
     * @return Account
     */
    private function getAuthenticatedAccount()
    {
        $authIdentity = $this->authService->getIdentity();
        if (!$authIdentity) {
            return null;
        }

        return $this->accountContainer->loadById($authIdentity->getAccountId());
    }

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
     * 
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getGetDefinitionAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        $objType = $request->getParam('obj_type');

        if (!$objType) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "obj_type is a required param."]);
            return $response;
        }

        try {
            $def = null;            
            $currentAccount = $this->getAuthenticatedAccount();

            // Make sure that we have an authenticated account
            if ($currentAccount) {
                // Get the definition data for this object type
                $def = $this->entityDefinitionLoader->get($objType, $currentAccount->getAccountId());
            }
            
            if (!$def) {
                $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
                $response->write(["error" => "$objType could not be loaded."]);
                return $response;                
            }

            $response->write($this->fillDefinitionArray($def));
            return $response;
        } catch (Exception $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
        }
    }

    /**
     * Just in case they use POST
     */
    public function postGetAction(HttpRequest $request): HttpResponse
    {
        return $this->getGetAction($request);
    }

    /**
     * Retrieve a single entity
     * 
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getGetAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        $id = $request->getParam('id'); // id for backwards compatibility
        $entityId = $request->getParam('entity_id');
        $objType = $request->getParam('obj_type');        
        $uname = $request->getParam('uname');
        $unameConditions = $request->getParam('uname_conditions');

        // Use id for backwards compatibility
        if (!$entityId && $id) {
            $entityId = $id;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        // Get the entity utilizing whatever params were passed in
        $entity = null;
        if ($entityId && Uuid::isValid($entityId)) {
            // Retrieve the entity by id
            $entity = $this->entityLoader->getEntityById($entityId, $currentAccount->getAccountId());
        } elseif ($uname && $objType) {
            // Retrieve the entity by a unique name and optional condition
            $entity = $this->entityLoader->getByUniqueName(
                $objType,
                $uname,
                $currentAccount->getAccountId(),
                $unameConditions
            );
        } else {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => "entity_id or uname are required params."]);
            return $response;
        }        

        // Entity Could not be found - we might want to change this to a 404 status code
        if (!$entity) {
            $response->write([]);
            return $response;
        }

        // If user is not allowed, then return an error
        if (!$this->checkIfUserIsAllowed($entity, Dacl::PERM_VIEW)) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write([
                "error" => "You do not have permission to view this.",
                "entity_id" => $entity->getEntityId(),
                "params" => $params
            ]);
            return $response;
        }

        // Put the current DACL in a special field to keep it from being overwritten when the entity is saved
        $user = $currentAccount->getAuthenticatedUser();
        $dacl = $this->daclLoader->getForEntity($entity, $user);
        $currentUserPermissions = $dacl->getUserPermissions($user, $entity);

        // Export the entity to array if the current user has access to view this entity
        if ($currentUserPermissions['view']) {
            $entityData = $entity->toArray();
            $entityData["applied_dacl"] = $dacl->toArray();
        } else {
            $entityData['entity_id'] = $entity->getEntityId();
            $entityData['name'] = $entity->getName();
        }

        $entityData['currentuser_permissions'] = $currentUserPermissions;
        
        $response->write($entityData);
        return $response;
    }

    /**
     * Save an entity
     * 
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postSaveAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        if (!$rawBody) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("Request input is not valid");
            return $response;
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['obj_type'])) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "obj_type is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        try {
            // Create a new entity to save
            $entity = $this->entityLoader->create($objData['obj_type'], $currentAccount->getAccountId());

            // If editing an existing etity, then load it rather than using the new entity
            if (isset($objData['entity_id']) && !empty($objData['entity_id'])) {
                $entity = $this->entityLoader->getEntityById($objData['entity_id'], $currentAccount->getAccountId());
            }

            // If no entity is found, then return an error.
            if (!$entity) {                
                $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
                $response->write([
                    "error" => "No entity found.",
                    "entity_id" => $objData['entity_id']
                ]);
                return $response;
            }

            // Make sure that the user has a permission to save this entity
            if ($entity->getEntityId() && !$this->checkIfUserIsAllowed($entity, Dacl::PERM_EDIT)) {                
                $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
                $response->write([
                    "error" => "You do not have permission to edit this.",
                    "entity_id" => $entity->getEntityId()
                ]);
                return $response;
            }
        } catch (Exception $ex) {
            return $this->sendOutput(["error" => $ex->getMessage()]);
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => "Error saving entity."]);
            return $response;
        }

        // Parse the params
        $entity->fromArray($objData);

        // Save the entity        
        $currentUser = $currentAccount->getAuthenticatedUser();

        try {            
            if (!$this->entityLoader->save($entity, $currentUser)) {                
                $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
                $response->write(["error" => "Error saving entity."]);
                return $response;
            }
        } catch (\RuntimeException $ex) {            
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => "Error saving: " . $ex->getMessage()]);
            return $response;
        }

        // Check to see if any new object_multi objects were sent awaiting save
        $this->savePendingObjectMultiObjects($entity, $objData);

        $entityData = $entity->toArray();

        // Put the current DACL in a special field to keep it from being overwritten when the entity is saved        
        $dacl = $this->daclLoader->getForEntity($entity, $currentAccount->getAuthenticatedUser());
        $currentUserPermissions = $dacl->getUserPermissions($currentAccount->getAuthenticatedUser(), $entity);

        // Export the entity to array if the current user has access to view this entity
        if ($currentUserPermissions['view']) {
            $entityData = $entity->toArray();
            $entityData["applied_dacl"] = $dacl->toArray();
        } else {
            $entityData = [];
            $entityData['entity_id'] = $entity->getEntityId();
            $entityData['name'] = $entity->getName();
        }

        $entityData['currentuser_permissions'] = $currentUserPermissions;

        // Return the saved entity
        $response->write($entityData);
        return $response;
    }

    /**
     * PUT pass-through for save
     * 
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function putSaveAction(HttpRequest $request): HttpResponse
    {
        return $this->postSaveAction($request);
    }

    /**
     * Remove an entity (or a list of entities)
     * 
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postRemoveAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        if (!$rawBody) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("Request input is not valid");
            return $response;
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);
        if (!isset($objData['entity_id'])) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "entity_id is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        // IDs can either be a single entry or an array
        $ids = $objData['entity_id'];

        // Convert a single id to an array so we can handle them all the same way
        if (!is_array($ids) && $ids) {
            $ids = [$ids];
        }

        $ret = [];
        try {
            foreach ($ids as $entityId) {
                $entity = $this->entityLoader->getEntityById($entityId, $currentAccount->getAccountId());

                // Check first if we have permission to delete this entity
                if ($entity && $this->checkIfUserIsAllowed($entity, Dacl::PERM_DELETE)) {
                    // Proceed with the deleting this entity
                    if ($this->entityLoader->delete($entity, $currentAccount->getAuthenticatedUser())) {
                        $ret[] = $entityId;
                    }
                } else {
                    $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
                    $response->write(["error" => "You do not have permissions to delete this entity: " . $entity->getName()]);
                    return $response;
                }
            }
        } catch (\RuntimeException $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
            return $response;
        }

        // Return what was deleted
        $response->write($ret);
        return $response;
    }

    /**
     * Get groupings for an object
     * 
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getGetGroupingsAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        $objType = $request->getParam("obj_type");
        $fieldName = $request->getParam("field_name");

        if (!$objType || !$fieldName) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "obj_type & field_name are required params."]);
            return $response;
        }

        // Get the groupings for this $objType and $fieldName
        try {
            $groupings = $this->getGroupings($this->groupingLoader, $objType, $fieldName);
        } catch (Exception $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
            return $response;            
        }

        if (!$groupings) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "No groupings found for specified obj_type and field."]);
            return $response;
        }

        $response->write([
            "obj_type" => $objType,
            "field_name" => $fieldName,
            "groups" => $groupings->toArray()
        ]);
        return $response;
    }

    /**
     * Get all the entity defintions
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getAllDefinitionsAction(HttpRequest $request): HttpResponse
    {
        $response = new HttpResponse($request);

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        // Load all the entity definitions
        $definitions = $this->entityDefinitionLoader->getAll($currentAccount->getAccountId());

        $ret = [];
        foreach ($definitions as $def) {
            $ret[] = $this->fillDefinitionArray($def);
        }

        if (sizeOf($ret) == 0) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => "Definitions could not be loaded."]);
            return $response;
        }

        $response->write($ret);
        return $response;
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
        $currentAccount = $this->getAuthenticatedAccount();        
        $user = $currentAccount->getAuthenticatedUser();
        $ret = $def->toArray();
        $ret["browser_mode"] = "table";

        // TODO: Get browser blank content

        // Get forms        
        $ret['forms'] = $this->forms->getDeviceForms($def, $user);

        // Get views from browser view service        
        $browserViews = $this->browserViewService->getViewsForUser($def->getObjType(), $user);
        $ret['views'] = [];
        foreach ($browserViews as $view) {
            $ret['views'][] = $view->toArray();
        }

        // Return the default view
        $ret['default_view'] = $this->browserViewService->getDefaultViewForUser($def->getObjType(), $user);

        // Add the currently applied DACL for this entity definition
        $defDacl = $this->daclLoader->getForEntityDefinition($def);
        $ret['applied_dacl'] = $defDacl->toArray();

        return $ret;
    }

    /**
     * @param EntityInterface $entity
     * @param array $objData
     */
    private function savePendingObjectMultiObjects(EntityInterface $entity, array $objData)
    {
        $currentAccount = $this->getAuthenticatedAccount();
        $currentUser = $currentAccount->getAuthenticatedUser();
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

                    if (
                        $field->subtype // Make sure that this field has a subtype
                        && is_array($waitingObjectData)
                    ) {
                        // Since we have found objects waiting to be saved, then we will loop thru the field's data
                        foreach ($waitingObjectData as $data) {
                            $waitingObjectEntity = $this->entityLoader->create($field->subtype, $currentAccount->getAccountId());

                            // Specify the object reference for the awaiting entity to be saved
                            $data['obj_reference'] = $entity->getEntityId();

                            // Parse the awaiting entity data
                            $waitingObjectEntity->fromArray($data);

                            // Save the awaiting entity object
                            if (!$this->entityLoader->save($waitingObjectEntity, $currentUser)) {
                                return $this->sendOutput(["error" => "Error saving object reference " . $field->name]);
                            }

                            // Set the reference for the $entity
                            $entity->addMultiValue($field->name, $waitingObjectEntity->getEntityId(), $waitingObjectEntity->getName());

                            // Lets flag this to true so $entity will be saved after the looping thru the fields
                            $entityShouldUpdate = true;
                        }
                    }
                    break;
            }
        }

        if ($entityShouldUpdate) {
            $this->entityLoader->save($entity, $currentUser);
        }
    }

    /**
     * Updates the entity definition
     * 
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postUpdateEntityDefAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        if (!$rawBody) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("Request input is not valid");
            return $response;
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);
        if (!isset($objData['obj_type'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "obj_type is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);            
            return $response;
        }

        $objType = $objData['obj_type'];
        if (!$objType) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "obj_type should not be empty."]);
            return $response;
        }

        // Load existing if it is there
        $def = $this->entityDefinitionLoader->get($objType, $currentAccount->getAccountId());

        if (!$def) {
            // If we are trying to edit an existing entity that could not be found, error out
            if ($objData['id'] || $objData['entity_definition_id']) {
                $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
                $response->write(["error" => "Definition not found."]);
                return $response;
            }

            // Otherwise create a new definition object to update
            $def = new EntityDefinition($objType, $currentAccount->getAccountId());
        }

        // Import the $objData into the entity definition
        $def->fromArray($objData);

        // Save the entity definition
        $this->entityDefinitionLoader->save($def);

        // Build the new entity definition and return the result
        $response->write($this->fillDefinitionArray($def));
        return $response;
    }

    /**
     * Deletes the entity definition
     * 
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postDeleteEntityDefAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        if (!$rawBody) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("Request input is not valid");
            return $response;
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);
        if (!isset($objData['obj_type'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);            
            $response->write(["error" => "obj_type is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        $objType = $objData['obj_type'];
        $def = $this->entityDefinitionLoader->get($objType, $currentAccount->getAccountId());

        if (!$def) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "$objType could not be loaded."]);
            return $response;
        }

        // Try to delete the entity definition
        $result = false;
        try {
            $result = $this->entityDefinitionLoader->delete($def);
        } catch (\RuntimeException $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
            return $response;            
        }
        
        $response->write($result);
        return $response;
    }

    /**
     * Function that will handle the mass editing of entities
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postMassEditAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        if (!$rawBody) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("Request input is not valid");
            return $response;
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        // Check if we have id. If it is not defined, then return an error
        if (!isset($objData['entity_id'])) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "entity_id is a required param."]);
            return $response;
        }

        // Check if we have entity_data. If it is not defined, then return an error
        if (!isset($objData['entity_data'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "entity_data is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        // IDs can either be a single entry or an array
        $guids = $objData['entity_id'];
        $entityData = $objData['entity_data'];

        // Convert a single id to an array so we can handle them all the same way
        if (!is_array($guids) && $guids) {
            $guids = [$guids];
        }

        $ret = [];
        try {
            foreach ($guids as $guid) {
                if (Uuid::isValid($guid)) {
                    // Load the entity that we are going to update
                    $entity = $this->entityLoader->getEntityById($guid, $currentAccount->getAccountId());

                    // Update the fields with the data. Make sure we only update the provided fields.
                    $entity->fromArray($entityData, true);

                    // Save the entity
                    $this->entityLoader->save($entity, $currentAccount->getAuthenticatedUser());

                    // Return the entities that were updated
                    $ret[] = $entity->toArray();
                } else {
                    $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
                    $response->write(["error" => "Invalid entity_id was provided during mass edit action: $guid."]);
                    return $response;
                }
            }
        } catch (Exception $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
            return $response;
        }

        // Return what was edited
        $response->write($ret);
        return $response;
    }

    /**
     * Function that will handle the merging of entities
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postMergeEntitiesAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        if (!$rawBody) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("Request input is not valid");
            return $response;
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        // Check if we have obj_type. If it is not defined, then return an error
        if (!isset($objData['obj_type'])) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "obj_type is a required param."]);
            return $response;
        }

        // Check if we have entity_data. If it is not defined, then return an error
        if (!isset($objData['merge_data'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "merge_data is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        $mergeData = $objData['merge_data'];

        // Create the new entity where we merge all field values
        $mergedEntity = $this->entityLoader->create($objData['obj_type'], $currentAccount->getAccountId());

        try {
            /*
             * Let's save the merged entity initially so we can get its entity id.
             * We will use the merged entity id as our moved object id when we loop thru the mergedData
             */
            $mergedEntityId = $this->entityLoader->save($mergedEntity, $currentAccount->getAuthenticatedUser());
        } catch (Exception $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
            return $response;
        }

        $entityData = [];
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
                $entity = $this->entityLoader->getEntityById($entityId, $currentAccount->getAccountId());

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
                $this->entityLoader->setEntityMovedTo($entityId, $mergedEntityId, $identity->getAccountId());

                // Let's flag the original entity as deleted
                $this->entityLoader->archive($entity, $currentAccount->getAuthenticatedUser());
            }

            // Set the fields with the merged data.
            $mergedEntity->fromArray($entityData, true);

            // Now save the the entity where all merged data are set
            $this->entityLoader->save($mergedEntity, $currentAccount->getAuthenticatedUser());
        } catch (Exception $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
            return $response;
        }

        // Return the merged entity
        $response->write($mergedEntity->toArray());
        return $response;
    }

    /**
     * Function that will handle the saving of groups
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postSaveGroupAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        if (!$rawBody) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("Request input is not valid");
            return $response;
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['obj_type'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "obj_type is a required param."]);
            return $response;
        }

        if (!isset($objData['field_name'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "field_name is a required param."]);
            return $response;
        }

        if (!isset($objData['action'])) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "action is a required param."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        // Get the groupings for this obj_type and field_name
        $groupings = $this->getGroupings($this->groupingLoader, $objData['obj_type'], $objData['field_name']);

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
                if (isset($objData['group_id']) && !empty($objData['group_id'])) {
                    $group = $groupings->getByGuidOrGroupId($objData['group_id']);
                } else {
                    $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
                    $response->write(["error" => "Edit action needs group id to update the group."]);
                    return $response;
                }

                // Set the group data
                $group->fromArray($objData);
                break;
                
            case 'delete':
                // $objData['group_id'] is the Group Id where we need to check it first before deleting the group
                if (isset($objData['group_id']) && !empty($objData['group_id'])) {
                    $group = $groupings->getByGuidOrGroupId($objData['group_id']);
                } else {
                    $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
                    $response->write(["error" => "Delete action needs group id to update the group."]);
                    return $response;
                }

                // Now flag the group as deleted
                $groupings->delete($objData['group_id']);
                break;
            default:                
                $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
                $response->write(["error" => "No action made for entity group."]);
                return $response;
        }

        try {
            // Save the changes made to the groupings
            $this->groupingLoader->save($groupings);
        } catch (Exception $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
            return $response;
        }

        $response->write($group->toArray());
        return $response;
    }

    /**
     * Get the groupings model
     *
     * @param {GroupingLoader} $this->groupingLoader The entity loader that we will be using to get the entity definition
     * @param {string} $objType The object type where we will be getting the groups
     * @param {string} $fieldName The name of the field we are working with
     * @return EntityGroupings Returns the instance of EntityGroupings Model
     */
    private function getGroupings(GroupingLoader $groupingLoader, $objType, $fieldName)
    {        
        $currentAccount = $this->getAuthenticatedAccount();
        
        try {
            // Get the entity defintion of the $objType            
            $def = $this->entityDefinitionLoader->get($objType, $currentAccount->getAccountId());
            $path = "$objType/$fieldName";

            // If this is a private object then add the user entity_id in the unique path
            if ($def->isPrivate()) {
                $path .= "/" . $currentAccount->getAuthenticatedUser()->getEntityId();
            }

            // Get all groupings using a unique path
            $groupings = $this->groupingLoader->get($path, $currentAccount->getAccountId());

            // Return the groupings object
            return $groupings;
        } catch (Exception $ex) {
            return $this->sendOutput(["error" => $ex->getMessage()]);
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
        $currentAccount = $this->getAuthenticatedAccount();

        // Check entity permission        
        $dacl = $this->daclLoader->getForEntity($entity, $currentAccount->getAuthenticatedUser());

        return $dacl->isAllowed($currentAccount->getAuthenticatedUser(), $permission, $entity);
    }

    /**
     * Function that will get the groupings by path
     * 
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getGetGroupByObjTypeAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }

        $objType = $request->getParam('obj_type');
        $fieldName = $request->getParam('field_name');

        if (!$objType) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "obj_type is a required param."]);
            return $response;
        }

        if (!$fieldName) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "field_name is a required param."]);
            return $response;
        }

        try {
            $def = $this->entityDefinitionLoader->get($objType, $currentAccount->getAccountId());
            $grouping = $this->groupingLoader->getGroupings($def, $fieldName);
        } catch (Exception $ex) {
            $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
            $response->write(["error" => $ex->getMessage()]);
            return $response;
        }

        if (!$grouping) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "No grouping found for specified obj_type and field."]);
            return $response;
        }
        
        $response->write($grouping->toArray());
        return $response;
    }

    /**
     * Update the sort order of the entities based on the entity's position in the array
     * 
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postUpdateSortOrderEntitiesAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        if (!$rawBody) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write("Request input is not valid");
            return $response;
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);
        if (!isset($objData['entity_ids'])) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "entity_ids is a required param."]);
            return $response;
        }

        if (!is_array($objData['entity_ids'])) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "entity_ids should be an array."]);
            return $response;
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {            
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => "Account authentication error."]);
            return $response;
        }
        
        // We should reverse the order of entity_ids array so the top entity will have highest sort order
        $entityIds = array_reverse($objData['entity_ids']);
        $updatedEntities = [];
        $currentTime = mktime(date("h"), date("i"), date("s"), date("n"), date("j"), date("Y"));

        forEach($entityIds as $entityId)
        {
            // Load the entity using the entityId
            $entity = $this->entityLoader->getEntityById($entityId, $currentAccount->getAccountId());

            // Make sure that the entity exists before we update its sort order
            if ($entity) {                
                $entity->setValue('sort_order', $currentTime++);

                try {
                    if (!$this->entityLoader->save($entity, $currentAccount->getAuthenticatedUser())) {                        
                        $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
                        $response->write(["error" => "Error saving entity.", "data" => $entity->toArray()]);
                        return $response;
                    }

                    $updatedEntities[] = $entity->toArray();
                } catch (\RuntimeException $ex) {                    
                    $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
                    $response->write(["error" => "Error saving: " . $ex->getMessage()]);
                    return $response;
                }
            }
        }

        // Return the updated entities        
        $response->write(array_reverse($updatedEntities));
        return $response;
    }
}
