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
     * The global unique identifier of this group
     *
     * @var string
     */
    public $groupId = "";

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
    public $children = [];

    /**
     * Dirty flag set when changes are made
     *
     * @var bool
     */
    private $dirty = false;

    /**
     * The user of this group
     *
     * @var int
     */
    private $userId = 0;

    /**
     * The path of this group
     *
     * @var string
     */
    private $path = "";

    /**
     * Convert class properties to an associative array
     *
     * @return array
     */
    public function toArray()
    {
        $data = [
            "group_id" => $this->getGroupId(),
            "name" => $this->getName(),
            "f_system" => $this->isSystem,
            "parent_id" => $this->parentId,
            "color" => $this->color,
            "sort_order" => $this->sortOrder,
            "commit_id" => $this->commitId
        ];

        if ($this->userId) {
            $data["user_id"] = $this->userId;
        }

        if ($this->path) {
            $data["path"] = $this->path;
        }

        return $data;
    }

    /**
     * Import the group data into the class properties
     *
     * @return array
     */
    public function fromArray($data)
    {
        if (isset($data['group_id'])) {
            $this->setGroupId($data['group_id']);
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

        if (isset($data['user_id'])) {
            $this->userId = $data['user_id'];
        }

        if (isset($data['path'])) {
            $this->path = $data['path'];
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
            case "group_id":
            case "groupId":
                $this->setGroupId($fval);
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
            case "groupId":
            case "group_id":
                return $this->getGroupId();
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
        if (!$this->groupId) {
            return true;
        }

        return $this->dirty;
    }

    /**
     * Get the id of this group
     *
     * @return string
     */
    public function getGroupId(): string
    {
        return $this->groupId;
    }

    /**
     * Set the id of this group
     *
     * @param string $groupId
     * @return void
     */
    public function setGroupId(string $groupId): void
    {
        $this->groupId = $groupId;
    }

    /**
     * Get the name of this group
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name of this group
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the commit id if this group was saved
     *
     * @return int
     */
    public function getCommitId(): int
    {
        return $this->commitId;
    }

    /**
     * Set the commit id of the last save on this group
     *
     * @param int $commitId
     */
    public function setCommitId(int $commitId): void
    {
        $this->commitId = $commitId;
    }
}
