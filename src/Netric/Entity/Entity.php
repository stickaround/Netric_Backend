<?php

namespace Netric\Entity;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\FileSystem\FileSystem;
use Netric\Entity\Recurrence\RecurrencePattern;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\Field;
use DateTime;
use Netric\FileSystem\FileSystemFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Permissions\DaclLoaderFactory;
use Ramsey\Uuid\Uuid;
use Netric\Entity\EntityLoader;

/**
 * Base class sharing common functionality of all stateful entities
 */
class Entity implements EntityInterface
{
    /**
     * The values for the fields of this entity
     *
     * @var array
     */
    protected $values = [];

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
    protected $fkeysValues = [];

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
    private $changelog = [];

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
     * Loader used to get entity owner details and entity members
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader Loader used to get entity followers and entity owner details
     */
    public function __construct(EntityDefinition $def, EntityLoader $entityLoader)
    {
        $this->def = $def;
        $this->objType = $def->getObjType();
        $this->entityLoader = $entityLoader;
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
     * Get the unique id of this object
     */
    public function getEntityId(): string
    {
        $guid = $this->getValue("entity_id");
        // Convert null to empty string
        return $guid ? $guid : '';
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
     * @param string $valueId If set, get the label for the id
     * @return string
     */
    public function getValueName($strName, $getForId = null)
    {
        $valueNames = $this->getValueNames($strName);

        if ($getForId && is_array($valueNames) && count($valueNames) > 0) {
            foreach ($valueNames as $valId => $valName) {
                if ($valId == $getForId) {
                    $valueNames = [$valName];
                    break;
                }
            }
        }

        if (count($valueNames)) {
            return implode(',', $valueNames);
        }

        // No names could be found
        return '';
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

        if (isset($this->fkeysValues[$strName])) {
            // Only return value name data for peoperites in $values
            if (is_array($values)) {
                $ret = [];
                foreach ($values as $val) {
                    if (isset($this->fkeysValues[$strName][$val])) {
                        $ret[$val] = $this->fkeysValues[$strName][$val];
                    }
                }
                return $ret;
            } elseif ($values && isset($this->fkeysValues[$strName][$values])) {
                return array($values => $this->fkeysValues[$strName][$values]);
            }
        }

        return [];
    }

    /**
     * Set a field value for this object
     *
     * @param string $strName
     * @param mixed $value
     * @param string $valueName If this is an object or fkey then cache the foreign value
     */
    public function setValue($strName, $value, $valueName = null)
    {
        $oldval = $this->getValue($strName);
        $oldvalName = $this->getValueName($strName);

        // Convert data types and validate
        $field = $this->def->getField($strName);
        if ($field) {
            switch ($field->type) {
                case Field::TYPE_BOOL:
                    if (is_string($value)) {
                        $value = ($value === 't' || $value === 'true') ? true : false;
                    }
                    break;
                case Field::TYPE_DATE:
                case Field::TYPE_TIMESTAMP:
                    if ($value && !is_numeric($value)) {
                        $value = strtotime($value);
                    }
                    break;
                case Field::TYPE_GROUPING_MULTI:
                case Field::TYPE_OBJECT_MULTI:
                    if ($value && !is_array($value)) {
                        if ($valueName && !is_array($valueName)) {
                            $valueName = [$value => $valueName];
                        }

                        $value = array($value);
                    }
            }
        }

        $this->values[$strName] = $value;

        if ($valueName) {
            if (is_array($valueName)) {
                $this->fkeysValues[$strName] = $valueName;
            } elseif (is_string($value) || is_numeric($value)) {
                $this->fkeysValues[$strName] = array((string) $value => $valueName);
            } else {
                throw new \InvalidArgumentException(
                    "Invalid value name for object or fkey: " .
                        var_export($value, true)
                );
            }
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
    public function addMultiValue($strName, $value, $valueName = "")
    {
        $oldval = $this->getValue($strName);
        $oldvalName = $this->getValueNames($strName);

        if (!isset($this->values[$strName]) || $this->values[$strName] == '') {
            $this->values[$strName] = [];
        }

        $fieldMultiValues = $this->values[$strName];

        // Check to make sure we do not already have this value added
        foreach ($fieldMultiValues as $key => $mValue) {
            if ($value == $mValue) {
                // The value was already added and they need to be unique

                // Update valueName just in case it has changed
                if ($valueName) {
                    $valueKeyName = (string) $value;
                    $this->fkeysValues[$strName][$mValue] = $valueName;
                }

                // Do not add an additional value
                return;
            }
        }

        // Set the value
        $this->values[$strName][] = $value;

        if ($valueName) {
            $valueKeyName = (string) $value;

            // Make sure we initialize the arrays
            if (!isset($this->fkeysValues[$strName])) {
                $this->fkeysValues[$strName] = [];
            }

            if (!isset($this->fkeysValues[$strName][$valueKeyName])) {
                $this->fkeysValues[$strName][$valueKeyName] = [];
            }

            $this->fkeysValues[$strName][$valueKeyName] = $valueName;
        }

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
        $fieldMultiValues = $this->values[$strName];

        // Loop thru the field values and look for the value that we will be removing
        foreach ($fieldMultiValues as $key => $mValue) {
            if ($value == $mValue) {

                // Unset the array index and it will be removed from the field multi value
                unset($fieldMultiValues[$key]);
                unset($this->fkeysValues[$strName][$mValue]);

                // Re-index the array
                $this->values[$strName] = array_values($fieldMultiValues);

                // Log changes
                $this->logFieldChanges($strName, $this->values[$strName], $mValue, null);
                break;
            }
        }
    }

    /**
     * Update a value name from a *_multi type field
     * 
     * @param string $strName The name of the field
     * @param string|int $value The value of the field that we will update its value name
     * @param string $valueName The value name that will be using for update
     */
    public function updateValueName($strName, $value, $valueName)
    {
        $oldValueName = $this->fkeysValues[$strName][$value];
        $this->fkeysValues[$strName][$value] = $valueName;

        // Log changes
        $this->logFieldChanges($strName, $this->fkeysValues[$strName], $valueName, $$oldValueName);
    }

    /**
     * Clear all values in a multi-value field
     *
     * @param string $fieldName The name of the field to clear
     */
    public function clearMultiValues($fieldName)
    {
        $this->setValue($fieldName, [], []);
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
        if ($recurrencePattern->getObjTypeId() != $this->getDefinition()->getEntityDefinitionId()) {
            $recurrencePattern->setObjTypeId($this->getDefinition()->getEntityDefinitionId());
        }
        $this->recurrencePattern = $recurrencePattern;
    }

    /**
     * Get the owner of this entity
     *
     * @return string
     */
    public function getOwnerGuid()
    {
        $ownerGuid = '';

        if ($this->getValue('creator_id')) {
            $ownerGuid = $this->getValue('creator_id');
        } else if ($this->getValue('owner_id')) {
            $ownerGuid = $this->getValue('owner_id');
        }

        // If ownerGuid is not a valid guid, then we need to look for its guid
        if (!Uuid::isValid($ownerGuid) && is_numeric($ownerGuid)) {
            $ownerEntity = $this->entityLoader->getByGuid($ownerGuid);

            if ($ownerEntity) {
                $ownerGuid = $ownerEntity->getEntityId();
            }
        }

        // No owner
        return $ownerGuid;
    }

    /**
     * Record changes to the local changelog
     *
     * @param $strName
     * @param $value
     * @param $oldval
     * @param string $oldvalName
     */
    private function logFieldChanges($strName, $value, $oldval, $oldvalName = "")
    {
        // Log changes
        if ($oldval != $value) {
            $oldvalraw = $oldval;
            $newvalraw = $value;

            if ($oldvalName) {
                $oldval = $oldvalName;
            }

            $newval = $value;

            if ($this->getValueNames($strName)) {
                $newval = $this->getValueNames($strName);
            }

            $this->changelog[$strName] = array(
                "field" => $strName,
                "oldval" => $oldval,
                "newval" => $newval,
                "oldvalraw" => $oldvalraw,
                "newvalraw" => $newvalraw
            );
        }
    }

    /**
     * Set values from array
     *
     * @param array $data Associative array of values
     * $param bool $onlyProvidedFields Optional. If true, it will check first if field exists in $data array
     *                                  before settign a field value
     */
    public function fromArray($data, $onlyProvidedFields = false)
    {
        $fields = $this->def->getFields();
        foreach ($fields as $field) {
            $fname = $field->name;
            $value = (isset($data[$fname])) ? $data[$fname] : "";
            $valNames = [];

            /*
             * If $onlyProvidedFields is set to true, we need to check first if field key exists in $data array
             * If field key does not exist, then we do not need update the current field.
             */
            if ($onlyProvidedFields && !isset($data[$fname])) {
                continue;
            }

            // If the fieldname is recurrence pattern, let the RecurrencePattern Class handle the checking
            if ($fname == 'recurrence_pattern') {
                continue;
            }

            // Check for fvals
            if (isset($data[$fname . "_fval"])) {
                if (!is_array($data[$fname . "_fval"])) {
                    $data[$fname . "_fval"] = array($data[$fname . "_fval"]);
                }

                $valNames = $data[$fname . "_fval"];
            }

            if (is_array($value)) {
                // Clear existing value
                $this->clearMultiValues($fname);

                foreach ($value as $mval) {
                    if (is_array($mval) || is_object($mval)) {
                        throw new \InvalidArgumentException(
                            "Array value for $fname was " . var_export($mval, true)
                        );
                    }

                    $valName = (isset($valNames[$mval])) ? $valNames[$mval] : null;
                    $this->addMultiValue($fname, $mval, $valName);
                }
            } else {
                if (($field->type == FIELD::TYPE_OBJECT_MULTI || $field->type == FIELD::TYPE_GROUPING_MULTI)) {
                    $this->clearMultiValues($fname);
                }

                $valName = (isset($valNames[$value])) ? $valNames[$value] : null;
                $this->setValue($fname, $value, $valName);
            }
        }

        // If the recurrence pattern data was passed then load it
        if (isset($data['recurrence_pattern']) && is_array($data['recurrence_pattern'])) {
            $this->recurrencePattern = new RecurrencePattern();
            if (!isset($data['recurrence_pattern']['entity_definition_id'])) {
                $data['recurrence_pattern']['entity_definition_id'] = $this->getDefinition()->getEntityDefinitionId();
            }
            $this->recurrencePattern->fromArray($data['recurrence_pattern']);
        }

        if (isset($data['recurrence_exception'])) {
            $this->isRecurrenceException = $data['recurrence_exception'];
        }
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
        if ($this->def->recurRules) {
            // If the field_recur_id is set then this is part of a series
            if ($this->getValue($this->def->recurRules['field_recur_id'])) {
                $data['recurrence_exception'] = $this->isRecurrenceException;
            }
        }

        $fields = $this->def->getFields();

        foreach ($fields as $fname => $field) {
            $val = $this->getValue($fname);

            if ($val) {
                switch ($field->type) {
                    case 'date':
                        $val = date('Y-m-d T', $val);
                        break;
                    case 'timestamp':
                        $val = date(DateTime::ATOM, $val);
                        break;
                    default:
                }
            }

            // Make sure we will not overwrite the obj_type
            if ($fname !== 'obj_type') {
                $data[$fname] = $val;
            }

            $valueNames = $this->getValueNames($fname);
            if ($valueNames) {
                $data[$fname . "_fval"] = [];

                // Send the value name for each id
                if (is_array($val)) {
                    foreach ($val as $id) {
                        $data[$fname . "_fval"]["$id"] = $this->getValueName($fname, $id);
                    }
                } elseif ($val) {
                    $data[$fname . "_fval"]["$val"] = $this->getValueName($fname, $val);
                }
            }
        }

        // Send the recurrence pattern if it is set
        if ($this->recurrencePattern) {
            $data['recurrence_pattern'] = $this->recurrencePattern->toArray();
        }

        return $data;
    }

    /**
     * The datamapper will call this just before the entity is saved
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function beforeSave(AccountServiceManagerInterface $sm)
    {
        // Update or add followers based on changes to fields
        $this->updateFollowers();

        // Call derived extensions
        $this->onBeforeSave($sm);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(AccountServiceManagerInterface $sm)
    {
    }

    /**
     * The datamapper will call this just after the entity is saved
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function afterSave(AccountServiceManagerInterface $sm)
    {
        // Process any temp files or attachments associated with this entity
        $this->processTempFiles($sm->get(FileSystemFactory::class));

        // Set permissions for entity folder (if we have attachments)
        $folderPath = '/System/Entity/' . $this->getValue('entity_id');
        $entityFolder = $sm->get(FileSystemFactory::class)->openFolder($folderPath);
        if ($entityFolder && $entityFolder->getValue('entity_id')) {
            $dacl = $sm->get(DaclLoaderFactory::class)->getForEntity($this);
            if ($dacl) {
                $sm->get(FileSystemFactory::class)->setFolderDacl($entityFolder, $dacl);
            }
        }

        // Call derived extensions
        $this->onAfterSave($sm);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onAfterSave(AccountServiceManagerInterface $sm)
    {
    }

    /**
     * The datamapper will call this just before an entity is purged -- hard delete
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function beforeDeleteHard(AccountServiceManagerInterface $sm)
    {
        // Call derived extensions
        $this->onBeforeDeleteHard($sm);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeDeleteHard(AccountServiceManagerInterface $sm)
    {
    }

    /**
     * The datamapper will call this just after an entity is purged -- hard delete
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function afterDeleteHard(AccountServiceManagerInterface $sm)
    {
        // Call derived extensions
        $this->onAfterDeleteHard($sm);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onAfterDeleteHard(AccountServiceManagerInterface $sm)
    {
    }

    /**
     * Check if a field value changed since created or opened
     *
     * @param string $checkfield The field name
     * @return bool true if it is dirty, false if unchanged
     */
    public function fieldValueChanged($checkfield)
    {
        if (!is_array($this->changelog)) {
            return false;
        }

        foreach ($this->changelog as $fname => $log) {
            if ($fname == $checkfield) {
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
        if (!is_array($this->changelog)) {
            return null;
        }

        if (isset($this->changelog[$checkfield]["oldvalraw"])) {
            return $this->changelog[$checkfield]["oldvalraw"];
        }

        return null;
    }

    /**
     * Reset is dirty indicating no changes need to be saved
     */
    public function resetIsDirty()
    {
        $this->changelog = [];
    }

    /**
     * Check if the object values have changed
     *
     * @return true if object has been edited, false if not
     */
    public function isDirty()
    {
        return (count($this->changelog) > 0) ? true : false;
    }

    /**
     * Get name of this object based on common name fields
     *
     * @return string The name/label of this object
     */
    public function getName()
    {
        if ($this->def->getField("name")) {
            return $this->getValue("name");
        }
        if ($this->def->getField("title")) {
            return $this->getValue("title");
        }
        if ($this->def->getField("subject")) {
            return $this->getValue("subject");
        }
        if ($this->def->getField("full_name")) {
            return $this->getValue("full_name");
        }
        if ($this->def->getField("first_name")) {
            return $this->getValue("first_name");
        }
        if ($this->def->getField("comment")) {
            return $this->getValue("comment");
        }

        return $this->getEntityId();
    }

    /**
     * Try and get a textual description of this entity typically found in fileds named "notes" or "description"
     *
     * @return string The name of this object
     */
    public function getDescription()
    {
        $fields = $this->def->getFields();
        foreach ($fields as $field) {
            if ($field->type == FIELD::TYPE_TEXT) {
                if (
                    $field->name == "description"
                    || $field->name == "notes"
                    || $field->name == "details"
                    || $field->name == "comment"
                ) {
                    return $this->getValue($field->name);
                }
            }
        }

        return "";
    }

    /**
     * Get a textual representation of what changed
     */

    // user, changed status, status value
    public function getChangeLogDescription()
    {
        $hide = [
            "commit_id",
            "uname",
            "ts_updated",
            "ts_entered",
            "revision",
            "num_comments",
            "num_attachments",
            "dacl",
        ];
        $buf = "";
        foreach ($this->changelog as $fname => $log) {
            $oldVal = $log['oldval'];
            $newVal = $log['newval'];

            $field = $this->def->getField($fname);

            // Skip multi key arrays
            if ($field->type == FIELD::TYPE_OBJECT_MULTI || $field->type == FIELD::TYPE_GROUPING_MULTI) {
                continue;
            }

            if ($field->type == FIELD::TYPE_GROUPING || $field->type == FIELD::TYPE_OBJECT) {
                $newVal = $this->getValueName($fname);
            }

            if ($field->type == FIELD::TYPE_BOOL) {
                if ($oldVal == 't') {
                    $oldVal = "Yes";
                }
                if ($oldVal == 'f') {
                    $oldVal = "No";
                }
                if ($newVal == 't') {
                    $newVal = "Yes";
                }
                if ($newVal == 'f') {
                    $newVal = "No";
                }
            }

            if (!in_array($field->name, $hide)) {
                $buf .= $field->title . " was changed ";
                if ($oldVal) {
                    $buf .= "from \"" . $oldVal . "\" ";
                }
                $buf .= "to \"" . $newVal . "\" \n";
            }
        }

        if (!$buf) {
            $buf = "No changes were made";
        }

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
     * Determine if this entity is unsaved / new
     *
     * @return bool true if the entity was previously saved to persistent storage
     */
    public function isSaved(): bool
    {
        return ($this->getValue('revision') > 0);
    }

    /**
     * Set defaults for a field given an event
     *
     * @param string $event The event we are firing
     * @param AntUser $user Optional current user for default variables
     */
    public function setFieldsDefault($event, $user = null)
    {
        $fields = $this->def->getFields();
        foreach ($fields as $fname => $field) {
            // Currently multi-values are not supported for defaults
            if ($field->type == FIELD::TYPE_OBJECT_MULTI || $field->type == FIELD::TYPE_GROUPING_MULTI) {
                continue;
            }

            $val = $this->getValue($fname);
            $new = $field->getDefault($val, $event, $this, $user);

            // If the default was different, then set it
            if ($new != $val) {
                $this->setValue($fname, $new);
            }
        }
    }

    /**
     * Extract object references from text
     *
     * Object references are stored in the form [<obj_type>:<id>:<name>]
     * and can be placed in any text.
     *
     * @param string $text The text to get refrence tags from
     * @return array(array("obj_type"=>type, "id"=>id, "name"=>name))
     */
    public static function getTaggedObjRef($text)
    {
        $taggedReferences = [];

        $matches = [];
        // Extract all [<obj_type>:<id>:<name>] tags from string
        preg_match_all('/\[([a-z_]+)\:(.*?)\:(.*?)\]/u', $text, $matches);

        // $matches = array(array('full_matches'), array('obj_type'), array('id'), array('name'))
        $numMatches = count($matches[0]);

        // Loop through each match index and set the object reference
        for ($i = 0; $i < $numMatches; $i++) {
            // Each variables is parsed into three parts above, 1=obj_type, 2=id, and 3=name
            if ($matches[1][$i] && $matches[2][$i] && $matches[3][$i]) {
                $taggedReferences[] = array(
                    "obj_type" => $matches[1][$i],
                    "entity_id" => $matches[2][$i],
                    "name" => $matches[3][$i],
                );
            }
        }

        return $taggedReferences;
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
        foreach ($fields as $field) {
            if (($field->type == FIELD::TYPE_OBJECT || $field->type === FIELD::TYPE_OBJECT_MULTI) &&
                $field->subtype === ObjectTypes::FILE
            ) {
                // Only process if the value has changed since last time
                if ($this->fieldValueChanged($field->name)) {
                    // Make a files array - if it's an object than an array of one
                    $files = ($field->type == FIELD::TYPE_OBJECT) ?
                        array($this->getValue($field->name)) :
                        $this->getValue($field->name);

                    if (is_array($files)) {
                        foreach ($files as $fid) {
                            $file = $fileSystem->openFileById($fid);

                            // Check to see if the file is a temp file
                            if ($file) {
                                if ($fileSystem->fileIsTemp($file)) {
                                    // Move file to a permanent directory
                                    $objDir = "/System/Entity/" . $this->getValue('entity_id');
                                    $fldr = $fileSystem->openFolder($objDir, true);
                                    if ($fldr && $fldr->getEntityId()) {
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
     * @param bool $deep Flag to indicate if we should also clone object references
     * @return Entity
     */
    public function cloneTo(Entity $toEntity)
    {
        $thisData = $this->toArray();
        $thisData['id'] = null;
        $thisData['entity_id'] = null;
        $thisData['revision'] = 0;
        $toEntity->fromArray($thisData);
    }

    /**
     * Increment the comments counter for this entity
     *
     * @param bool $added If true increment, if false then decrement for deleted comment
     * @param int $numComments Optional manual override to set total number of comments
     * @return bool true on success false on failure
     */
    public function setHasComments($added = true, $numComments = null)
    {
        $cur = $numComments;

        // We used to store a flag in cache, but now we put comment counts in the actual object
        if ($numComments == null) {
            $cur = ($this->getValue('num_comments')) ? (int) $this->getValue('num_comments') : 0;
            if ($added) {
                $cur++;
            } elseif ($cur > 0) {
                $cur--;
            }
        }

        $this->setValue("num_comments", $cur);
    }

    /**
     * Add interested users to the list of followers for this entity
     *
     * Interested users are any users attached via a field where type='object'
     * and subtype='user' or tagged in a text field with [user:<id>:<name>].
     */
    private function updateFollowers()
    {
        $fields = $this->getDefinition()->getfields();

        foreach ($fields as $field) {
            $value = $this->getValue($field->name);

            switch ($field->type) {
                case FIELD::TYPE_TEXT:
                    // Check if any text fields are tagging users
                    $tagged = self::getTaggedObjRef($value);
                    foreach ($tagged as $objRef) {
                        // We need to have a valid uid, before we add it as follower
                        if ($objRef['obj_type'] === 'user') {
                            if (Uuid::isValid($objRef['entity_id'])) {
                                $this->addMultiValue("followers", $objRef['entity_id'], $objRef['name']);
                            }
                        }
                    }
                    break;

                case FIELD::TYPE_OBJECT:
                    // Make sure we have associations added for any object reference
                    if ($value) {
                        if ($field->subtype == ObjectTypes::USER) {
                            $this->addObjReferenceGuid("followers", $field->name, $value, ObjectTypes::USER);
                        }
                    }
                    break;
                case FIELD::TYPE_OBJECT_MULTI:
                    // Check if any fields are referencing users
                    if ($field->subtype == ObjectTypes::USER) {
                        if (is_array($value)) {
                            foreach ($value as $guid) {
                                if ($guid) {
                                    $this->addObjReferenceGuid("followers", $field->name, $guid, ObjectTypes::USER);
                                }
                            }
                        } elseif ($value) {
                            $this->addObjReferenceGuid("followers", $field->name, $value, ObjectTypes::USER);
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Add an object reference guid to a field
     * 
     * @param string $referenceType The type object reference we are adding (associations or followers)
     * @param string $fieldName The name of the field that we will be referencing
     * @param string $value The value of the object reference. This should be the guid of the referenced entity
     * @param string $objType Optional. For backward compatibility, if the provided object reference value is an entity id, 
     *                        then it needs the objType so we can look for the referenced entity.
     */
    private function addObjReferenceGuid(string $referenceType, string $fieldName, string $value, string $objType = "")
    {
        // Get the referenced entity
        $referencedEntity = $this->entityLoader->getByGuid($value);
        if ($referencedEntity) {
            $this->addMultiValue($referenceType, $referencedEntity->getEntityId(), $referencedEntity->getName());
        }
    }

    /**
     * Synchronize followers between this entity and another
     *
     * This is useful for entities such as comments where it is common to
     * add new followers to the comment (through tagging like [user:123:Test])
     * and then make sure the comment also notifies any followers of the entity
     * being commented on (like a task).
     *
     * Note, this does not save changes to either entity so that is something
     * that needs to be done after calling this function.
     *
     * @param EntityInterface $otherEntity The entity we are synchronizing with
     */
    public function syncFollowers(EntityInterface $otherEntity)
    {
        // First copy all followers from the entity we've commented on
        $entityFollowers = $otherEntity->getValue("followers");
        foreach ($entityFollowers as $guid) {
            if (Uuid::isValid($guid)) {
                $userName = $otherEntity->getValueName("followers", $guid);

                // addMultiValue will prevent duplicates so we just add them all
                $this->addMultiValue("followers", $guid, $userName);
            }
        }

        // Now add any new followers from this comment to follow the entity we've commented on
        $commentFollowers = $this->getValue("followers");
        foreach ($commentFollowers as $guid) {
            // We need to have a valid guid, before we add it as follower
            if (Uuid::isValid($guid)) {
                $userName = $this->getValueName("followers", $guid);
                $otherEntity->addMultiValue("followers", $guid, $userName);
            }
        }
    }
}
