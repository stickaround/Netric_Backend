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
use Netric\Db\Relational\RelationalDbContainerInterface;
use Netric\Db\Relational\RelationalDbContainer;
use Netric\Db\Relational\RelationalDbInterface;
use Ramsey\Uuid\Uuid;

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
     * Database container
     */
    private RelationalDbContainerInterface $databaseContainer;

    /**
     * Handles the loading and saving of dacl permissions
     */
    private DaclLoader $daclLoader;

    /**
     * Initialize controller and all dependencies
     *
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param EntityLoader $this->entityLoader Handles the loading and saving of entities
     * @param EntityDefinitionLoader $entityDefinitionLoader Handles the loading and saving of entity definition
     * @param GroupingLoader $this->groupingLoader Handles the loading and saving of groupings
     * @param BrowserViewService $browserViewService Manages the entity browser views
     * @param Forms $forms Manages the entity forms
     * @param DaclLoader $this->daclLoader Handles the loading and saving of dacl permissions
     * @param RelationalDbContainer $dbContainer Handles the database actions     
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        EntityLoader $entityLoader,
        EntityDefinitionLoader $entityDefinitionLoader,
        GroupingLoader $groupingLoader,
        BrowserViewService $browserViewService,
        Forms $forms,
        DaclLoader $daclLoader,
        RelationalDbContainer $dbContainer     
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->entityLoader = $entityLoader;
        $this->entityDefinitionLoader = $entityDefinitionLoader;
        $this->groupingLoader = $groupingLoader;
        $this->browserViewService = $browserViewService;
        $this->forms = $forms;        
        $this->daclLoader = $daclLoader;
        $this->databaseContainer = $dbContainer;
    }

    /**
     * Get active database handle
     *
     * @param string $accountId The account being acted on
     * @return RelationalDbInterface
     */
    private function getDatabase(string $accountId): RelationalDbInterface
    {
        return $this->databaseContainer->getDbHandleForAccountId($accountId);
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
     */
    public function getGetDefinitionAction(HttpRequest $request): HttpResponse
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

        if (!$objData['obj_type']) {
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
                $def = $this->entityDefinitionLoader->get($objData['obj_type'], $currentAccount->getAccountId());
            }
            
            if (!$def) {
                $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
                $response->write(["error" => "{$objData['obj_type']} could not be loaded."]);
                return $response;                
            }

            $response->write($this->fillDefinitionArray($def));
            return $response;            
        } catch (\Exception $ex) {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => $ex->getMessage()]);
        }
    }

    /**
     * Query entities
     */
    public function postQueryAction()
    {
        $ret = [];
        $params = $this->getRequest()->getParams();

        if (!isset($params["obj_type"])) {
            return $this->sendOutput(["error" => "obj_type must be set"]);
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
        }

        $accountId = $currentAccount->getAccountId();
        $user = $currentAccount->getAuthenticatedUser();
        $query = new EntityQuery($params["obj_type"], $accountId, $user->getEntityId());

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
            $res = $this->getDatabase($accountId)->query($query);
        } catch (\Exception $ex) {
            return $this->sendOutput(["error" => $ex->getMessage()]);
        }

        // Pagination
        // ---------------------------------------------
        $ret["total_num"] = $res->getTotalNum();
        $ret["offset"] = $res->getOffset();
        $ret["limit"] = $query->getLimit();

        // Set results
        $entities = [];
        for ($i = 0; $i < $res->getNum(); $i++) {
            $ent = $res->getEntity($i);

            // Put the current DACL in a special field to keep it from being overwritten when the entity is saved
            $dacl = $this->daclLoader->getForEntity($ent, $user);
            $currentUserPermissions = $dacl->getUserPermissions($user, $ent);

            // Always reset $entityData when loading the next entity
            $entityData = [];

            // Export the entity to array if the current user has access to view this entity
            if ($currentUserPermissions['view']) {
                $entityData = $ent->toArray();
                $entityData["applied_dacl"] = $dacl->toArray();
            } else {
                $entityData['entity_id'] = $ent->getEntityId();
                $entityData['name'] = $ent->getName();
            }

            $entityData['currentuser_permissions'] = $currentUserPermissions;

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
    public function postGetAction(HttpRequest $request)
    {
        return $this->getGetAction($request);
    }

    /**
     * Retrieve a single entity
     */
    public function getGetAction(HttpRequest $request): HttpResponse
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

        
        if ($rawBody) {            
            $params['obj_type'] = (isset($objData['obj_type'])) ? $objData['obj_type'] : null;
            $params['entity_id'] = (isset($objData['entity_id'])) ? $objData['entity_id'] : null;
            $params['uname'] = (isset($objData['uname'])) ? $objData['uname'] : null;
            $params['uname_conditions'] = (isset($objData['uname_conditions'])) ? $objData['uname_conditions'] : [];
        }

        // Use id for backwards compatibility
        if (empty($params['entity_id']) && !empty($params['id'])) {
            $params['entity_id'] = $params['id'];
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
        if (!empty($params['entity_id']) && Uuid::isValid($params['entity_id'])) {
            // Retrieve the entity by id
            $entity = $this->entityLoader->getEntityById($params['entity_id'], $currentAccount->getAccountId());
        } elseif (!empty($params['uname']) && !empty($params['obj_type'])) {
            // Retrieve the entity by a unique name and optional condition
            $entity = $this->entityLoader->getByUniqueName(
                $params['obj_type'],
                $params['uname'],
                $currentAccount->getAccountId(),
                $params['uname_conditions']
            );
        } else {
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
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
            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
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
     */
    public function postSaveAction()
    {
        $rawBody = $this->getRequest()->getBody();
        if (!$rawBody) {
            return $this->sendOutput(["error" => "Request input is not valid"]);
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['obj_type'])) {
            return $this->sendOutput(["error" => "obj_type is a required param"]);
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
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
                return $this->sendOutput(
                    [
                        "error" => "No entity found.",
                        "entity_id" => $objData['entity_id'],
                        "params" => $params
                    ]
                );
            }

            // Make sure that the user has a permission to save this entity
            if ($entity->getEntityId() && !$this->checkIfUserIsAllowed($entity, Dacl::PERM_EDIT)) {
                return $this->sendOutput(
                    [
                        "error" => "You do not have permission to edit this.",
                        "entity_id" => $entity->getEntityId(),
                        "params" => $params
                    ]
                );
            }
        } catch (\Exception $ex) {
            return $this->sendOutput(["error" => $ex->getMessage()]);
        }

        // Parse the params
        $entity->fromArray($objData);

        // Save the entity        
        $currentUser = $currentAccount->getAuthenticatedUser();

        try {            
            if (!$this->entityLoader->save($entity, $currentUser)) {
                return $this->sendOutput(["error" => "Error saving entity.", "data" => $this->entityLoader->toArray()]);
            }
        } catch (\RuntimeException $ex) {
            return $this->sendOutput(["error" => "Error saving: " . $ex->getMessage()]);
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
        $ret = [];
        // objType is a required to determine what exactly we are deleting
        $objType = $this->request->getParam("obj_type");
        // IDs can either be a single entry or an array
        $ids = $this->request->getParam("entity_id");

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
            $ids = [$ids];
        }

        if (!$objType) {
            return $this->sendOutput(["error" => "obj_type is a required param"]);
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
        }

        try {
            foreach ($ids as $did) {
                $entity = $this->entityLoader->getEntityById($did, $currentAccount->getAccountId());

                // Check first if we have permission to delete this entity
                if ($entity && $this->checkIfUserIsAllowed($entity, Dacl::PERM_DELETE)) {
                    // Proceed with the deleting this entity
                    if ($this->entityLoader->delete($entity, $currentAccount->getAuthenticatedUser())) {
                        $ret[] = $did;
                    }
                } else {
                    return $this->sendOutput(
                        ["error" => 'You do not have permissions to delete this entity: ' . $entity->getEntityId()]
                    );
                }
            }
        } catch (\RuntimeException $ex) {
            return $this->sendOutput(["error" => $ex->getMessage()]);
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
            return $this->sendOutput(["error" => "obj_type & field_name are required params"]);
        }

        // Get the groupings for this $objType and $fieldName
        try {
            $groupings = $this->getGroupings($this->groupingLoader, $objType, $fieldName);
        } catch (\Exception $ex) {
            return $this->sendOutput(["error" => $ex->getMessage()]);
        }

        if (!$groupings) {
            return $this->sendOutput(["error" => "No groupings found for specified obj_type and field"]);
        }

        return $this->sendOutput([
            "obj_type" => $objType,
            "field_name" => $fieldName,
            "groups" => $groupings->toArray()
        ]);
    }

    /**
     * Get all the entity defintions
     *
     */
    public function getAllDefinitionsAction()
    {
        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
        }

        // Load the entity definition        
        $definitions = $this->definitionLoader->getAll($currentAccount->getAccountId());

        $ret = [];
        foreach ($definitions as $def) {
            $ret[] = $this->fillDefinitionArray($def);
        }

        if (sizeOf($ret) == 0) {
            return $this->sendOutput(["Definitions could not be loaded"]);
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

        $ret = [];
        if (!$rawBody) {
            return $this->sendOutput(["error" => "Request input is not valid"]);
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['obj_type'])) {
            return $this->sendOutput(["error" => "obj_type is a required param"]);
        } elseif ($objData['obj_type'] === "") {
            return $this->sendOutput(["error" => "obj_type is empty."]);
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
        }

        // Load existing if it is there
        $def = $this->entityDefinitionLoader->get($objData['obj_type'], $currentAccount->getAccountId());

        if (!$def) {
            // If we are trying to edit an existing entity that could not be found, error out
            if ($objData['id'] || $objData['entity_definition_id']) {
                return $this->sendOutput(["error" => 'Definition not found']);
            }

            // Otherwise create a new definition object to update
            $def = new EntityDefinition($objData['obj_type'], $currentAccount->getAccountId());
        }

        // Import the $objData into the entity definition
        $def->fromArray($objData);

        // Save the entity definition
        $this->entityDefinitionLoader->save($def);

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

        $ret = [];
        if (!$rawBody) {
            return $this->sendOutput(["error" => "Request input is not valid"]);
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['obj_type'])) {
            return $this->sendOutput(["error" => "obj_type is a required param"]);
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
        }

        $def = $this->entityDefinitionLoader->get($objData['obj_type'], $currentAccount->getAccountId());

        if (!$def) {
            return $this->sendOutput(["error" => $objData['obj_type'] . ' could not be loaded']);
        }

        // Delete the entity definition
        $this->entityDefinitionLoader->delete($def);
        return $this->sendOutput(true);
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
        $ret = [];

        $rawBody = $this->getRequest()->getBody();

        if (!$rawBody) {
            return $this->sendOutput(["error" => "Request input is not valid"]);
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        // Check if we have id. If it is not defined, then return an error
        if (!isset($objData['entity_id'])) {
            return $this->sendOutput(["error" => "entity_id is a required param"]);
        }

        // Check if we have entity_data. If it is not defined, then return an error
        if (!isset($objData['entity_data'])) {
            return $this->sendOutput(["error" => "entity_data is a required param"]);
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
        }

        $entityData = $objData['entity_data'];

        // IDs can either be a single entry or an array
        $guids = $objData['entity_id'];

        // Convert a single id to an array so we can handle them all the same way
        if (!is_array($guids) && $guids) {
            $guids = [$guids];
        }

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
                    $ret["error"][] = "Invalid entity_id was provided during mass edit action: $guid.";
                }
            }
        } catch (\Exception $ex) {
            return $this->sendOutput(["error" => $ex->getMessage()]);
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
        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
        }
        
        $rawBody = $this->getRequest()->getBody();        

        if (!$rawBody) {
            return $this->sendOutput(["error" => "Request input is not valid"]);
        }

        // Decode the json structure
        $requestData = json_decode($rawBody, true);

        // Check if we have obj_type. If it is not defined, then return an error
        if (!isset($requestData['obj_type'])) {
            return $this->sendOutput(["error" => "obj_type is a required param"]);
        }

        // Check if we have entity_data. If it is not defined, then return an error
        if (!isset($requestData['merge_data'])) {
            return $this->sendOutput(["error" => "merge_data is a required param"]);
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
        }

        $mergeData = $requestData['merge_data'];

        // Create the new entity where we merge all field values
        $mergedEntity = $this->entityLoader->create($requestData['obj_type'], $currentAccount->getAccountId());

        try {
            /*
            * Let's save the merged entity initially so we can get its entity id.
            * We will use the merged entity id as our moved object id when we loop thru the mergedData
            */
            $mergedEntityId = $this->entityLoader->save($mergedEntity, $currentAccount->getAuthenticatedUser());
        } catch (\Exception $ex) {
            return $this->sendOutput(["error" => $ex->getMessage()]);
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
        } catch (\Exception $ex) {
            return $this->sendOutput(["error" => $ex->getMessage()]);
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
        $ret = [];

        if (!$rawBody) {
            return $this->sendOutput(["error" => "Request input is not valid"]);
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['obj_type'])) {
            return $this->sendOutput(["error" => "obj_type is a required param"]);
        }

        if (!isset($objData['field_name'])) {
            return $this->sendOutput(["error" => "field_name is a required param"]);
        }

        if (!isset($objData['action'])) {
            return $this->sendOutput(["error" => "action is a required param"]);
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
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
                    return $this->sendOutput(["error" => "Edit action needs group id to update the group."]);
                }

                // Set the group data
                $group->fromArray($objData);

                break;
            case 'delete':
                // $objData['group_id'] is the Group Id where we need to check it first before deleting the group
                if (isset($objData['group_id']) && !empty($objData['group_id'])) {
                    $group = $groupings->getByGuidOrGroupId($objData['group_id']);
                } else {
                    return $this->sendOutput(["error" => "Delete action needs group id to update the group."]);
                }

                // Now flag the group as deleted
                $groupings->delete($objData['group_id']);
                break;
            default:
                return $this->sendOutput(["error" => "No action made for entity group."]);
        }

        try {
            // Save the changes made to the groupings
            $this->groupingLoader->save($groupings);
        } catch (\Exception $ex) {
            return $this->sendOutput(["error" => $ex->getMessage()]);
        }

        return $this->sendOutput($group->toArray());
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
            if ($def->isPrivate) {
                $path .= "/" . $currentAccount->getAuthenticatedUser()->getEntityId();
            }

            // Get all groupings using a unique path
            $groupings = $this->groupingLoader->get($path, $currentAccount->getAccountId());

            // Return the groupings object
            return $groupings;
        } catch (\Exception $ex) {
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
     */
    public function getGetGroupByObjTypeAction()
    {
        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
        }

        $objType = $this->request->getParam("obj_type");
        $fieldName = $this->request->getParam("field_name");

        $def = $this->entityDefinitionLoader->get($objType, $currentAccount->getAccountId());
        $group = $this->groupingLoader->getGroupings($def, $fieldName);

        return $this->sendOutput($group->toArray());
    }

    /**
     * Update the sort order of the entities based on the entity's position in the array
     */
    public function postUpdateSortOrderEntitiesAction()
    {
        $rawBody = $this->getRequest()->getBody();
        if (!$rawBody) {
            return $this->sendOutput(["error" => "Request input is not valid"]);
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['entity_ids'])) {
            return $this->sendOutput(["error" => "entity_ids is a required param"]);
        }

        if (!is_array($objData['entity_ids'])) {
            return $this->sendOutput(["error" => "entity_ids should be an array"]);
        }

        // Make sure that we have an authenticated account
        $currentAccount = $this->getAuthenticatedAccount();
        if (!$currentAccount) {
            return $this->sendOutput(["error" => "Account authentication error."]);
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
                        return $this->sendOutput(["error" => "Error saving entity.", "data" => $this->entityLoader->toArray()]);
                    }

                    $updatedEntities[] = $entity->toArray();
                } catch (\RuntimeException $ex) {
                    return $this->sendOutput(["error" => "Error saving: " . $ex->getMessage()]);
                }
            }
        }

        // Return the updated entities
        return $this->sendOutput(array_reverse($updatedEntities));
    }
}
