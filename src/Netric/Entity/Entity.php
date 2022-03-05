<?php

namespace Netric\Entity;

use Netric\ServiceManager\ServiceLocatorInterface;
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
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityGroupings\GroupingLoader;

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
     * Grouping loader used to get groupings data
     *
     * @var GroupingLoader
     */
    private GroupingLoader $groupingLoader;

    /**
     * Entity loader used to get referenced entities
     *
     * @var EntityLoader
     */
    private EntityLoader $entityLoader;

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader Loader used to get entity followers and entity owner details
     * @param GroupingLoader $groupingLoader Loader used to get entity group information
     */
    public function __construct(
        EntityDefinition $def,
        EntityLoader $entityLoader,
        GroupingLoader $groupingLoader
    ) {
        $this->def = $def;
        $this->objType = $def->getObjType();
        $this->entityLoader = $entityLoader;
        $this->groupingLoader = $groupingLoader;
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
     * Get account id for this entity
     *
     * @return string
     */
    public function getAccountId(): string
    {
        if (!empty($this->getValue('account_id'))) {
            return $this->getValue('account_id');
        }

        return '';
    }

    /**
     * Get definition
     *
     * @return EntityDefinition
     */
    public function getDefinition(): EntityDefinition
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
                return [$values => $this->fkeysValues[$strName][$values]];
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

                        $value = [$value];
                    }
            }
        }

        $this->values[$strName] = $value;

        if ($valueName) {
            if (is_array($valueName)) {
                $this->fkeysValues[$strName] = $valueName;
            } elseif (is_string($value) || is_numeric($value)) {
                $this->fkeysValues[$strName] = [(string) $value => $valueName];
            } else {
                throw new \InvalidArgumentException(
                    "Invalid value name for object or fkey: " .
                        var_export($value, true)
                );
            }
        }

        // Check if this field update changed uname
        if (!empty($this->getDefinition()->unameSettings)) {
            $this->updateUnameIfNeeded($strName);
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
    public function getOwnerId()
    {
        $ownerGuid = '';

        if ($this->getValue('owner_id')) {
            $ownerGuid = $this->getValue('owner_id');
        } elseif ($this->getValue('creator_id')) {
            $ownerGuid = $this->getValue('creator_id');
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

            if ($this->getValueNames($strName, $value)) {
                $newval = $this->getValueNames($strName, $value);
            }

            $this->changelog[$strName] = [
                "field" => $strName,
                "oldval" => $oldval,
                "newval" => $newval,
                "oldvalraw" => $oldvalraw,
                "newvalraw" => $newvalraw
            ];
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
                    $data[$fname . "_fval"] = [$data[$fname . "_fval"]];
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
                if (($field->type == Field::TYPE_OBJECT_MULTI || $field->type == Field::TYPE_GROUPING_MULTI)) {
                    $this->clearMultiValues($fname);
                }

                $valName = (isset($valNames[$value])) ? $valNames[$value] : null;
                $this->setValue($fname, $value, $valName);
            }
        }

        // Make sure account_id is set
        if (empty($this->getValue('account_id')) && !empty($this->getDefinition()->getAccountId())) {
            $this->setValue('account_id', $this->getDefinition()->getAccountId());
        }

        // If the recurrence pattern data was passed then load it
        if (isset($data['recurrence_pattern']) && is_array($data['recurrence_pattern'])) {
            $this->recurrencePattern = new RecurrencePattern($this->getAccountId());
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
        // Handle any pre-processing like default values
        $this->onBeforeToArray();

        $data = ["obj_type" => $this->objType];

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
     * Get all values with applied_name in it and return them as an array
     *
     * @return array Associative array of all fields in array(field_name=>value with applied_name=>value) format
     */
    public function toArrayWithApplied(UserEntity $user)
    {
        $entityData = $this->toArray();
        $entityData['applied_name'] = $this->getName($user);
        $entityData['applied_icon'] = $this->getIconName();
        $entityData['applied_description'] = $this->getDescription();

        return $entityData;
    }

    /**
     * Special function used to get data visible to users who have no view permission
     *
     * @return array Associative array of select fields in array(field_name=>value) format
     */
    public function toArrayWithNoPermissions()
    {
        // Handle any pre-processing like default values
        $this->onBeforeToArray();

        $nameTitleLabelField = $this->getNameTitleLabelField();
        $data = [
            "obj_type" => $this->objType,
            "entity_id" => $this->getEntityId(),
            $nameTitleLabelField => $this->getName(),
        ];

        if ($this->def->getField('image_id')) {
            $data['image_id'] = $this->getValue('image_id');
        }

        // If this is a recurring object, indicate if this is an exception
        if ($this->def->recurRules) {
            // If the field_recur_id is set then this is part of a series
            if ($this->getValue($this->def->recurRules['field_recur_id'])) {
                $data['recurrence_exception'] = $this->isRecurrenceException;
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
     * @param ServiceLocatorInterface $serviceLocator Service manager used to load supporting services
     * @param UserEntity $user The user that is acting on this entity
     */
    public function beforeSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        // Update or add followers based on changes to fields
        $this->updateFollowers();

        // If the owner of this entity is the current user, then set the f_seen value to true
        if ($user->getEntityId() == $this->getOwnerId() && $this->getObjType() !== ObjectTypes::NOTIFICATION) {
            $this->setValue("f_seen", true);
        }

        // Call derived extensions
        $this->onBeforeSave($serviceLocator, $user);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator Service manager used to load supporting services
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
    }

    /**
     * The datamapper will call this just after the entity is saved
     *
     * @param ServiceLocatorInterface $serviceLocator Service manager used to load supporting services
     * @param UserEntity $user The user that is acting on this entity
     */
    public function afterSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        $daclLoader = $serviceLocator->get(DaclLoaderFactory::class);
        $fileSystem = $serviceLocator->get(FileSystemFactory::class);

        // If we are working with an entity that is not a folder or file, then handle attachments
        if (
            $this->getDefinition()->getObjType() != ObjectTypes::FOLDER &&
            $this->getDefinition()->getObjType() != ObjectTypes::FILE
        ) {
            // Process any temp files or attachments associated with this entity if it is not the root folder
            $this->processTempFiles($fileSystem, $user);

            // Set permissions for entity folder (if we have attachments)
            $entityFolder = $fileSystem->getOrCreateEntityFolder($this, $user);
            if ($entityFolder) {
                $dacl = $daclLoader->getForEntity($this, $user);

                if ($dacl && is_array($this->getValue('followers'))) {
                    // Make sure all interested users are given permission to view
                    $followers = $this->getValue('followers');
                    foreach ($followers as $followerId) {
                        $dacl->allowUser($followerId);
                    }

                    $fileSystem->setFolderDacl($entityFolder, $dacl, $user);
                }

                // Copy owner
                // if ($this->getOwnerId() && $this->getOwnerId() !== $entityFolder->getOwnerId()) {
                //     $fileSystem->setFolderOwner($entityFolder, $this->getOwnerId(), $user);
                // }
            }
        }

        // Call derived extensions
        $this->onAfterSave($serviceLocator, $user);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator Service manager used to load supporting services
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onAfterSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
    }

    /**
     * The datamapper will call this just before an entity is purged -- hard delete
     *
     * @param ServiceLocatorInterface $serviceLocator Service manager used to load supporting services
     * @param UserEntity $user The user that is acting on this entity
     */
    public function beforeDeleteHard(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        // Call derived extensions
        $this->onBeforeDeleteHard($serviceLocator, $user);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator Service manager used to load supporting services
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeDeleteHard(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
    }

    /**
     * The datamapper will call this just after an entity is purged -- hard delete
     *
     * @param ServiceLocatorInterface $serviceLocator Service manager used to load supporting services
     * @param UserEntity $user The user that is acting on this entity
     */
    public function afterDeleteHard(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        // Call derived extensions
        $this->onAfterDeleteHard($serviceLocator, $user);
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator Service manager used to load supporting services
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onAfterDeleteHard(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
    }

    /**
     * This function is called just before we export entity as data
     */
    public function onBeforeToArray(): void
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
     * Get human readable name of this object based on common name fields
     *
     * @param UserEntity $user Optional. The user that is acting on this entity
     * @return string The name/label of this object
     */
    public function getName(UserEntity $user = null)
    {
        // If $user is defined, then check if there is a custom name generated for this entity
        if ($user && $this->onGetName($user)) {
            return $this->onGetName($user);
        }
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
        if ($this->def->getField("body")) {
            // This is for chat messages, since they only have body field
            return $this->getValue("body");
        }

        return $this->getEntityId();
    }

    /**
     * Call derived extensions
     * 
     * @param UserEntity $user Optional. The user that is acting on this entity
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onGetName(UserEntity $user = null)
    {
    }

    /**
     * Get Icon name of this object based on common name icon
     *
     * @return string The Icon name of this object
     */
    public function getIconName(): string
    {
        if ($this->def->getField("icon")) {
            return $this->getValue("icon");
        }
        return '';
    }

    /**
     * Get the field of this object that it uses to display as its name/title/subject/label
     * 
     * @return string
     */
    private function getNameTitleLabelField()
    {
        if ($this->def->getField("name")) {
            return "name";
        }
        if ($this->def->getField("title")) {
            return "title";
        }
        if ($this->def->getField("subject")) {
            return "subject";
        }
        if ($this->def->getField("full_name")) {
            return "full_name";
        }
        if ($this->def->getField("first_name")) {
            return "first_name";
        }
        if ($this->def->getField("comment")) {
            return "comment";
        }

        return "entity_id";
    }


    /**
     * Try and get a textual description of this entity typically found in fileds named "notes" or "description"
     *
     * @return string The name of this object
     */
    public function getDescription(): string
    {
        $fields = $this->def->getFields();
        foreach ($fields as $field) {
            if ($field->type == Field::TYPE_TEXT && $this->getValue($field->name)) {
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
    public function getChangeLogDescription(): string
    {
        $hide = [
            "commit_id",
            "uname",
            "ts_updated",
            "ts_entered",
            "date_entered", // legacy
            "revision",
            "f_seen",
            "num_comments",
            "comments", // Ignore because comments make their own notice
            "activity",
            "entity_id",
            "seen_by",
            "num_attachments",
            "dacl",
            "sort_order",
            "account_id",
            "creator_id",
            "path", // legacy
            "uname", // system, no need to give to user
            "last_active", // This would create tons of unnecessary updates
            "last_login", // No need to track logins
        ];
        $buf = "";
        foreach ($this->changelog as $fname => $log) {
            $oldVal = $log['oldval'];
            $newVal = $log['newval'];

            $field = $this->def->getField($fname);

            // Skip multi key arrays
            if ($field == null || $field->type == Field::TYPE_OBJECT_MULTI || $field->type == Field::TYPE_GROUPING_MULTI) {
                continue;
            }

            if ($field->type == Field::TYPE_GROUPING || $field->type == Field::TYPE_OBJECT) {
                $newVal = $this->getValueName($fname);
            }

            if ($field->type == Field::TYPE_BOOL) {
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

        return $buf;
    }

    /**
     * Check if the archived/deleted flag is set for this entity but it still exists
     *
     * @return bool
     */
    public function isArchived(): bool
    {
        return ($this->getValue("f_deleted") === true);
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
            $val = $this->getValue($fname);
            $new = $field->getDefault($val, $event, $this, $user);

            // If the default was different, then set it
            if (!empty($new) && $new != $val) {
                if ($field->type == Field::TYPE_OBJECT_MULTI || $field->type == Field::TYPE_GROUPING_MULTI) {
                    $this->addMultiValue($fname, $new);
                } else {
                    // Set value
                    $this->setValue($fname, $new);
                }
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
                $taggedReferences[] = [
                    "obj_type" => $matches[1][$i],
                    "entity_id" => $matches[2][$i],
                    "name" => $matches[3][$i],
                ];
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
     * @param UserEntity $user The user that owns the temp files
     */
    public function processTempFiles(FileSystem $fileSystem, UserEntity $user)
    {
        $fields = $this->def->getFields();
        foreach ($fields as $field) {
            if (($field->type == Field::TYPE_OBJECT || $field->type === Field::TYPE_OBJECT_MULTI) &&
                $field->subtype === ObjectTypes::FILE
            ) {
                // Only process if the value has changed since last time
                if ($this->fieldValueChanged($field->name)) {
                    // Make a files array - if it's an object than an array of one
                    $files = ($field->type == Field::TYPE_OBJECT) ?
                        [$this->getValue($field->name)] :
                        $this->getValue($field->name);

                    if (is_array($files)) {
                        // Make sure we remove empty values in the entity files.
                        $entityFiles = array_values(array_filter($files));

                        foreach ($entityFiles as $fid) {
                            $file = $fileSystem->openFileById($fid, $user);

                            // Check to see if the file is a temp file
                            if ($file) {
                                if ($fileSystem->fileIsTemp($file, $user)) {
                                    // Move file to a permanent directory
                                    $entityAttachmentFolder = $fileSystem->getOrCreateEntityFolder($this, $user);
                                    $fileSystem->moveFile($file, $entityAttachmentFolder, $user);
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
        // Set fields we should avoid cloning
        $thisData['id'] = null;
        $thisData['entity_id'] = null;
        $thisData['revision'] = 0;
        $thisData['ts_created'] = null;
        $thisData['uname'] = null; // We cannot have a collision of unique names
        // ts_executed is used in recurring entities sometimes for reminders,
        // notifications, or jobs and should always default to null
        $thisData['ts_executed'] = null;
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
     * Increment the reaction counter for this entity
     *
     * @param bool $added If true increment, if false then decrement for deleted reaction
     * @param int $numReactions Optional manual override to set total number of reactions
     * @return bool true on success false on failure
     */
    public function setHasReaction($added = true, $numReactions = null)
    {
        $cur = $numReactions;

        // We used to store a flag in cache, but now we put reaction counts in the actual object
        if ($numReactions == null) {
            $cur = ($this->getValue('num_reactions')) ? (int) $this->getValue('num_reactions') : 0;
            if ($added) {
                $cur++;
            } elseif ($cur > 0) {
                $cur--;
            }
        }

        $this->setValue("num_reactions", $cur);
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
            $valueName = $this->getValueName($field->name, $value);

            switch ($field->type) {
                case Field::TYPE_TEXT:
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

                case Field::TYPE_OBJECT:
                    // Make sure we have associations added for any object reference
                    if ($value) {
                        if ($field->subtype == ObjectTypes::USER) {
                            $this->addMultiValue("followers", $value, $valueName);
                        }
                    }
                    break;
                case Field::TYPE_OBJECT_MULTI:
                    // Check if any fields are referencing users
                    if ($field->subtype == ObjectTypes::USER) {
                        if (is_array($value)) {
                            foreach ($value as $guid) {
                                if ($guid) {
                                    $this->addMultiValue("followers", $guid);
                                }
                            }
                        } elseif ($value) {
                            $this->addMultiValue("followers", $value, $valueName);
                        }
                    }
                    break;
            }
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

    /**
     * Allow derrived classes to get the entity loader dependency
     *
     * @return EntityLoader
     */
    protected function getEntityLoader(): EntityLoader
    {
        return $this->entityLoader;
    }

    /**
     * Allow derrived classes to get the grouping loader dependency
     *
     * @return GroupingLoader
     */
    protected function getGroupingLoader(): GroupingLoader
    {
        return $this->groupingLoader;
    }

    /**
     * Load the name for a referenced ID - both grouping and entity
     *
     * @param string $fieldName
     * @param string $groupOrEntityId
     * @return string
     */
    public function getNameForReferencedId(string $fieldName, string $groupOrEntityId): string
    {
        $field = $this->getDefinition()->getField($fieldName);

        if ($field->type === Field::TYPE_GROUPING || $field->type === Field::TYPE_GROUPING_MULTI) {
            $groupings = $this->getGroupingLoader()->get(
                $this->getDefinition()->getObjType() . '/' . $fieldName,
                $this->getAccountId()
            );

            $group = $groupings->getByGuid($groupOrEntityId);
            if ($group) {
                return $group->getName();
            }
        }

        if ($field->type === Field::TYPE_OBJECT || $field->type === Field::TYPE_OBJECT_MULTI) {
            if ($groupOrEntityId === $this->getEntityId()) {
                return $this->getName();
            }

            $refEntity = $this->getEntityLoader()->getEntityById($groupOrEntityId, $this->getAccountId());
            if ($refEntity && $refEntity->getName()) {
                return $refEntity->getName();
            }
        }

        // Name not found
        return "";
    }

    /**
     * Unames are auto-generated often from settings of field names
     *
     * @param string $fieldName
     * @return void
     */
    private function updateUnameIfNeeded(string $fieldName): void
    {
        $unameSettings = $this->getDefinition()->unameSettings;

        // If there are no settings than uname will just be an auto-generated field
        if (empty($unameSettings)) {
            return;
        }

        $unameFields = explode(":", $unameSettings);

        // If the $feidlName is not in the uname settings array, do nothing
        $fieldIndex = array_search($fieldName, $unameFields);
        if ($fieldIndex === false) {
            return;
        }

        $unameValues = explode(':', $this->getValue('uname'));


        // Now escape the uname field to a uri friendly name
        // since we use this for URLs and usernames in many cases
        $fieldValue = $this->getValue($fieldName);
        $fieldValue = strtolower($fieldValue);
        $fieldValue = str_replace(" ", "-", $fieldValue);
        $fieldValue = str_replace("&", "_and_", $fieldValue);
        $fieldValue = str_replace("@", "_at_", $fieldValue);
        $fieldValue = preg_replace('/[^A-Za-z0-9:._-]/', '', $fieldValue);
        $unameValues[$fieldIndex] = $fieldValue;

        // Make sure each part of the namespaced field is initialized
        // so empty values show up like "::end" but never "::" if all empty
        $unameToSet = [];
        $isUnameEmpty = true;
        for ($i = 0; $i < count($unameFields); $i++) {
            $unameToSet[$i] = isset($unameValues[$i]) ? $unameValues[$i] : "";
            if ($unameToSet[$i] != "") {
                $isUnameEmpty = false;
            }
        }

        if ($isUnameEmpty === false) {
            $this->setValue("uname", implode(":", $unameToSet));
        }
    }
}
