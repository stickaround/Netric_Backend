<?php
/**
 * This is just a simple test controller
 */
namespace Netric\Controller;

use \Netric\Mvc;

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
                "error" => "Request input is not valid. Must post a raw body with JSON defining the query"
            ));
        }

        $params = json_decode($rawBody, true);
        $ret = [];
        
        if (!isset($params["obj_type"])) {
            return $this->sendOutput(array("error" => "obj_type must be set"));
        }

        $index = $this->account->getServiceManager()->get("EntityQuery_Index");

        $query = new \Netric\EntityQuery($params["obj_type"]);
        $query->fromArray($params);

        // Execute the query
        $res = $index->executeQuery($query);

        // Pagination
        // ---------------------------------------------
        $ret["total_num"] = $res->getTotalNum();
        $ret["offset"] = $res->getOffset();
        $ret["limit"] = $query->getLimit();

        // Set results
        $entities = array();
        for ($i = 0; $i < $res->getNum(); $i++) {
            $ent = $res->getEntity($i);

            if (isset($params['updatemode']) && $params['updatemode']) // Only get id and revision
            {
                // Return condensed results
                $entities[] = array(
                    "id" => $ent->getId(),
                    "revision" => $ent->getValue("revision"),
                    "num_comments" => $ent->getValue("num_comments"),
                );
            } else {
                // TODO: security

                // Print full details
                $entities[] = $ent->toArray();
            }
        }
        $ret["entities"] = $entities;

        return $this->sendOutput($ret);
	}
}