<?php
/**
 * Entities will replace objects eventually. The reason for the rename is simply because Object is not a good name given reserved ns.
 */
namespace Netric\Entity;

use My\Space\ExceptionNamespaceTest;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\FileSystem\FileSystem;
use Netric\EntityDefinition\Field;
use Netric\Entity\Recurrence\RecurrencePattern;
use Netric\EntityDefinition;

class Entity implements \Netric\Entity\EntityInterface
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
	 * Recurrence pattern if this entity is part of a series
	 *
	 * @var RecurrencePattern
	 */
	private $recurrencePattern = null;

    /**
     * Flag to indicate if this is a recurrence exception in the serices
     *
     * @var bool
     */
    private $isRecurrenceException = false;
    
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
        $values = $this->getValue($strName);

		if (isset($this->fkeysValues[$strName]))
        {
            // Only return value name data for peoperites in $values
            if (is_array($values))
            {
                $ret = array();
                foreach ($values as $val)
                {
                    if (isset($this->fkeysValues[$strName][$val]))
                    {
                        $ret[$val] = $this->fkeysValues[$strName][$val];
                    }
                }
                return $ret;
            }
            else if ($values && isset($this->fkeysValues[$strName][$values]))
            {
                return array($values=>$this->fkeysValues[$strName][$values]);
            }

        }

        return array();
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

        // Convert data types and validate
        $field = $this->def->getField($strName);
        if ($field)
        {
            switch ($field->type)
            {
                case 'bool':
                    if (is_string($value))
                    {
                        $value = ($value == 't') ? true : false;
                    }
                    break;
				case 'date':
				case 'timestamp':
					if ($value && !is_numeric($value))
					{
						$value = strtotime($value);
					}
					break;
            }
        }

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
        $this->logFieldChanges($strName, $value, $oldval, $oldvalName);
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
        $oldval = $this->getValue($strName);
        $oldvalName = $this->getValueNames($strName);

        if (!isset($this->values[$strName]))
            $this->values[$strName] = array();

        // Check to make sure we do not already have this value added
        for ($i = 0; $i < count($this->values[$strName]); $i++)
        {
        	if (!empty($this->values[$strName][$i]) && $value === $this->values[$strName][$i])
        	{
        		// The value was already added and they need to be unique

        		// Update valueName just in case it has changed
        		if ($valueName)
        			$this->fkeysValues[$strName][$value] = $valueName;

        		// Do not add an additional value
        		return;
        	}
        }
        
        // Set the value
        $this->values[$strName][] = $value;

        if ($valueName)
            $this->fkeysValues[$strName][$value] = $valueName;

        // Log changes
        $this->logFieldChanges($strName, $this->values[$strName], $oldval, $oldvalName);
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
     * Clear all values in a multi-value field
     *
     * @param string $fieldName The name of the field to clear
     */
    public function clearMultiValues($fieldName)
    {
        $this->setValue($fieldName, array(), array());
    }

	/**
	 * Get the local recurrence pattern
	 *
	 * @return RecurrencePattern
	 */
	public function getRecurrencePattern()
	{
		return $this->recurrencePattern;
	}

    /**
     * Check if this entity is an exception to a recurrence series
     *
     * @return bool
     */
    public function isRecurrenceException()
    {
        return $this->isRecurrenceException;
    }

	/**
	 * Set the recurrence pattern
	 *
	 * @param RecurrencePattern $recurrencePattern
	 */
	public function setRecurrencePattern(RecurrencePattern $recurrencePattern)
	{
		if ($recurrencePattern->getObjType() != $this->getDefinition()->getObjType())
			$recurrencePattern->setObjType($this->getDefinition()->getObjType());
		$this->recurrencePattern = $recurrencePattern;
	}

    /**
     * Record changes to the local changelog
     *
     * @param $strName
     * @param $value
     * @param $oldval
     * @param string $oldvalName
     */
    private function logFieldChanges($strName, $value, $oldval, $oldvalName="")
    {
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
	 * Set values from array
	 *
	 * @param array $data Associative array of values
	 */
	public function fromArray($data)
	{
		$fields = $this->def->getFields();
		foreach ($fields as $field)
		{
			$fname = $field->name;
			$value = (isset($data[$fname])) ? $data[$fname] : "";
			$valNames = array();

            // If the fieldname is recurrence pattern, let the RecurrencePattern Class handle the checking
            if($fname == 'recurrence_pattern')
                continue;

			// Check for fvals
			if (isset($data[$fname . "_fval"]))
			{
				if (!is_array($data[$fname . "_fval"]))
                {
                    $data[$fname . "_fval"] = array($data[$fname . "_fval"]);
                }

				$valNames = $data[$fname . "_fval"];
			}

			if (is_array($value))
			{
                // Clear existing value
                $this->clearMultiValues($fname);

				foreach ($value as $mval)
				{
					if (is_array($mval) || is_object($mval))
					{
						throw new \InvalidArgumentException(
							"Array value for $fname was " . var_export($mval, true)
						);
					}

					$valName = (isset($valNames[$mval])) ? $valNames[$mval] : null;
					$this->addMultiValue($fname, $mval, $valName);
				}
			}
			else
			{
                if (($field->type === "object_multi" || $field->type === "fkey_multi"))
                    $this->clearMultiValues($fname);

				$valName = (isset($valNames[$value])) ? $valNames[$value] : null;
				$this->setValue($fname, $value, $valName);
			}
		}

		// If the recurrence pattern data was passed then load it
		if (isset($data['recurrence_pattern']) && !empty($data['recurrence_pattern']))
		{
			$this->recurrencePattern = new RecurrencePattern();
			$this->recurrencePattern->fromArray($data['recurrence_pattern']);
			$this->recurrencePattern->setObjType($this->getDefinition()->getObjType());
		}

        if (isset($data['recurrence_exception']))
            $this->isRecurrenceException = $data['recurrence_exception'];
	}

	/**
	 * Get all values and return them as an array
	 *
	 * @return array Associative array of all fields in array(field_name=>value) format
	 */
	public function toArray()
	{
		$data = array(
			"obj_type" => $this->objType,
		);

        // If this is a recurring object, indicate if this is an exception
        if ($this->def->recurRules)
        {
            // If the field_recur_id is set then this is part of a series
            if ($this->getValue($this->def->recurRules['field_recur_id']))
            {
                $data['recurrence_exception'] = $this->isRecurrenceException;
            }
        }

		$fields = $this->def->getFields();

		foreach ($fields as $fname=>$field)
		{
			$val = $this->getValue($fname);

			if ($val)
			{
				switch ($field->type)
				{
					case 'date':
						$val = date('Y-m-d T', $val);
						break;
					case 'timestamp':
						$val = date('Y-m-d G:i:s T', $val);
						break;
					default:

				}
			}

			$data[$fname] = $val;

            $valueNames = $this->getValueNames($fname);
			if ($valueNames)
            {
                $data[$fname . "_fval"] = array();

                // Send the value name for each id
                if (is_array($val))
                {
                    foreach ($val as $id)
                    {
                        $data[$fname . "_fval"]["$id"] = $this->getValueName($fname, $id);
                    }
                }
                else if ($val)
                {
                    $data[$fname . "_fval"]["$val"] = $this->getValueName($fname, $val);
                }
            }
		}

		// Send the recurrence pattern if it is set
		if ($this->recurrencePattern)
		{
			$data['recurrence_pattern'] = $this->recurrencePattern->toArray();
		}

		return $data;
	}

	/**
	 * The datamapper will call this just before the entity is saved
	 *
	 * @param ServiceLocatorInterface $sm Service manager used to load supporting services
	 */
	public function beforeSave(ServiceLocatorInterface $sm)
	{
        // Make sure we have associations added for any object reference
        $fields = $this->getDefinition()->getFields();
        foreach ($fields as $field)
        {
            if ($field->type === "object")
            {
                $fieldValue = $this->getValue($field->name);
                if ($fieldValue && $field->subtype)
                {
                    $this->addMultiValue(
                        "associations",
                        Entity::encodeObjRef($field->subtype, $fieldValue)
                    );
                }
                else if ($fieldValue)
                {
                    $this->addMultiValue("associations", $fieldValue);
                }
            }
        }

		// Call derived extensions
		$this->onBeforeSave($sm);
	}

	/**
	 * Callback function used for derrived subclasses
	 *
	 * @param ServiceLocatorInterface $sm Service manager used to load supporting services
	 */
	public function onBeforeSave(ServiceLocatorInterface $sm) { }

	/**
	 * The datamapper will call this just after the entity is saved
	 *
	 * @param ServiceLocatorInterface $sm Service manager used to load supporting services
	 */
	public function afterSave(ServiceLocatorInterface $sm)
	{
		// Process any temp files or attachments associated with this entity
		$this->processTempFiles($sm->get("Netric/FileSystem/FileSystem"));

		// Call derived extensions
		$this->onAfterSave($sm);
	}

	/**
	 * Callback function used for derrived subclasses
	 *
	 * @param ServiceLocatorInterface $sm Service manager used to load supporting services
	 */
	public function onAfterSave(ServiceLocatorInterface $sm) { }

	/**
	 * The datamapper will call this just before an entity is purged -- hard delete
	 *
	 * @param ServiceLocatorInterface $sm Service manager used to load supporting services
	 */
	public function beforeDeleteHard(ServiceLocatorInterface $sm)
	{
		// Call derived extensions
		$this->onBeforeDeleteHard($sm);
	}

	/**
	 * Callback function used for derrived subclasses
	 *
	 * @param ServiceLocatorInterface $sm Service manager used to load supporting services
	 */
	public function onBeforeDeleteHard(ServiceLocatorInterface $sm) { }

	/**
	 * The datamapper will call this just after an entity is purged -- hard delete
	 *
	 * @param ServiceLocatorInterface $sm Service manager used to load supporting services
	 */
	public function afterDeleteHard(ServiceLocatorInterface $sm)
	{
		// Call derived extensions
		$this->onAfterDeleteHard($sm);
	}

	/**
	 * Callback function used for derrived subclasses
	 *
	 * @param ServiceLocatorInterface $sm Service manager used to load supporting services
	 */
	public function onAfterDeleteHard(ServiceLocatorInterface $sm) { }

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
            {
                return true;
            }
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
     * Try and get a textual description of this entity typically found in fileds named "notes" or "description"
     *
     * @return string The name of this object
     */
    public function getDescription()
    {
        $fields = $this->def->getFields();
        foreach ($fields as $field)
        {
            if ($field->type == 'text')
            {
                if ($field->name == "description"
                    || $field->name == "notes"
                    || $field->name == "details"
                    || $field->name == "comment")
                {
                    return $this->getValue($field->name);
                }
            }

        }

        return "";
    }

    /**
     * Get a textual representation of what changed
     */
    public function getChangeLogDescription()
    {
        $hide = array(
            "revision",
            "uname",
            "num_comments",
            "num_attachments",
        );
        $buf = "";
        foreach ($this->changelog as $fname=>$log)
        {
            $oldVal = $log['oldval'];
            $newVal = $log['newval'];

            $field = $this->def->getField($fname);

            // Skip multi key arrays
            if ($field->type == "object_multi" || $field->type == "fkey_multi")
                continue;

            if ($field->type == "bool")
            {
                if ($oldVal == 't') $oldVal = "Yes";
                if ($oldVal == 'f') $oldVal = "No";
                if ($newVal == 't') $newVal = "Yes";
                if ($newVal == 'f') $newVal = "No";
            }

            if (!in_array($field->name, $hide))
            {
                $buf .= $field->title . " was changed ";
                if ($oldVal)
                    $buf .="from \"" . $oldVal . "\" ";
                $buf .= "to \"" . $newVal . "\" \n";
            }
        }

        if (!$buf)
            $buf = "No changes were made";

        return $buf;
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
	 * Static function used to decode object reference string
	 *
	 * @param string $value The object ref string - [obj_type]:[obj_id]:[name] (last param is optional)
	 * @return array Assoc array with the following keys: obj_type, id, name
	 */
	static public function decodeObjRef($value)
	{
		$parts = explode(":", $value);
		if (count($parts)>1)
		{
			$ret = array(
				'obj_type' => $parts[0],
				'id' => null,
				'name' => null,
			);

            // Was encoded with obj_type:id:name (new)
            if (count($parts) === 3)
            {
                $ret['id'] = $parts[1];
                $ret['name'] = $parts[2];
            }
            else
            {
                // Check for full name added after bar '|' (old)
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
            }

			return $ret;
		}
		else
			return false;
	}

    /**
     * Statfic function used to encode an object reference string
     *
     * @param string $objType The type of entity being referenced
     * @param string $id The id of the entity being referenced
     * @param string $name The human readable name of the entity being referenced
     * @return string Encoded object reference
     */
    static public function encodeObjRef($objType, $id, $name = null)
    {
        $ret = $objType . ":" . $id;

        if ($name)
            $ret .= ":" . $name;

        return $ret;
    }

    /**
     * Get the encoded object reference for this entity
     *
     * @param bool $includeName If true then name will be encoded with the reference
     * @return string [obj_type]:[id]:[name]
     */
    public function getObjRef($includeName = false)
    {
        $name = ($includeName) ? $this->getName() : null;
        return self::encodeObjRef($this->def->getObjType(), $this->getId(), $name);
    }

	/**
	 * Process temporary file uploads and move them into the object folder
	 *
	 * Files are initially uploaded by users into the temp directory and then
	 * the fileId is set in the field. When we save we need to check if any of
	 * the referenced files are in temp and move them to the object directory
	 * because everything in temp get's purged after a period of time.
	 *
	 * @param FileSystem $fileSystem Handle to the netric filesystem service
	 */
	public function processTempFiles(FileSystem $fileSystem)
	{
		$fields = $this->def->getFields();
		foreach ($fields as $field)
		{
			if (($field->type === "object" || $field->type === "object_multi") &&
				$field->subtype === "file")
			{
				// Only process if the value has changed since last time
				if ($this->fieldValueChanged($field->name))
				{
					// Make a files array - if it's an object than an array of one
					$files = ($field->type == "object") ?
						array($this->getValue($field->name)) :
						$this->getValue($field->name);

					if (is_array($files))
					{
						foreach ($files as $fid)
						{
							$file = $fileSystem->openFileById($fid);

							// Check to see if the file is a temp file
							if ($file)
							{
                                $fileFolder = $fileSystem->openFolderById($file->getValue("folder_id"));
                                $tempFolder = $fileSystem->openFolder("%tmp%");
								if ($fileSystem->fileIsTemp($file))
								{
									// Move file to a permanent directory
									$objDir = "/System/objects/" . $this->def->getObjType() . "/" . $this->getId();
									$fldr = $fileSystem->openFolder($objDir, true);
									if ($fldr->getId())
									{
										$fileSystem->moveFile($file, $fldr);
									}
								}
							}
						}
					}
				}
			}
		}
	}


    /**
     * Perform a clone of this entity to another
     *
     * This essentially does a shallow copy of all values
     * from this entity into $toEntity, with the exception of
     * ID which will be left blank for saving.
     *
     * @param Entity $toEntity
     * @return Entity
     */
    public function cloneTo(Entity $toEntity)
    {
        $thisData = $this->toArray();
        $thisData['id'] = null;
        $toEntity->fromArray($thisData);
    }

    /**
     * Increment the comments counter for this entity
     *
     * @param bool $added If true increment, if false then decrement for deleted comment
     * @param int $numComments Optional manual override to set total number of comments
     * @return bool true on success false on failure
     */
    public function setHasComments($added=true, $numComments=null)
    {
        // We used to store a flag in cache, but now we put comment counts in the actual object
        if ($numComments == null)
        {
            $cur = ($this->getValue('num_comments')) ? (int) $this->getValue('num_comments') : 0;
            if ($added)
                $cur++;
            else if ($cur > 0)
                $cur--;
        }
        else
        {
            $cur = $numComments;
        }

        $this->setValue("num_comments", $cur);
    }
}
