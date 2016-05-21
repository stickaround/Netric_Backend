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
        $params = $this->getRequest()->getParams();
        $ret = array();
        
        if (!isset($params["obj_type"]))
            return $this->sendOutput(array("error"=>"obj_type must be set"));
        
        $index = $this->account->getServiceManager()->get("EntityQuery_Index");
        
        $query = new \Netric\EntityQuery($params["obj_type"]);

        if(isset($params['offset']))
            $query->setOffset($params['offset']);
            
        if(isset($params['limit']))
            $query->setLimit($params["limit"]);
        
        // Parse values passed from POST or GET params
        \Netric\EntityQuery\FormParser::buildQuery($query, $params);

        /*
        // Check for private
        if ($olist->obj->isPrivate())
        {
            if ($olist->obj->def->getField("owner"))
                $olist->fields("and", "owner", "is_equal", $this->user->id);
            if ($olist->obj->def->getField("owner_id"))
                $olist->addCondition("and", "owner_id", "is_equal", $this->user->id);
            if ($olist->obj->def->getField("user_id"))
                $olist->addCondition("and", "user_id", "is_equal", $this->user->id);
        }
        */

        // Execute the query
        $res = $index->executeQuery($query);
        
        // Pagination
        // ---------------------------------------------
        $ret["total_num"] = $res->getTotalNum();
        $ret["offset"] = $res->getOffset();
        $ret["limit"] = $res->getLimit();

        /*
         * This may no longer be needed with the new client
         * - Sky Stebnicki
        if ($res->getTotalNum() > $query->getLimit())
        {
            $prev = -1; // Hide

            // Get total number of pages
            $leftover = $res->getTotalNum() % $query->getLimit();
            
            if ($leftover)
                $numpages = (($res->getTotalNum() - $leftover) / $query->getLimit()) + 1; //($numpages - $leftover) + 1;
            else
                $numpages = $res->getTotalNum() / $query->getLimit();
            // Get current page
            if ($offset > 0)
            {
                $curr = $offset / $query->getLimit();
                $leftover = $offset % $query->getLimit();
                if ($leftover)
                    $curr = ($curr - $leftover) + 1;
                else 
                    $curr += 1;
            }
            else
                $curr = 1;
            // Get previous page
            if ($curr > 1)
                $prev = $offset - $query->getLimit();
            // Get next page
            if (($offset + $query->getLimit()) < $res->getTotalNum())
                $next = $offset + $query->getLimit();
            $pag_str = "Page $curr of $numpages";

            $ret['paginate'] = array();
            $ret['paginate']['nextPage'] = $next;
            $ret['paginate']['prevPage'] = $prev;
            $ret['paginate']['desc'] = $pag_str;
        }
        */
        
        // Set results
        $entities = array();
        for ($i = 0; $i < $res->getNum(); $i++)
        {
            $ent = $res->getEntity($i);
            
            if(isset($params['updatemode']) && $params['updatemode']) // Only get id and revision
			{
                // Return condensed results
                $entities[] = array(
                    "id" => $ent->getId(),
                    "revision" => $ent->getValue("revision"),
                    "num_comments" => $ent->getValue("num_comments"),
                );
			}
			else
			{
                // TODO: security
                
                // Print full details
                $entities[] = $ent->toArray();
            }
        }
        $ret["entities"] = $entities;
        
        return $this->sendOutput($ret);
	}
}