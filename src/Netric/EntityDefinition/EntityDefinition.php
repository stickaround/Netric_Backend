<?php

namespace Netric\EntityDefinition;

use Netric\Permissions\Dacl;

/**
 * Define the meta-data for an object type (fields)
 */
class EntityDefinition
{
    /**
     * Unique id for this entity
     *
     * @var string
     */
    public $id = "";

    /**
     * List of fields
     *
     * @var Field[]
     */
    private $fields = [];

    /**
     * The object type name for this definiton
     *
     * @var string
     */
    private $objType = "";

    /**
     * The human readable title of this object type
     *
     * @var string
     */
    public $title = "";

    /**
     * The unique system id of this object type
     *
     * @var string
     */
    private $otid = "";

    /**
     * Is a system object which cannot be deleted
     *
     * We assume it is
     *
     * @var {bool}
     */
    public $system = true;

    /**
     * Hash of last loaded entity definition
     *
     * For system definitions we hash the last loaded definition from the file system
     * to know if we should update it from source code.
     * @var string
     */
    public $systemDefinitionHash = "";

    /**
     * Optional icon name used by the front-end to dynamically load an icon
     *
     * @var {string}
     */
    public $icon = "";

    /**
     * The default activity level to use when working with this object type
     *
     * @var int
     */
    public $defaultActivityLevel = 3;

    /**
     * Saved collection views
     *
     * @var EntityCollection_View[]
     */
    public $collectionViews = [];

    /**
     * Unique name settings string
     *
     * If empty then uname will not be generated automatically and id will be used
     *
     * @var string
     */
    public $unameSettings = "";

    /**
     * The current revision of this definition
     *
     * @var int
     */
    public $revision = 0;

    /**
     * Field to use for the name/title in lists
     *
     * @var string
     */
    public $listTitle = "name";

    /**
     * System forms for UIXML forms
     *
     * @var array('desktop', 'mobile', 'infobox', 'small', 'medium', 'large', 'xlarge')
     */
    private $forms = [];

    /**
     * Is this a private object type where only the owner gets acces
     *
     * @var bool
     */
    public $isPrivate = false;

    /**
     * Reucrrance rules
     *
     * @var array
     */
    public $recurRules = null;

    /**
     * Aggregate object reference fields
     *
     * @var array
     */
    public $aggregates = [];

    /**
     * Define a field reference to inherit permissions from if set like cases and projects
     *
     * @var string
     */
    public $inheritDaclRef;

    /**
     * The application id that owns this object
     *
     * @var string
     */
    public $applicationId = "";

    /**
     * Whether or not we should store revisions of each change
     *
     * @var bool
     */
    public $storeRevisions = true;

    /**
     * Put a cap on the number of objects this entity can have per account
     *
     * @var int
     */
    public $capped = false;

    /**
     * Parent field
     *
     * @var string
     */
    public $parentField = "";

    /**
     * Default access control list for all entities of this type
     *
     * @var null
     */
    private $dacl = null;

    /**
     * Class constructor
     */
    public function __construct($objType)
    {
        $this->objType = trim($objType);

        // Set default fields
        $this->setDefaultFields();
    }

    /**
     * Set unique id
     *
     * @param string $id
     */
    public function setEntityDefinitionId($id)
    {
        $this->id = $id;
    }

    /**
     * Get unique id
     *
     * @return string The saved unique id of this definition
     */
    public function getEntityDefinitionId()
    {
        return $this->id;
    }

    /**
     * Return the object type for this definition
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set form for a medium
     *
     * @param string $xmlForm The UIXML form
     * @param string $medium Either 'default', 'mobile' or 'infobox'
     */
    public function setForm($xmlForm, $medium = 'default')
    {
        $this->forms[$medium] = $xmlForm;
    }

    /**
     * Get a form
     *
     * @param string $medium Either 'default', 'mobile' or 'infobox'
     * @return string|null
     */
    public function getForm($medium = 'default')
    {
        return (isset($this->forms[$medium])) ? $this->forms[$medium] : null;
    }

    /**
     * Return forms
     *
     * @return array
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * Add a field
     *
     * @param Field $field
     * @return bool true on success, false on failure
     */
    public function addField(Field $field)
    {
        if (!$field) {
            return false;
        }

        if (!$field->name || !$field->type) {
            return false;
        }

        // Add field with the name as the index
        $this->fields[$field->name] = $field;

        return true;
    }

    /**
     * Remove a field
     *
     * @param string $fieldName
     * @return bool true on success, false on failure
     */
    public function removeField($fieldName)
    {
        if ($this->fields[$fieldName]) {
            if (!$this->fields[$fieldName]->system) {
                $this->fields[$fieldName] = null;
                return true;
            }
        }

        // Did not meet removal requirements
        return false;
    }

    /**
     * Get a field
     *
     * @param string $fname The name of the field to get
     * @return Field
     */
    public function getField(string $fname)
    {
        if (isset($this->fields[$fname])) {
            return $this->fields[$fname];
        }

        return null;
    }

    /**
     * Get all fields for this object type
     *
     * @param bool $includeRemoved If true, then removed fields will be returned with null values
     * @return Field[]
     */
    public function getFields($includeRemoved = false)
    {
        if ($includeRemoved) {
            return $this->fields;
        }

        $fields = [];
        foreach ($this->fields as $fname => $field) {
            if ($field) {
                $fields[$fname] = $field;
            }
        }

        return $fields;
    }

    /**
     * Get the total number of fields
     *
     * @return int The number of fields defined
     */
    public function getNumFields()
    {
        return count($this->fields);
    }

    /**
     * Get the type of a field by name
     *
     * @param string $name The name of the field to get the type for
     * @return array('type'=>[type], 'subtype'=>[subtype])
     */
    public function getFieldType($name)
    {
        $arr = ["type" => null, "subtype" => null];

        if (isset($this->fields[$name]->type)) {
            $arr['type'] = $this->fields[$name]->type;
        }

        if (isset($this->fields[$name]->subtype)) {
            $arr['subtype'] = $this->fields[$name]->subtype;
        }

        return $arr;
    }

    /**
     * Add a defined collection view
     *
     * @param EntityCollection_View $view The view to add
     */
    public function addView($view)
    {
        $this->collectionViews[] = $view;
    }

    /**
     * Get all views
     *
     * @return EntityCollection_View[]
     */
    public function getViews()
    {
        return $this->collectionViews;
    }

    /**
     * Add an aggregate
     *
     * @param stdCls $agg
     */
    public function addAggregate($agg)
    {
        $this->aggregates[] = $agg;
    }

    /**
     * Build an array of this definition
     *
     * @return array
     */
    public function toArray()
    {
        $ret = [
            "id" => $this->id,
            "obj_type" => $this->objType,
            "title" => $this->title,
            "revision" => $this->revision,
            "capped" => $this->capped,
            "default_activity_level" => $this->defaultActivityLevel,
            "is_private" => $this->isPrivate,
            "recur_rules" => $this->recurRules,
            "inherit_dacl_ref" => $this->inheritDaclRef,
            "uname_settings" => $this->unameSettings,
            "list_title" => $this->listTitle,
            "icon" => $this->icon,
            "system" => $this->system,
            "system_definition_hash" => $this->systemDefinitionHash,
            "application_id" => $this->applicationId,
            "fields" => [],
            "aggregates" => [],
            "dacl" => '',
            "store_revisions" => $this->storeRevisions,
            "parent_field" => $this->parentField,
        ];

        // Add fields for this object definition
        foreach ($this->fields as $fname => $field) {
            // Make sure the the $field is not a deleted field
            if ($field != null) {
                $ret['fields'][$fname] = $field->toArray();
            }
        }

        $views = $this->getViews();
        $ret['views'] = [];
        foreach ($views as $view) {
            $ret['views'][] = $view->toArray();
        }

        foreach ($this->aggregates as $agg) {
            $ret['aggregates'][] = [
                'type' => $agg->type,
                'calc_field' => $agg->calcField,
                'obj_field_to_update' => $agg->refField,
                'ref_obj_update' => $agg->field,
            ];
        }

        if ($this->getDacl()) {
            $ret['dacl'] = $this->getDacl()->toArray();
        }

        return $ret;
    }

    /**
     * Load from an associative array
     *
     * @param array $data The data to load
     * @return bool true on success, false on failure
     */
    public function fromArray($data)
    {
        if (!is_array($data)) {
            return false;
        }

        if (isset($data['revision'])) {
            $this->revision = $data['revision'];
        }

        if (isset($data['fields'])) {
            foreach ($data['fields'] as $name => $fdef) {
                $field = new Field();
                $field->name = $name;
                $field->fromArray($fdef);
                $this->addField($field);
            }
        }

        if (isset($data['aggregates'])) {
            foreach ($data['aggregates'] as $name => $aggData) {
                $agg = new \stdClass();
                $agg->field = $aggData['ref_obj_update'];
                $agg->refField = $aggData['obj_field_to_update'];
                $agg->calcField = $aggData['calc_field'];
                $agg->type = $aggData['type'];
                $this->addAggregate($agg);
            }
        }

        if (isset($data['deleted_fields'])) {
            foreach ($data['deleted_fields'] as $fieldName) {
                $this->removeField($fieldName);
            }
        }

        if (isset($data['system'])) {
            $this->system = $data['system'];
        }

        if (isset($data['system_definition_hash'])) {
            $this->systemDefinitionHash = $data['system_definition_hash'];
        }

        if (isset($data['capped']) && is_numeric($data['capped'])) {
            $this->capped = $data['capped'];
        }

        if (isset($data['default_activity_level'])) {
            $this->defaultActivityLevel = $data['default_activity_level'];
        }

        if (isset($data['is_private'])) {
            $this->isPrivate = $data['is_private'];
        }

        if (isset($data['recur_rules'])) {
            $this->recurRules = $data['recur_rules'];
        }

        if (isset($data['inherit_dacl_ref'])) {
            $this->inheritDaclRef = $data['inherit_dacl_ref'];
        }

        if (isset($data['parent_field'])) {
            $this->parentField = $data['parent_field'];
        }

        if (isset($data['uname_settings'])) {
            $this->unameSettings = $data['uname_settings'];
        }

        if (isset($data['list_title'])) {
            $this->listTitle = $data['list_title'];
        }

        if (isset($data['icon'])) {
            $this->icon = $data['icon'];
        }

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        if (isset($data['title'])) {
            $this->title = $data['title'];
        }

        if (isset($data['application_id'])) {
            $this->applicationId = $data['application_id'];
        }

        if (isset($data['store_revisions'])) {
            $this->storeRevisions = $data['store_revisions'];
        }

        // Check if dacl is not empty
        if (isset($data['dacl']) && is_array($data['dacl'])) {
            $this->setDacl(new Dacl($data['dacl']));
        }

        return true;
    }

    /**
     * Set common default fields for all objects
     *
     * @return array
     */
    private function setDefaultFields()
    {
        // Add default fields that are common to all objects
        $defaultFields = require(__DIR__ . '/../../../data/entity_definitions/default.php');

        foreach ($defaultFields as $fname => $fdef) {
            $field = new Field();
            $field->name = $fname;
            $field->system = true;
            $field->fromArray($fdef);
            $this->addField($field);
        }
    }

    /**
     * Get the title of this object type
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the title of this object type
     *
     * The title is the human readable short description
     * of the object type and always has an upper case first letter.
     *
     * @param string $title The title of this object type
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Check if this is a private entity type
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * Set discretionary access control list
     *
     * @param Dacl $dacl
     */
    public function setDacl(Dacl $dacl = null)
    {
        $this->dacl = $dacl;
    }

    /**
     * Get the discretionary access control list for this object type
     *
     * @return Dacl
     */
    public function getDacl()
    {
        return $this->dacl;
    }

    /**
     * Set whether or not this is a system entity (can't be deleted)
     *
     * @param bool $isSystem
     */
    public function setSystem($isSystem)
    {
        $this->system = $isSystem;
    }

    /**
     * Get flag that indicates if this is a system entity or not (can't be deleted)
     */
    public function getSystem()
    {
        return $this->system;
    }
}
