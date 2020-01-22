<?php
namespace Netric\EntityGroupings;

/**
 * Base grouping entry
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 */
class Group
{
    /**
     * Unique id of this grouping
     *
     * @var string
     */
    public $id = "";

    /**
     * The global unique identifier of this group
     * 
     * @var uuid
     */
    public $guid = "";

    /**
     * The title of this grouping
     *
     * @var string
     */
    public $name = "";

    /**
     * Deprecated - Marl 01/20/20
     * Grouping is heiarchial with a parent id
     *
     * @var bool
     */
    public $isHeiarch = false;

    /**
     * Grouping is system generated and cannot be modified by user
     *
     * @var bool
     */
    public $isSystem = false;

    /**
     * Deprecated - Marl 01/20/20
     * If heiarchial then parent may be used to define parent-child groupings
     * 
     * @var int
     */
    public $parentId = null;

    /**
     * Optional hex color
     *
     * @var string
     */
    public $color = "";

    /**
     * The sort order of this grouping if not by name
     *
     * @var int
     */
    public $sortOrder = 0;

    /**
     * The last save commit id
     *
     * @var int
     */
    public $commitId = 0;

    /**
     * Deprecated - Marl 01/20/20
     * Children
     *
     * @var Group[]
     */
    public $children = array();

    /**
     * Dirty flag set when changes are made
     *
     * @var bool
     */
    private $dirty = false;

    /**
     * Convert class properties to an associative array
     *
     * @return array
     */
    public function toArray()
    {
        $data = array(
            "id" => $this->id,
            "guid" => $this->guid,
            "name" => $this->name,
            "f_system" => $this->isSystem,
            "parent_id" => $this->parentId,
            "color" => $this->color,
            "sort_order" => $this->sortOrder,
            "commit_id" => $this->commitId,
        );

        return $data;
    }

    /**
     * Import the group data into the class properties
     *
     * @return array
     */
    public function fromArray($data)
    {
        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        if (isset($data['guid'])) {
            $this->guid = $data['guid'];
        }

        if (isset($data['name'])) {
            $this->name = $data['name'];
        }

        if (isset($data['color'])) {
            $this->color = $data['color'];
        }

        if (isset($data['parent_id'])) {
            $this->parentId = $data['parent_id'];
        }

        if (isset($data['sort_order'])) {
            $this->sortOrder = $data['sort_order'];
        }

        if (isset($data['is_heiarch'])) {
            $this->isHeiarch = $data['is_heiarch'];
        }

        if (isset($data['commit_id'])) {
            $this->commitId = $data['commit_id'];
        }

        if (isset($data['f_system'])) {
            $this->isSystem = $data['f_system'];
        }

        // Inicate this group has been changed
        $this->setDirty(true);
    }

    /**
     * Set a property value by name
     *
     * @param string $fname The property or field name to set
     * @param string $fval The value of the property
     */
    public function setValue($fname, $fval)
    {
        switch ($fname) {
            case "id":
                $this->id = $fval;
                break;
            case "name":
                $this->name = $fval;
                break;
            case "isHeiarch":
                $this->isHeiarch = $fval;
                break;
            case "parentId":
                $this->parentId = $fval;
                break;
            case "color":
                $this->color = $fval;
                break;
            case "sortOrder":
                $this->sortOrder = $fval;
                break;
            case "commit_id":
            case "commitId":
                $this->commitId = $fval;
                break;
        }

        // Inicate this group has been changed
        $this->setDirty(true);
    }

    /**
     * Set a property value by name
     *
     * @param string $fname The property or field name to set
     * @param string $fval The value of the property
     */
    public function getValue($fname)
    {
        switch ($fname) {
            case "id":
                return $this->id;
            case "name":
                return $this->name;
            case "isHeiarch":
                return $this->isHeiarch;
            case "parentId":
                return $this->parentId;
            case "color":
                return $this->color;
            case "sortOrder":
                return $this->sortOrder;
            case "commit_id":
            case "commitId":
                return $this->commitId;
        }

        return "";
    }

    /**
     * Set an undefined property in the filtered fields
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->filterFields[$name] = $value;
    }

    /**
     * Get an undefined property
     *
     * @param string $name
     * @return string|null
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->filterFields)) {
            return $this->filterFields[$name];
        }

        return null;
    }

    /**
     * Check if an undefined property is set in the filterFields propery
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->filterFields[$name]);
    }

    /**
     * @Deprecated - Marl 01/14/2020
     * Get filtered value
     *
     * @param string $name
     * @return string
     */
    public function getFilteredVal($name)
    {
        if (isset($this->filterFields[$name])) {
            return $this->filterFields[$name];
        } else {
            return "";
        }
    }

    /**
     * Set dirty flag
     *
     * @param bool $isDirty True if we made changes, false if not
     */
    public function setDirty($isDirty = true)
    {
        $this->dirty = $isDirty;
    }

    /**
     * Determine if changes have been made to this grouping since it was loaded
     *
     * @return bool true if changes were made, false if no changes
     */
    public function isDirty()
    {
        if (!$this->id) {
            return true;
        }

        return $this->dirty;
    }
}
