<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Authentication\AuthenticationService;
use Netric\EntityQuery\EntityQuery;
use Netric\Permissions\DaclLoader;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Stats\StatsPublisher;

/**
 * This is just a simple test controller
 */
class EntityQueryController extends AbstractFactoriedController implements ControllerInterface
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
     * Index to query entities
     */
    private IndexInterface $entityIndex;

    /**
     * Handles the loading and saving of dacl permissions
     */
    private DaclLoader $daclLoader;

    /**
     * Initialize controller and all dependencies
     *
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param DaclLoader $this->daclLoader Handles the loading and saving of dacl permissions
     * @param IndexInterface $entityIndex Index to query entities
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        DaclLoader $daclLoader,
        IndexInterface $entityIndex
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->daclLoader = $daclLoader;
        $this->entityIndex = $entityIndex;
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
     * Execute a query
     *
     * @param HttpRequest $request Request object for this run
     * @return HttpResponse
     */
    public function postExecuteAction(HttpRequest $request): HttpResponse
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

        $currentUser = $currentAccount->getAuthenticatedUser();
        $accountId = $currentAccount->getAccountId();
        $query = new EntityQuery($objData["obj_type"], $accountId, $currentUser->getEntityId());
        $query->fromArray($objData);


        // Execute the query
        try {
            $res = $this->entityIndex->executeQuery($query);
        } catch (\Exception $ex) {
            // // Log the error so we can setup some alerts
            // $this->getApplication()->getLog()->error(
            //     "EntityQueryController: Failed API Query - " . $ex->getMessage()
            // );

            $response->setReturnCode(HttpResponse::STATUS_CODE_BAD_REQUEST);
            $response->write(["error" => $ex->getMessage(), "query_ran" => $query->toArray()]);
            return $response;
        }

        // Pagination
        $ret["total_num"] = $res->getTotalNum();
        $ret["offset"] = $res->getOffset();
        $ret["limit"] = $query->getLimit();
        $ret["num"] = $res->getNum();
        $ret["query_ran"] = $query->toArray();
        $ret["account"] = $currentAccount->getName();

        // Set results
        $entities = [];
        for ($i = 0; $i < $res->getNum(); $i++) {
            $ent = $res->getEntity($i);
            $dacl = $this->daclLoader->getForEntity($ent, $currentUser);
            $currentUserPermissions = $dacl->getUserPermissions($currentUser, $ent);

            // Always reset $entityData when loading the next entity
            $entityData = [];

            // Export the entity to array if the current user has access to view this entity
            if ($currentUserPermissions["view"]) {
                $entityData = $ent->toArrayWithApplied($currentUser);
                $entityData["applied_dacl"] = $dacl->toArray();
            } else {
                $entityData = $ent->toArrayWithNoPermissions();
            }

            // Applied/computed values
            $entityData["currentuser_permissions"] = $currentUserPermissions;

            // Print full details
            $entities[] = $entityData;
        }

        // Log stats
        StatsPublisher::increment("controller.entityquery.execute");

        $ret["entities"] = $entities;
        $response->write($ret);
        return $response;
    }
}
