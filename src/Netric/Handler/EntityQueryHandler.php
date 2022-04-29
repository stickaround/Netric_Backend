<?php

declare(strict_types=1);

namespace Netric\Handler;

use InvalidArgumentException;
use Netric\Account\AccountContainerInterface;
use Netric\Entity\EntityLoader;
use Netric\Permissions\DaclLoader;
use NetricApi\InvalidArgument;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Stats\StatsPublisher;
use NetricApi\EntityQueryIf;
use NetricApi\ErrorException;
use Netric\EntityQuery\EntityQuery;

class EntityQueryHandler implements EntityQueryIf
{
    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;


    /**
     * Index to query entities
     */
    private IndexInterface $entityIndex;

    /**
     * Handles the loading and saving of dacl permissions
     */
    private DaclLoader $daclLoader;

    /**
     * Get entities (like user) by ID
     *
     * @var EntityLoader
     */
    private EntityLoader $entityLoader;

    /**
     * Initialize controller and all dependencies
     *
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param DaclLoader $this->daclLoader Handles the loading and saving of dacl permissions
     * @param IndexInterface $entityIndex Index to query entities
     * @param EntityLoader $entityLoader
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        DaclLoader $daclLoader,
        IndexInterface $entityIndex,
        EntityLoader $entityLoader
    ) {
        $this->accountContainer = $accountContainer;
        $this->daclLoader = $daclLoader;
        $this->entityIndex = $entityIndex;
        $this->entityLoader = $entityLoader;
    }

    /**
     * Run a query and return the results
     *
     * @param string $userId
     * @param string $accountId
     * @param string $jsonQuery
     * @return string JSON results
     */
    public function execute($userId, $accountId, $jsonQuery): string
    {
        $objData = json_decode($jsonQuery, true);

        if (!isset($objData['obj_type'])) {
            // TODO: throw InvalidArgument
        }

        // Get the account and user
        $account = $this->accountContainer->loadById($accountId);
        $user = $this->entityLoader->getEntityById($userId, $accountId);

        if (!$account || !$user) {
            throw new InvalidArgumentException("accountId or userId are not valid");
        }

        // Construct the query
        $query = new EntityQuery($objData["obj_type"], $accountId, $userId);
        $query->fromArray($objData);

        // Execute the query
        try {
            $res = $this->entityIndex->executeQuery($query);
        } catch (\Exception $ex) {
            // // Log the error so we can setup some alerts
            // $this->getApplication()->getLog()->error(
            //     "EntityQueryController: Failed API Query - " . $ex->getMessage()
            // );

            throw new ErrorException($ex->getMessage());
        }

        $ret["total_num"] = $res->getTotalNum();
        $ret["offset"] = $res->getOffset();
        $ret["limit"] = $query->getLimit();
        $ret["num"] = $res->getNum();
        $ret["query_ran"] = $query->toArray();
        $ret["account"] = $account->getName();

        // Set results
        $entities = [];
        for ($i = 0; $i < $res->getNum(); $i++) {
            $ent = $res->getEntity($i);
            $dacl = $this->daclLoader->getForEntity($ent, $user);
            $userPermissions = $dacl->getUserPermissions($user, $ent);

            // Always reset $entityData when loading the next entity
            $entityData = [];

            // Export the entity to array if the current user has access to view this entity
            if ($userPermissions["view"]) {
                $entityData = $ent->toArrayWithApplied($user);
                $entityData["applied_dacl"] = $dacl->toArray();
            } else {
                $entityData = $ent->toArrayWithNoPermissions();
            }

            // Applied/computed values
            $entityData["currentuser_permissions"] = $userPermissions;

            // Print full details
            $entities[] = $entityData;
        }

        $ret["entities"] = $entities;

        // Log stats
        StatsPublisher::increment("thrift,handler=entityquery,function=execute");

        // Hand the results off to the client
        return json_encode($ret);
    }
}
