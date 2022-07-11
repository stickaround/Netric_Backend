<?php

/**
 * A saved (or defined by file) view for an entity browser
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\BrowserView;

use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Where;
use Netric\EntityQuery\OrderBy;

/**
 * Represent a single browser view
 *
 * @package Netric\Entity\BrowserView
 */
class BrowserView
{
    /**
     * Owner id if this view is owned by an individual user
     *
     * @var string
     */
    private $ownerId = '';

    /**
     * Set if this view is owned by a team
     *
     * @var string
     */
    private $teamId = '';

    /**
     * Unique id of this view if saved
     *
     * @var string
     */
    private $id = '';

    /**
     * Name describing this view
     *
     * @var string
     */
    private $name = '';

    /**
     * Full description of the view
     *
     * @var string
     */
    private $description = '';

    /**
     * Which fields to display in a table view
     *
     * @var array
     */
    private $tableColumns = [];

    /**
     * The view fields that will be used a quick filters
     *
     * @var string[]
     */
    private $filterFields = [];

    /**
     * True if this is the default view for the given user
     *
     * @var bool
     */
    private $default = false;

    /**
     * This is a system view which cannot be modified or deleted
     *
     * @var bool
     */
    private $system = false;

    /**
     * Array of order by fields
     *
     * @var EntityQuery\OrderBy[]
     */
    private $orderBy = [];

    /**
     * Array of where conditions
     *
     * @var EntityQuery\Where[]
     */
    private $wheres = [];

    /**
     * The type of object this view is describing
     *
     * @var string
     */
    private $objType = '';

    /**
     * Flag that will determine if we will set a group by based on the first sort order field
     *
     * @var boolean
     */
    private $groupFirstOrderBy = false;

    /**
     * Convert the data for this view to an array
     *
     * @return array
     */
    public function toArray($userid = null)
    {
        $ret = [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "system" => $this->system,
            "default" => $this->default,
            "owner_id" => $this->ownerId,
            "team_id" => $this->teamId,
            "obj_type" => $this->objType,
            "table_columns" => [],
            "conditions" => [],
            "order_by" => [],
            "group_first_order_by" => $this->groupFirstOrderBy,
            "filter_fields" => $this->filterFields
        ];

        // Add view fields
        foreach ($this->tableColumns as $field) {
            $ret['table_columns'][] = $field;
        }

        // Add conditions
        foreach ($this->wheres as $where) {
            $ret['conditions'][] = $where->toArray();
        }

        // Add sort order
        foreach ($this->orderBy as $sort) {
            $ret['order_by'][] = $sort->toArray();
        }

        return $ret;
    }

    /**
     * Load this view from an associative array
     *
     * @param array $data
     */
    public function fromArray(array $data)
    {
        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        if (isset($data['name'])) {
            $this->name = $data['name'];
        }

        if (isset($data['obj_type'])) {
            $this->objType = $data['obj_type'];
        }

        if (isset($data['description'])) {
            $this->description = $data['description'];
        }

        if (isset($data['system']) && is_bool($data['system'])) {
            $this->system = $data['system'];
        }

        if (isset($data['default']) && is_bool($data['default'])) {
            $this->default = $data['default'];
        }

        if (isset($data['f_default']) && is_bool($data['f_default'])) {
            $this->default = $data['f_default'];
        }

        if (isset($data['team_id'])) {
            $this->setTeamId($data['team_id']);
        }

        if (isset($data['group_first_order_by'])) {
            $this->groupFirstOrderBy = $data['group_first_order_by'];
        }

        if (isset($data['filter_fields'])) {
            $this->filterFields = $data['filter_fields'];
        }

        // We put this last in case they set both team and user then user will override team
        if (isset($data['owner_id'])) {
            $this->setOwnerId($data['owner_id']);
        }

        if (isset($data['table_columns']) && is_array($data['table_columns'])) {
            foreach ($data['table_columns'] as $colField) {
                $this->tableColumns[] = $colField;
            }
        }

        if (isset($data['conditions']) && is_array($data['conditions'])) {
            foreach ($data['conditions'] as $cond) {
                $where = new Where($cond['field_name']);
                $where->fromArray($cond);
                $this->wheres[] = $where;
            }
        }

        if (isset($data['order_by']) && is_array($data['order_by'])) {
            foreach ($data['order_by'] as $sortData) {
                $orBy = new OrderBy($sortData['field_name'], $sortData['direction']);
                $this->orderBy[] = $orBy;
            }
        }
    }

    /**
     * Set the BrowserView id
     *
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the id of the BrowserView if saved in DB
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the name of this view
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the full description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get owner global id if set just for a user
     *
     * @return string
     */
    public function getOwnerId(): string
    {
        return $this->ownerId;
    }

    /**
     * Set the owner global id for this browser view
     *
     * If the userId is set, then this will clear the teamId
     * since only one can be set at a time.
     *
     * @param string $ownerId Unique user global id for this view
     */
    public function setOwnerId(string $ownerId)
    {
        if ($this->getTeamId()) {
            $this->teamId = null;
        }

        $this->ownerId = $ownerId;
    }

    /**
     * Get team id if only set for a team
     *
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }

    /**
     * Set the team id
     *
     * If the teamId is set, then this will clear the userId
     * since only one can be set at a time.
     *
     * @param string $teamId Unique team ID for this view
     */
    public function setTeamId(string $teamId)
    {
        if ($this->getOwnerId()) {
            $this->userId = null;
        }

        $this->teamId = $teamId;
    }

    /**
     * Get the table colums array
     *
     * @return array
     */
    public function getTableColumns()
    {
        return $this->tableColumns;
    }

    /**
     * Get the object type this view is describing
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set the object type
     *
     * @param $objType
     */
    public function setObjType($objType)
    {
        $this->objType = $objType;
    }

    /**
     * Check if this is set as a default view
     *
     * @return bool true if this should be displayed by default
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Check if this is a system view (from a file)
     *
     * @return bool true if this is not a db view (cannot be changed)
     */
    public function isSystem()
    {
        return $this->system;
    }

    /**
     * Set this to a system view which means it cannot be saved or changed
     *
     * @param bool $isSystem
     */
    public function setSystem($isSystem = false)
    {
        $this->system = $isSystem;
    }

    /**
     * Get conditions array
     *
     * @return EntityQuery\Where[]
     */
    public function getConditions()
    {
        return $this->wheres;
    }

    /**
     * Get order by array
     *
     * @return EntityQuery\OrderBy[]
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }
}
