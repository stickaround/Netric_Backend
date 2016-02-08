<?php
/**
 * This is just a simple test controller
 */
namespace Netric\Controller;

use Netric\Entity\EntityInterface;
use \Netric\Mvc;
use \Netric\EntityDefinition;

class EntityController extends Mvc\AbstractController
{
	public function getTestAction($params=array())
	{
        return $this->sendOutput("test");
	}

	/**
	 * Get the definition (metadata) of an entity
	 */
	public function getGetDefinitionAction()
	{
        $params = $this->getRequest()->getParams();
		if (!$params['obj_type'])
		{
			return $this->sendOutput(array("error"=>"obj_type is a required param"));
		}

		// Get the service manager and current user
		$serviceManager = $this->account->getServiceManager();

		// Load the entity definition
		$defLoader = $serviceManager->get("EntityDefinitionLoader");
		$def = $defLoader->get($params['obj_type']);
		if (!$def) 
		{
			return $this->sendOutput(array("error"=>$params['obj_type'] . " could not be loaded"));
		}

        $ret = $this->fillDefinitionArray($def);

        return $this->sendOutput($ret);
	}

	/**
	 * Query entities
	 */
	public function postQueryAction()
	{
        $ret = array();
        $params = $this->getRequest()->getParams();
        
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
        $ret["limit"] = $query->getLimit();

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

    /**
     * GET pass-through for query
     */
    public function getQueryAction()
    {
        return $this->postQueryAction();
    }

    /**
     * Retrieve a single entity
     */
    public function getGetAction()
    {
        $ret = array();
        $params = $this->getRequest()->getParams();

        if (!$params['obj_type'] || !$params['id'])
        {
            return $this->sendOutput(array("error"=>"obj_type and id are required params"));
        }


        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $entity = $loader->get($params['obj_type'], $params['id']);

        // TODO: Check permissions

        $ret = $entity->toArray();
        
        // Check for definition (request may be made by client)
        if (isset($params['loadDef']))
        {
            // TODO: add $ret['definition'] with results from $this->getDefinition
        }


        return $this->sendOutput($ret);
    }

    /**
     * Save an entity
     */
    public function postSaveAction()
    {
        $rawBody = $this->getRequest()->getBody();
        $ret = array();
        if (!$rawBody)
        {
            return $this->sendOutput(array("error"=>"Request input is not valid"));
        }

        // Decode the json structure
        $objData = json_decode($rawBody, true);

        if (!isset($objData['obj_type']))
        {
            return $this->sendOutput(array("error"=>"obj_type is a required param"));
        }

        $loader = $this->account->getServiceManager()->get("EntityLoader");

        if (isset($objData['id']))
        {
            $entity = $loader->get($objData['obj_type'], $objData['id']);
        }
        else
        {
            $entity = $loader->create($objData['obj_type']);
        }

        // Parse the params
        $entity->fromArray($objData);

        // Save the entity
        $dataMapper = $this->account->getServiceManager()->get("Entity_DataMapper");
        if ($dataMapper->save($entity))
        {
            // Check to see if any new object_multi objects were sent awaiting save
            $this->savePendingObjectMultiObjects($entity, $objData);

            // Return the saved entity
            return $this->sendOutput($entity->toArray());
        }
        else
        {
            return $this->sendOutput(array("error"=>"Error saving: " . $dataMapper->getLastError()));
        }
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

        // Convert a single id to an array so we can handle them all the same way
        if (!is_array($ids) && $ids)
        {
            $ids = array($ids);
        }

        if (!$objType)
        {
            return $this->sendOutput(array("error"=>"obj_type is a required param"));
        }

        // Get the entity loader so we can initialize (and check the permissions for) each entity
        $loader = $this->account->getServiceManager()->get("EntityLoader");

        // Get the datamapper to delete
        $dataMapper = $this->account->getServiceManager()->get("Entity_DataMapper");

        foreach ($ids as $did)
        {
            $entity = $loader->get($objType, $did);
            if ($dataMapper->delete($entity))
            {
                $ret[] = $did;
            }
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
     * Get groupings for an object
     */
    public function getGetGroupingsAction()
    {
        $objType = $this->request->getParam("obj_type");
        $fieldName = $this->request->getParam("field_name");
        $filterString = $this->request->getParam("filter");

        if (!$objType || !$fieldName)
        {
            return $this->sendOutput(array("error"=>"obj_type & field_name are required params"));
        }

        // If filter was passed then decode it as an array
        $filterArray = ($filterString) ? json_decode($filterString) : array();

        // Get the service manager and current user
        $loader = $this->account->getServiceManager()->get("EntityGroupings_Loader");

        // If this is a private object then send the current user as a filter
        $def = $this->account->getServiceManager()->get("EntityDefinitionLoader")->get($objType);
        if ($def->isPrivate && !count($filterArray))
        {
            $filterArray['user_id'] = $this->account->getUser()->getId();
        }
        
        // Get all groupings from the loader
        $groups = $loader->get($objType, $fieldName, $filterArray);

        if ($groups)
        {
            return $this->sendOutput(array(
                "obj_type" => $objType,
                "field_name" => $fieldName,
                "filter" => $filterArray,
                "groups"=> $groups->toArray()
            ));
        }
        else
        {
            return $this->sendOutput(array("error"=>"No groupings found for specified obj_type and field"));
        }
    }

    /**
     * Get all the entity defintions
     *
     */
    public function getDefinitions()
    {
        // Get the service manager and current user
        $serviceManager = $this->account->getServiceManager();

        // Load the entity definition
        $loader = $serviceManager->get("EntityDefinitionLoader");
        $definitions = $loader->getAll();

        $ret = array();
        foreach($definitions as $def) {
            $ret[] = $this->fillDefinitionArray($def);
        }

        if (sizeOf($ret) == 0)
        {
            return $this->sendOutput(array("Definitions could not be loaded"));
        }

        return $this->sendOutput($ret);
    }

    /**
     * Get the additional info (browser_mode, forms, views, default_view) for the object definition.
     *
     * @param Netric\EntityDefinition $def Definition of the object type
     *
     * @return array Object Type defintion with all the additional info of the object type
     */
    private function fillDefinitionArray(EntityDefinition $def)
    {
        $serviceManager = $this->account->getServiceManager();
        $user = $this->account->getUser();

        $ret = $def->toArray();

        // TODO: Get browser mode preference (Netric/Entity/ObjectType/User has no getSetting)
        /*
        $browserMode = $user->getSetting("/objects/browse/mode/" . $params['obj_type']);
        // Set default view modes
        if (!$browserMode)
        {
            switch ($params['obj_type'])
            {
            case 'email_thread':
            case 'note':
                $browserMode = "previewV";
                break;
            default:
                $browserMode = "table";
                break;
            }
        }
        $ret["browser_mode"] = $browserMode;
        */
        $ret["browser_mode"] = "table";

        // TODO: Get browser blank content

        // Get forms
        $entityForms = $serviceManager->get("Netric/Entity/Forms");
        $ret['forms'] = $entityForms->getDeviceForms($def, $user);

        // Get views from browser view service
        $viewsService = $serviceManager->get("Netric/Entity/BrowserView/BrowserViewService");
        $browserViews = $viewsService->getViewsForUser($def->getObjType(), $user);
        $ret['views'] = array();
        foreach ($browserViews as $view)
        {
            $ret['views'][] = $view->toArray();
        }

        // Return the default view
        $ret['default_view'] = $viewsService->getDefaultViewForUser($def->getObjType(), $user);

        return $ret;
    }

    /**
     * @param EntityInterface $entity
     * @param array $objData
     */
    private function savePendingObjectMultiObjects(EntityInterface $entity, array $objData)
    {
        /*
         * TODO: handle new objects
         * loop through each field looking type=object or object_multi with a subtype
         * and check if there is a corresponding *_new array of objects waiting
         * to be saved. We will need to check for a property called obj_reference
         * and automatically set it to $entity.
         * Once this is done be sure to update the fields in $entity to add the newly created
         * references, then save the entity before returning.
         */
    }
}
