<?php

namespace Netric\EntityGroupings;

use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperInterface;
use Ramsey\Uuid\Uuid;

/**
 * Manage collection of groupings for an entity
 */
class EntityGroupings
{
    /**
     * Array of groupings for this entity
     *
     * @var Group
     */
    private $groups = [];

    /**
     * Removed groupings
     *
     * @param array
     */
    private $deleted = [];

    /**
     * Get object type
     *
     * @var string
     */
    private $objType = "";

    /**
     * Field name we are working with
     *
     * @var string
     */
    private $fieldName = "";

    /**
     * Optional. userGuid is set if this grouping is private
     * 
     * @var string
     */
    private $userGuid = "";

    /**
     * Optional datamapper to call this->save through the Loader class
     *
     * @var EntityGroupingDataMapperInterface
     */
    private $dataMapper = null;

    /**
     * Unique path of the entity groupings
     * 
     * @var string
     */
    public $path = "";

    /**
     * Initialize groupings
     *
     * @param string $path The path of the object groupings. This consists 2 or 3 parts: obj_type/field_name/user_guid. User guid is optional.
     */
    public function __construct($path)
    {
        $this->path = $path;
        $parts = explode("/", $path);

        if (sizeof($parts) <= 1) {
            throw new \Exception("Entity groupings should at least have 2 parts obj_type/field_name.");
        }

        $this->objType = $parts[0];
        $this->fieldName = $parts[1];
        $path = "{$this->objType}/{$this->fieldName}";

        // If we have 3 parts of path, then we set it as our user guid
        if (isset($parts[2])) {
            $this->userGuid = $parts[2];
            $path .= "/{$this->userGuid}";
        }
    }

    /**
     * Set datammapper for groups
     *
     * @param EntityGroupingDataMapperInterface $dm
     */
    public function setDataMapper(EntityGroupingDataMapperInterface $dm)
    {
        $this->dataMapper = $dm;
    }

    /**
     * Save groupings to internally set DataMapper
     *
     * @throws Exception
     */
    public function save()
    {
        if (!$this->dataMapper) {
            throw new Exception("You cannot save groups without first calling setDatamapper");
        }

        $this->dataMapper->saveGroupings($this);
    }

    /**
     * Get the object type for this grouping
     *
     * @return string The name of the object type
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Get the field name for this grouping
     *
     * @return string The name of the field that stores these groupings
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Get the user guid of this groupings if it is a private grouping
     */
    public function getUserGuid()
    {
        return $this->userGuid;
    }

    /**
     * Get a group that is hierarchical by path
     *
     * @param string $path The full path to a grouping separated by '/'
     * @return Group
     */
    public function getByPath($path)
    {
        $parts = explode("/", $path);
        $ret = null;

        // Loop through the path and get the last entry
        foreach ($parts as $grpname) {
            if ($grpname) {
                $parent = ($ret) ? $ret->guid : "";
                $ret = $this->getByName($grpname, $parent);
            }
        }

        return $ret;
    }

    /**
     * Get grouping path by id
     *
     * Grouping paths are constructed using the parent id. For instance Inbox/Subgroup would be constructed
     * for a group called "Subgroup" whose parent group is "Inbox"
     *
     * @param string $gid The unique id of the group to get a path for
     * @return string The full path of the heiarchy
     */
    public function getPath($gid)
    {
        $grp = $this->getByGuidOrGroupId($gid);

        $path = "";
        if (!$grp) {
            return $path;
        }

        if ($grp->parentId) {
            $path .= $this->getPath($grp->parentId) . "/";
        }

        $path .= $grp->name;

        return $path;
    }

    /**
     * Retrieve grouping data by a unique name
     *
     * @param string $nameValue The unique value of the group to retrieve
     * @param int $paren Optional parent id for querying unique names of sub-groupings
     * @return array See getGroupingData return value for definition of grouping data entries
     */
    public function getByName($nameValue, $parent = null)
    {
        foreach ($this->groups as $grp) {
            if ($grp->name == $nameValue) {
                return $grp;
            }
        }

        return false;
    }

    /**
     * Get groups
     *
     * @return \Netric\EntityGroupings\Group[]
     */
    public function getAll()
    {
        return $this->groups;
    }

    /**
     * Recurrsively return all as an array
     *
     * @return arrray
     */
    public function toArray()
    {
        $ret = [];

        foreach ($this->groups as $grp) {
            $ret[] = $grp->toArray();
        }

        return $ret;
    }

    /**
     * Put all the groupings into a hierarchical structure with group->children being populated
     *
     * @param int $parentId Get all at the level of this parent
     * @return Group[] with $group->children populated
     */
    public function getHeirarch($parentId = null)
    {
        $ret = [];
        foreach ($this->groups as $grp) {
            if ($grp->parentId == $parentId) {
                // If existing group, then get the children setting parent to group id
                if ($grp->guid) {
                    $grp->children = $this->getHeirarch($grp->guid);
                }

                $ret[] = $grp;
            }
        }
        return $ret;
    }

    /**
     * Get all children in a flat one dimensional array
     *
     * @param int $parentId Get all at the level of this parent
     * @param $arr &$arr If set, then put children here
     * @return Group[] with $group->children populated
     */
    public function getChildren($parentId = null, &$ret = null)
    {
        if ($ret == null) {
            $ret = [];
        }

        foreach ($this->groups as $grp) {
            if ($grp->parentId == $parentId) {
                $ret[] = $grp;

                // If existing group, then get the children setting parent to group id
                if ($grp->guid) {
                    $this->getChildren($grp->guid, $ret);
                }
            }
        }

        return $ret;
    }

    /**
     * Get deleted groupings
     *
     * @return int[]
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Get changed or added groupings
     *
     * @return Group[]
     */
    public function getChanged()
    {
        $ret = [];

        foreach ($this->groups as $grp) {
            if ($grp->isDirty()) {
                $ret[] = $grp;
            }
        }

        return $ret;
    }

    /**
     * Insert a new entry into the table of a grouping field (fkey)
     *
     * @param Group $group The group to add to the array
     * @return true on success, false on failure
     */
    public function add($group)
    {
        // Check to see if a grouping with this name already exists
        if ($group->parentId) {
            $exists = $this->getByName($group->name, $group->parentId);
        } else {
            $exists = $this->getByName($group->name);
        }

        if ($exists) {
            return false;
        }

        if ($group->parentId) {
            // TODO: check for circular reference in the chain
        }

        $this->groups[] = $group;
        return true;
    }

    /**
     * Get the grouping entry by guid
     *
     * @param strin $guid the id to delete
     */
    public function getByGuid($guid)
    {
        foreach ($this->groups as $grp) {
            if ($grp->guid == $guid) {
                return $grp;
            }
        }

        return false;
    }

    /**
     * Create a new grouping
     *
     * @param string $name Optional name of grouping
     */
    public function create($name = "")
    {
        $group = new Group();
        $group->setDirty(true);
        if ($name) {
            $group->name = $name;
        }
        return $group;
    }

    /**
     * Delete and entry from the table of a grouping field (fkey)
     *
     * @param int $entryId the id to delete
     * @return bool true on success, false on failure
     */
    public function delete($entryId)
    {
        for ($i = 0; $i < count($this->groups); $i++) {
            if ($this->groups[$i]->guid == $entryId) {
                // Move to deleted queue
                $this->deleted[] = $this->groups[$i];

                // Remove group from this grouping collection
                array_splice($this->groups, $i, 1);

                break;
            }
        }

        return true;
    }

    /**
     * Deprecated - Marl Tumulak 01/14/2020
     * 
     * Get unique filters hash
     */
    public static function getFiltersHash($filters = [])
    {
        // Make sure we have filters provided
        if ($filters) {
            $buf = $filters; // copy array
            ksort($buf);

            $ret = "";

            foreach ($buf as $fname => $fval) {
                if ($fval) {
                    $ret .= $fname . "=" . $fval;
                }
            }

            if ("" == $ret) {
                $ret = 'none';
            }

            return $ret;
        }
    }

    /**
     * Function that will check if the value is a valid uuid or a group id. Then it will return the group
     * 
     * @param string $value This value should be either a valud uuid or group id.
     */
    public function getByGuidOrGroupId(string $value)
    {
        // If group guid is provided, then we need to use getByGuid
        if (Uuid::isValid($value)) {
            return $this->getByGuid($value);
        }
        return null;
    }
}
