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
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Permissions\Dacl;
use Netric\Permissions\Dacl\Entry;
use Netric\Permissions\DaclLoader;
use \RuntimeException;

/**
 * Controller for interaction with permission/security
 */
class PermissionController extends AbstractFactoriedController implements ControllerInterface
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
     * @param DaclLoader $this->daclLoader Handles the loading and saving of dacl permissions
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        EntityLoader $entityLoader,
        EntityDefinitionLoader $entityDefinitionLoader,
        GroupingLoader $groupingLoader,
        DaclLoader $daclLoader
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->entityLoader = $entityLoader;
        $this->entityDefinitionLoader = $entityDefinitionLoader;
        $this->groupingLoader = $groupingLoader;
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
     * Get the DACL data for entity
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function getGetDaclForEntityAction(HttpRequest $request): HttpResponse
    {
        $rawBody = $request->getBody();
        $response = new HttpResponse($request);

        $objType = $request->getParam('obj_type');
        $entityId = $request->getParam('entity_id');

        if (!$objType) {
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

        // Set the Dacl based on the obj_type provided in the params
        $def = $this->entityDefinitionLoader->get($objType, $currentAccount->getAccountId());
        $dacl = $this->daclLoader->getForEntityDefinition($def);

        // If id is set, then we will update the dacl and retrieve the entity by id
        if ($entityId) {
            $entity = $this->entityLoader->getEntityById($entityId, $currentAccount->getAccountId());
            $dacl = $this->daclLoader->getForEntity($entity, $currentAccount->getAuthenticatedUser());
        }

        $retData = $dacl->toArray();
        $retData["user_names"] = [];
        $retData["group_names"] = [];

        // Get the user details
        $users = $dacl->getUsers();
        foreach ($users as $userId) {
            $userEntity = $this->entityLoader->getEntityById($userId, $currentAccount->getAccountId());

            if ($userEntity) {
                $retData["user_names"][$userId] = $userEntity->getName();
            }
        }

        $userGroups = $this->groupingLoader->get(ObjectTypes::USER . "/groups", $currentAccount->getAccountId());
        $groups = $userGroups->toArray();

        // Get the group details
        foreach ($groups as $groupDetails) {
            $retData["group_names"][$groupDetails["group_id"]] = $groupDetails["name"];
        }

        $response->write($retData);
        return $response;
    }

    /**
     * Save the Dacl Entries
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postSaveDaclEntriesAction(HttpRequest $request): HttpResponse
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

        // Make sure we have the minimum required params
        if (empty($objData['obj_type'])) {
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

        // Retrieve the entity by id and return the result
        if (!empty($objData['entity_id'])) {
            $entity = $this->entityLoader->getEntityById($objData['entity_id'], $currentAccount->getAccountId());
            $dacl = $this->daclLoader->getForEntity($entity, $currentAccount->getAuthenticatedUser());
            $dacl->fromArray($objData);
            $entity->setValue("dacl", json_encode($dacl->toArray()));

            try {
                $this->entityLoader->save($entity, $currentAccount->getAuthenticatedUser());

                // After saving, prepare the dacl data and set the user_names and group_names
                $retData = $dacl->toArray();
                $retData["user_names"] = [];
                $retData["group_names"] = [];

                // Get the user details
                $users = $dacl->getUsers();
                foreach ($users as $userId) {
                    $userEntity = $this->entityLoader->getEntityById($userId, $currentAccount->getAccountId());

                    if ($userEntity) {
                        $retData["user_names"][$userId] = $userEntity->getName();
                    }
                }

                $userGroups = $this->groupingLoader->get(ObjectTypes::USER . "/groups", $currentAccount->getAccountId());
                $groups = $userGroups->toArray();

                // Get the group details
                foreach ($groups as $groupDetails) {
                    $retData["group_names"][$groupDetails["group_id"]] = $groupDetails["name"];
                }

                $response->write($retData);
                return $response;
            } catch (RuntimeException $ex) {
                $response->setReturnCode(HttpResponse::STATUS_INTERNAL_SERVER_ERROR);
                $response->write(["error" => "Error saving: " . $ex->getMessage()]);
                return $response;
            }
        }

        $def = $this->entityDefinitionLoader->get($objData['obj_type'], $currentAccount->getAccountId());
        $dacl = $this->daclLoader->getForEntityDefinition($def);
        $dacl->fromArray($objData);
        $def->setDacl($dacl);

        $response->write($dacl->toArray());
        return $response;
    }
}
