<?php

namespace Netric\Controller;

use Netric\Mvc;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Permissions\DaclLoaderFactory;
use Netric\EntityQuery\EntityQuery;

/**
 * This is just a simple test controller
 */
class EntityQueryController extends Mvc\AbstractAccountController
{
    /**
     * Execute a query
     *
     * @return Response
     */
    public function postExecuteAction()
    {
        $rawBody = $this->getRequest()->getBody();

        if (!$rawBody) {
            return $this->sendOutput([
                "error" => "Request input is not valid. Must post a raw body with JSON defining the query."
            ]);
        }

        $params = json_decode($rawBody, true);
        $ret = [];

        if (!isset($params["obj_type"])) {
            return $this->sendOutput(["error" => "obj_type must be set"]);
        }

        $user = $this->account->getUser();
        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        $daclLoader = $this->account->getServiceManager()->get(DaclLoaderFactory::class);

        $query = new EntityQuery($params["obj_type"], $this->account->getAccountId(), $user->getEntityId());
        $query->fromArray($params);

        // Execute the query
        try {
            $res = $index->executeQuery($query);
        } catch (\Exception $ex) {
            // Log the error so we can setup some alerts
            $this->getApplication()->getLog()->error(
                "EntityQueryController: Failed API Query - " . $ex->getMessage()
            );

            return $this->sendOutput([
                'error' => $ex->getMessage(),
                'query_ran' => $query->toArray()
            ]);
        }

        // Pagination
        // ---------------------------------------------
        $ret["total_num"] = $res->getTotalNum();
        $ret["offset"] = $res->getOffset();
        $ret["limit"] = $query->getLimit();
        $ret['num'] = $res->getNum();
        $ret['query_ran'] = $query->toArray();
        $ret['account'] = $this->account->getName();

        // Set results
        $entities = [];
        for ($i = 0; $i < $res->getNum(); $i++) {
            $ent = $res->getEntity($i);
            $dacl = $daclLoader->getForEntity($ent, $user);
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
}
