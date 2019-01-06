<?php
namespace Netric\Controller;

use Netric\Mvc;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Permissions\DaclLoaderFactory;

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
            return $this->sendOutput(array(
                "error" => "Request input is not valid. Must post a raw body with JSON defining the query."
            ));
        }

        $params = json_decode($rawBody, true);
        $ret = [];

        if (!isset($params["obj_type"])) {
            return $this->sendOutput(array("error" => "obj_type must be set"));
        }

        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        $daclLoader = $this->account->getServiceManager()->get(DaclLoaderFactory::class);

        $query = new \Netric\EntityQuery($params["obj_type"]);
        $query->fromArray($params);

        // Execute the query
        try {
            $res = $index->executeQuery($query);
        } catch (\Exception $ex) {
            $this->sendOutput([
                'error' => $ex->getMessage(),
                'query_ran' => $query->toArray()
            ]);

            // Log the error so we can setup some alerts
            $this->getApplication()->getLog()->error(
                "EntityQueryController: Failed API Query - " . $ex->getMessage()
            );
        }

        // Pagination
        // ---------------------------------------------
        $ret["total_num"] = $res->getTotalNum();
        $ret["offset"] = $res->getOffset();
        $ret["limit"] = $query->getLimit();
        $ret['num'] = $res->getNum();
        $ret['query_ran'] = $query->toArray();
        $ret['account'] = $this->getApplication()->getAccount()->getName();

        // Set results
        $entities = array();
        for ($i = 0; $i < $res->getNum(); $i++) {
            $ent = $res->getEntity($i);

            $entityData = $ent->toArray();
            $dacl = $daclLoader->getForEntity($ent);
            $entityData["applied_dacl"] = $dacl->toArray();

            // Print full details
            $entities[] = $entityData;
        }
        $ret["entities"] = $entities;

        return $this->sendOutput($ret);
    }
}
