<?php
/**
 * This is the base class for all entity indexes
 */
namespace Netric\EntityQuery\Index;

abstract class IndexAbstract
{
    /**
     * Handle to current account
     * 
     * @var \Netric\Account
     */
    protected $account = null;

    /**
     * Entity factory used for instantiating new entities
     *
     * @var \Netric\Entity\EntityFactory
     */
    protected $entityFactory = null;
    
    /**
     * Setup this index for the given account
     * 
     * @param \Netric\Account $account
     */
    public function __construct(\Netric\Account $account)
    {
        $this->account = $account;
        $this->entityFactory = $account->getServiceManager()->get("EntityFactory");
        
        // Setup the index
        $this->setUp($account);
    }
    
    /**
     * Setup this index for the given account
     * 
     * @param \Netric\Account $account
     */
    abstract protected function setUp(\Netric\Account $account);
    
    /**
	 * Save an object to the index
	 *
     * @param \Netric\Entity $entity Entity to save
	 * @return bool true on success, false on failure
	 */
	abstract public function save(\Netric\Entity $entity);
    
    /**
	 * Delete an object from the index
	 *
     * @param string $id Unique id of object to delete
	 * @return bool true on success, false on failure
	 */
	abstract public function delete($id);
    
    /**
	 * Execute a query and return the results
	 *
     * @param string $id Unique id of object to delete
	 * @return \Netric\EntityQuery\Results
	 */
	abstract public function executeQuery(\Netric\EntityQuery &$query);
    
    /**
	 * Split a full text string into an array of terms
	 *
	 * @param string $qstring The entered text
	 * @return array Array of terms
	 */
	public function queryStringToTerms($qstring)
	{
		if (!$qstring)
			return array();

		$res = array();
		//preg_match_all('/(?<!")\b\w+\b|\@(?<=")\b[^"]+/', $qstr, $res, PREG_PATTERN_ORDER);
		preg_match_all('~(?|"([^"]+)"|(\S+))~', $qstring, $res);
		return $res[0]; // not sure why but for some reason results are in a multi-dimen array, we just need the first
	}
    
    /**
     * Get a definition by name
     * 
     * @param string $objType
     */
    public function getDefinition($objType)
    {
        $defLoader = $this->account->getServiceManager()->get("EntityDefinitionLoader");
        return $defLoader->get($objType);
    }
    
    /**
	 * Get ids of all parent ids in a parent-child relationship
	 *
	 * @param string $table The table to query
	 * @param string $parent_field The field containing the id of the parent entry
	 * @param int $this_id The id of the child element
	 */
	public function getHeiarchyUp(\Netric\EntityDefinition\Field $field, $this_id)
	{
		$dbh = $this->dbh;
		$parent_arr = array($this_id);
        
        // TODO: finish
        /*
		if ($this_id && $parent_field)
		{
			$query = "select $parent_field as pid from $table where id='$this_id'";
			$result = $dbh->Query($query);
			if ($dbh->GetNumberRows($result))
			{
				$row = $dbh->GetNextRow($result, 0);

				$subchildren = $this->getHeiarchyUp($table, $parent_field, $row['pid']);

				if (count($subchildren))
					$parent_arr = array_merge($parent_arr, $subchildren);
			}
			$dbh->FreeResults($result);
		}
         */

		return $parent_arr;
	}

	/**
	 * Get ids of all child entries in a parent-child relationship
     * 
     * This function may be over-ridden in specific indexes for performance reasons
	 *
	 * @param string $table The table to query
	 * @param string $parent_field The field containing the id of the parent entry
	 * @param int $this_id The id of the child element
	 */
	public function getHeiarchyDownGrp(\Netric\EntityDefinition\Field $field, $this_id)
	{
		$children_arr = array($this_id);
        
        

		return $children_arr;
	}

	/**
	 * Get ids of all parent entries in a parent-child relationship of an object
	 *
	 * @param string $table The table to query
	 * @param string $parent_field The field containing the id of the parent entry
	 * @param int $this_id The id of the child element
	 */
	public function getHeiarchyUpObj($objType, $oid)
	{
		$ret = array($oid);

        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $ent = $loader->get($objType, $oid);
        $ret[] = $ent->getId();
        if ($ent->getDefinition()->parentField)
        {
            // Make sure parent is set, is of type object, and the object type has not crossed over (could be bad)
            $field = $ent->getDefinition()->getField($ent->getDefinition()->parentField);
            if ($ent->getValue($field->name) && $field->type == "object" && $field->subtype == $objType)
            {
                $children = $this->getHeiarchyUpObj($field->subtype, $ent->getValue($field->name));
                if (count($children))
                    $ret = array_merge($ret, $children);
            }
        }

		return $ret;
	}
    
    /**
	 * Get ids of all child entries in a parent-child relationship of an object
	 *
	 * @param string $table The table to query
	 * @param string $parent_field The field containing the id of the parent entry
	 * @param int $this_id The id of the child element
	 * @param int[] $aProtectCircular Hold array of already referenced objects to chk for array
	 */
	public function getHeiarchyDownObj($objType, $oid, $aProtectCircular=array())
	{
		// Check for circular refrences
		if (in_array($oid, $aProtectCircular))
			throw new \Exception("Circular reference found in $objType:$oid");
			//return array();

		$ret = array($oid);
		$aProtectCircular[] = $oid;

        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $ent = $loader->get($objType, $oid);
        //$ret[] = $ent->getId();
        if ($ent->getDefinition()->parentField)
        {
            // Make sure parent is set, is of type object, and the object type has not crossed over (could be bad)
            $field = $ent->getDefinition()->getField($ent->getDefinition()->parentField);
            if ($field->type == "object" && $field->subtype == $objType)
            {
                $index = $this->account->getServiceManager()->get("EntityQuery_Index");
                $query = new \Netric\EntityQuery($field->subtype);
                $query->where($ent->getDefinition()->parentField)->equals($ent->getId());
                $res = $index->executeQuery($query);
                for ($i = 0; $i < $res->getTotalNum(); $i++)
                {
                    $subEnt = $res->getEntity($i);
                    $children = $this->getHeiarchyDownObj($objType, $subEnt->getId(), $aProtectCircular);
                    if (count($children))
                        $ret = array_merge($ret, $children);
                }
            }
        }

		return $ret;
	}
}
