<?php
/**
 * Entities will replace objects eventually. The reason for the rename is simply because Object is not a good name given reserved ns.
 */
namespace Netric;

class Entity implements EntityInterface
{
	/**
     * The unique id of this object/entity
     * 
     * @var string
     */
    protected $id;
    
    /**
     * The values for the fields of this entity
     * 
     * @var array
     */
    protected $values = array();
    
    /**
     * Set object type
     * 
     * @var string
     */
    protected $objType = "";
    
    /**
     * The values for the fkey or object keys
     * 
     * @var array
     */
    protected $fkeysValues = array();

	/**
	 * Object type definition
	 *
	 * @var EntityDefinition
	 */
	protected $def = null;

	/**
	 * Array tracking changed fields
	 *
	 * @var array
	 */
	private $changelog = array();
    
    /**
     * Class constructor
     * 
     * @param EntityDefinition $def The definition of this type of object
     */
    public function __construct(&$def) 
    {
		$this->def = $def;
        $this->objType = $def->getObjType();
    }

	/**
     * This function is responsible for loading subclasses or the base class
     * 
     * @param EntityDefinition $def The definition of this type of object
     */
    public static function factory(\Netric\EntityDefinition &$def)
    {
		$obj = false;
		$objType = $def->getObjType();

		// First convert object name to file name - camelCase
		$fname = ucfirst($objType);
		if (strpos($objType, "_") !== false)
		{
			$parts = explode("_", $fname);
			$fname = "";
			foreach ($parts as $word)
				$fname .= ucfirst($word);
		}

		// Dynamically load subclass if it exists
		if (file_exists(dirname(__FILE__)."/Entity/" . $fname . ".php"))
		{
			$className = '\Netric\Entity' . "\\" . $fname;

			$obj = new $className($def);
		}
		else
		{
			$obj = new Entity($def);
		}

		return $obj;
    }
    
    /**
     * Get the object type of this object
     * 
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

	/**
	 * Get unique id of this object
	 */
	public function getId()
	{
		return $this->id;
	}
    
    /**
	 * Set the unique id of this object
     * 
     * @param string $id The unique id of this object instance
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Get definition
	 *
	 * @return EntityDefinition
	 */
	public function getDefinition()
	{
		return $this->def;
	}
    
    /**
     * Return either the string or an array of values if *_multi
     * 
     * @param string $strname
     * @return string|array
     */
    public function getValue($strname)
    {
        return (isset($this->values[$strname])) ? $this->values[$strname] : null;
    }
    
    /**
     * Get fkey name for key/value field types like fkey and fkeyMulti
     * 
     * @param string $strName The name of the field to pull
	 * @param string $id If set, get the label for the id
     * @return string
     */
    public function getValueName($strName, $id=null)
    {
		$name = "";

		if (isset($this->fkeysValues[$strName]))
		{
			foreach ($this->fkeysValues[$strName] as $key=>$value)
			{
				if ($id)
				{
					if ($key == $id)
						$name = $value;
				}
				else
				{
					if ($name)
						$name .= ", ";

					$name .= $value;
				}
			}
		}

        return $name;
    }

    /**
     * Get fkey name array for key/value field types like fkey and fkeyMulti
     * 
     * @param string $strName The name of the field to pull
     * @return array(array("id"=>"name"))
     */
    public function getValueNames($strName)
    {
		if (isset($this->fkeysValues[$strName]))
			return $this->fkeysValues[$strName];

        return null;
    }
    
    /**
     * Set a field value for this object
     * 
     * @param string $strName
     * @param mixed $value
     * @param string $valueName If this is an object or fkey then cache the foreign value
     */
    public function setValue($strName, $value, $valueName=null)
    {
		$oldval = $this->getValue($strName);
		$oldvalName = $this->getValueNames($strName);

        $this->values[$strName] = $value;
        
        if ($strName == "id")
            $this->setId($value);
        
        if ($valueName)
		{
			if (is_array($valueName))
            	$this->fkeysValues[$strName] = $valueName;
			else
            	$this->fkeysValues[$strName] = array($value=>$valueName);
		}

		// Log changes
		if ($oldval != $value)
		{
			$oldvalraw = $oldval;
			$newvalraw = $value;

			if ($oldvalName)
				$oldval = $oldvalName;

			if ($this->getValueNames($strName))
				$newval = $this->getValueNames($strName);
			else
				$newval = $value;

			$this->changelog[$strName] = array(
				"field"=>$strName, 
				"oldval"=>$oldval, 
				"newval"=>$newval, 
				"oldvalraw"=>$oldvalraw, 
				"newvalraw"=>$newvalraw
			);
		}
    }
    
    /**
     * Add a multi-value entry to the *_multi type field
     * 
     * @param string $strName
     * @param string|int $value
     * @param string $valueName Optional value name if $value is a key
     */
    public function addMultiValue($strName, $value, $valueName="")
    {
        if (!isset($this->values[$strName]))
            $this->values[$strName] = array();
        
        $this->values[$strName][] = $value;

        if ($valueName)
            $this->fkeysValues[$strName][$value] = $valueName;
    }
    
    /**
     * Remove a value from a *_multi type field
     * 
     * @param string $strName
     * @param string|int $value
     */
    public function removeMultiValue($strName, $value)
    {
        // TODO: remove the value from the multi-value array
    }
   
	/**
	 * Set values from array
	 *
	 * @param array $data Associative array of values
	 */
	public function fromArray($data)
	{
		foreach ($data as $fname=>$value)
		{
			$valNames = array();

			// Check for fvals
			if (isset($data[$fname . "_fval"]))
				$valNames = $data[$fname . "_fval"];

			if (is_array($value))
			{
				foreach ($value as $mval)
				{
					$valName = (isset($valNames[$mval])) ? $valNames[$mval] : null;
					$this->addMultiValue($fname, $mval, $valName);
				}
			}
			else
			{
				$valName = (isset($valNames[$value])) ? $valNames[$value] : null;
				$this->setValue($fname, $value, $valName);
			}
		}
	}

	/**
	 * Get all values and return them as an array
	 *
	 * @return array Associative array of all fields in array(field_name=>value) format
	 */
	public function toArray()
	{
		$data = array();
		$fields = $this->def->getFields();

		foreach ($fields as $fname=>$field)
		{
			$data[$fname] = $this->getValue($fname);

			if ($this->getValueNames($fname))
				$data[$fname . "_fval"] = $this->getValueNames($fname);
		}

		return $data;
	}

	/**
	 * Save this object to a datamapper
	 *
	 * @param Entity_DataMapperInterface $dm The datamapper for saving data
	 * @param AntUser $user The user who is saving this object
	 */
	public function save(Entity_DataMapperInterface $dm, $user)
	{
		throw new \Exception("Save is not yet implemented");

		$dbh = $this->dbh;
		$all_fields = $this->def->getFields();

		// Set all null defaults
		$this->setFieldsDefault("null", $user);

		// Set all update defaults
		$this->setFieldsDefault("update", $user);

		// Set all create defaults if this is a new object
		if (!$this->getId())
			$this->setFieldsDefault("create", $user);

		// First check for and set security
		$daclLoader = $dm->getServiceLocator()->get("DaclLoader");
		if ($user && $daclLoader)
		{
			// Get owner for GROUP_CREATOROWNER
			$ownerId = null;
			$fdef = $this->def->getField("owner_id");
			if ($fdef)
			{
				$userId = $this->getValue("owner_id");
			}
			else
			{
				// Some older objects used user_id rather than owner_id
				// This is provided for backwards compatibility only and should
				// eventually be deleted
				$fdef = $this->def->getField("user_id");
				if ($fdef)
					$userId = $this->getValue("user_id");
			}

			// Load Discretionary Access Control List
			if ($this->getValue("dacl"))
			{
				$daclDat = json_decode($this->getValue("dacl"), true);
				$dacl = $daclLoader->byData("/objects/" . $this->def->getObjType(), $daclDat);
			}
			else
			{
				// Get default generic DACL for all objects of this type, using the loader will cache it
				// if the object has a specific DACL then it will be pulled in the load function or when
				// a variable is set to a parent object to inherit from
				$dacl = $daclLoader->byName("/objects/" . $this->def->getObjType());
				if (!$dacl->id)
				{
					// Create if it does not exist
					$dacl->grantGroupAccess(GROUP_ADMINISTRATORS);
					$dacl->grantUserAccess(GROUP_CREATOROWNER);
					$dacl->save();
				}
			}

			// Check to see if the current user has edit access to this object
			if (!$dacl->checkAccess($user, "Edit", ($user->id==$userId)?true:false))
			{
				return false;
			}
		}

		// Derrived classes can define before save event
		$this->onBeforeSave($dm);

		// Increment revision
		$revision = $this->getValue("revision");
		$revision = (is_numeric($revision)) ? $revision+1 : 1;
		$this->setValue("revision", $revision);

		// Make sure that a parent field is not referening itself causing an endless loop
		// So far this only goes one level deep, deeper loops need to be checked in updateHeiarchPath
		if ($this->def->parentField)
		{
			$pfield = $this->def->getField($this->def->parentField);
			if ($pfield->subtype == $this->def->getObjType() && $this->getValue($this->def->parentField) == $this->getId())
				$this->setValue($this->def->parentField, ""); // set to null
		}

		// Deal with unique names - do not create unique names for activities
		if (!$this->getValue("uname") && $this->def->getObjType()!="activity")
		{
			$this->setValue("uname", $this->createUniqueName($dm));
		}
		else if ($this->fieldValueChanged($this->getValue("uname")) && $this->def->getObjType()!="activity") 
		{
			// Safe guard against duplicate unames if the uname was manually set since last load
			if (!$dm->verifyUniqueName($this, $this->getValue("uname")))
				$this->setValue("uname", $this->createUniqueName($dm)); // Reset with new unique name
		}

		// TODO: Sky Stebnicki - stopped here... everything below is from CAntObject and needs to be modified 

		// Get recurrence pattern ID
		if (!$this->recurrenceException && $this->def->recurRules!=null)
		{
			// If this event had recur_id saved in field, then load, otherwise leave null
			if ($this->recurrencePattern == null)
			{
				$rid = $this->getValue($this->def->recurRules['field_recur_id']);
				if ($rid)
					$this->getRecurrencePattern($rid);
			}

			if ($this->recurrencePattern != null)
			{
				if (!isset($rid))
					$rid = $this->recurrencePattern->getNextId();

				if (!$this->getValue($this->def->recurRules['field_recur_id']))
					$this->setValue($this->def->recurRules['field_recur_id'], $rid);
			}
		}	

		// Save values to the datamapper
		// ------------------------------------------------------------------
		$dm->save($this, $user);
		

		// Continue editing below
		// ------------------------------------------
		

			// Call saved for derrived class callbacks
			$this->saved();

			// Clear object values cache - will not clear definition
			$this->clearCache();

			// Save revision history
			$this->saveRevision();

			// Set and save recurrence pattern
			if (!$this->recurrenceException && $this->def->recurRules!=null && $this->recurrencePattern!=null)
				$rid = $this->recurrencePattern->saveFromObj($this);

			// Load inserted data for defaults
			if ($performed == "create")
				$this->load();

			// Index this object
			$this->index();

			// Comments on activities should be excluded from activities
			if ($this->object_type == "comment")
			{
				$obj_ref = $this->getValue("obj_reference");
				if ($obj_ref)
				{
					$parts = explode(":", $obj_ref);
					if ($parts[0] == "activity")
					{
						$logact = false;
					}
				}
			}

			// Update path
			if ($this->def->parentField)
			{
				$this->updateHeiarchPath();
			}

			// Update uname index table
			//if ($this->getValue("uname"))
				//$this->setUniqueName($this->getValue("uname"));

			// Process workflow
			$this->processWorkflow($performed);

			// Process temp file uploads
			$this->processTempFiles();

		if ($logact)
		{
			$this->updateObjectSyncStat('c');

			if ($performed == "create" && $this->object_type != "activity")
			{
				$desc = $this->getDesc();
				$this->addActivity("created", $this->getName(), ($desc)?$desc:"New object created", null, null, 't');

			}

			if ($performed == "update" && $this->object_type != "activity")
			{
				$desc = $this->getChangeLogDesc();
				$this->addActivity("updated", $this->getName(), ($desc)?$desc:"Object Updated", null, null, 't');
			}
		}

		if (count($this->def->aggregates))
		{
			$this->saveAggregates($this->def->aggregates);
		}

		return $this->id;
	}

	/**
	 * Callback function used for derrived subclasses
	 *
	 * @param \Netric\ServiceManager $sm Service manager used to load supporting services
	 */
	public function onBeforeSave(\Netric\ServiceManager $sm) { }

	/**
	 * Callback function used for derrived subclasses
	 *
	 * @param E\Netric\ServiceManager $sm Service manager used to load supporting services
	 */
	public function onAfterSave(\Netric\ServiceManager $sm) { }

	/**
	 * Check if a field value changed since created or opened
	 *
	 * @param string $checkfield The field name
	 * @return bool true if it is dirty, false if unchanged
	 */
	public function fieldValueChanged($checkfield)
	{
		if (!is_array($this->changelog))
			return false;

		foreach ($this->changelog as $fname=>$log)
		{
			if ($fname == $checkfield)
				return true;
		}

		return false;
	}
    
    /**
	 * Get previous value of a changed field
	 *
	 * @param string $checkfield The field name
	 * @return null if not found, mixed old value if set
	 */
	public function getPreviousValue($checkfield)
	{
		if (!is_array($this->changelog))
			return null;

        if (isset($this->changelog[$checkfield]["oldvalraw"]))
            return $this->changelog[$checkfield]["oldvalraw"];
        
		return null;
	}

	/**
	 * Reset is dirty indicating no changes need to be saved
	 */
	public function resetIsDirty()
	{
		$this->changelog = array();
	}

	/**
	 * Check if the object values have changed
	 *
	 * @return true if object has been edited, false if not
	 */
	public function isDirty()
	{
		return (count($this->changelog)>0) ? true : false;
	}

	/**
	 * Create a unique name for this object given the values of the object
	 *
	 * Unique names may only have alphanum chars, no spaces, no special
	 *
	 * @param Entity_DataMapperInterface $dm Datamapper used to verify uname
	 */
	private function createUniqueName($dm)
	{
		$dbh = $this->dbh;

		// If already set then return current value
		if ($this->getValue("uname") || !$create)
			return $this->getValue("uname");

		$uname = "";

		// Get unique name conditions
		$settings = $this->def->unameSettings;

		if ($settings)
		{
			$alreadyExists = false;

			$uriParts = explode(":", $settings);

			// Create desired uname from the right field
			if ($uriParts[count($uriParts)-1] == "name")
				$uname = $this->getName();
			else
				$uname = $this->getValue($uriParts[count($uriParts)-1]); // last one is the uname field

			// The uname must be populated before we try to save anything
			if (!$uname)
				return "";

			// Now escape the uname field to a uri fiendly name
			$uname = strtolower($uname);
			$uname = str_replace(" ", "-", $uname);
			$uname = str_replace("?", "", $uname);
			$uname = str_replace("&", "_and_", $uname);
			$uname = str_replace("---", "-", $uname);
			$uname = str_replace("--", "-", $uname);
			$uname = preg_replace('/[^A-Za-z0-9_-]/', '', $uname);

			$isUnique = $dm->verifyUniqueName($this, $uname); // Do not reset because that would create a loop

			// If the unique name already exists, then append with id or a random number
			if (!$isUnique)
			{
				$uname .= "-";
				$uname .= ($this->getId()) ? $this->getId() : uniqid(); 
			}
		}
		else if ($this->getId())
		{
			// uname is required but we are working with objects that do not need unique uri names then just use the id
			$uname = $this->getId();
		}

		return $uname;
	}	

	/**
	 * Get name of this object based on common name fields
	 *
	 * @return string The name/label of this object
	 */
	public function getName()
	{
		if ($this->def->getField("name"))
			return $this->getValue("name");
		if ($this->def->getField("title"))
			return $this->getValue("title");
		if ($this->def->getField("subject"))
			return $this->getValue("subject");
		if ($this->def->getField("full_name"))
			return $this->getValue("full_name");
		if ($this->def->getField("first_name"))
			return $this->getValue("first_name");

		return $this->getId();
	}

	/**
	 * Check if the deleted flag is set for this object
	 *
	 * @return bool
	 */
	public function isDeleted()
	{
		return $this->getValue("f_deleted");
	}

	/**
	 * Set defaults for a field given an event
	 *
	 * @param string $event The event we are firing
	 * @param AntUser $user Optional current user for default variables
	 */
	public function setFieldsDefault($event, $user=null)
	{
		$fields = $this->def->getFields();
		foreach ($fields as $fname=>$field)
		{
			// Currently multi-values are not supported for defaults
			if ($field->type == "object_multi" || $field->type == "fkey_multi")
				continue;

			$val = $this->getValue($fname);
			$new = $field->getDefault($val, $event, $this, $user);

			// If the default was different, then set it
			if ($new != $val)
				$this->setValue($fname, $new);
		}
	}
    
    /**
	 * Static funciton used to decode object reference string
	 *
	 * @param string $value The object ref string - [obj_type]:[obj_id]:[name] (last param is optional)
	 * @return array Assoc array with the following keys: obj_type, id, name
	 */
	static public function decodeObjRef($value)
	{
		$parts = explode(":", $value);
		if (count($parts)>1)
		{
			$ret = array();
			$ret['obj_type'] = $parts[0];

			// Check for full name added after bar '|'
			$parts2 = explode("|", $parts[1]);
			if (count($parts2)>1)
			{
				$ret['id'] = $parts2[0];
				$ret['name'] = $parts2[1];
			}
			else
			{
				$ret['id'] = $parts[1];
			}

			return $ret;
		}
		else
			return false;
	}

}
