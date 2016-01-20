<?php
/**
 * New discretionary access controll list
 * 
 * TODO: this class is in progress being converted from the old \Dacl class
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */
namespace Netric\Permissions;

/**
 * Discretionary access controll list 
 */
class Dacl 
{
    /**
	 * ID of dacl to inherit permissions from
	 *
	 * @var int
	 */
	private $inheritFrom = null;

	/**
	 * Default permissions
	 *
	 * @var string[]
	 */
	private $defaultPerms  = array("View", "Edit", "Delete");

	/**
	 * Saved DACLs will all have a unique id
	 *
	 * @var int
	 */
	public $id = null;

	/**
	 * Each DACL may have a unique name/key to access it by
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * Used in debugging only
	 *
	 * @var bool
	 */
	public $debug = false;

	/**
	 * Associative array with either group or user assoicated with an permission
	 *
	 * @var Netric\Permissions\Dacl\Entry[]
	 */
	private $entries = array();

	/**
	 * List of permissions for this DACL
	 *
	 * @var string[]
	 */
	private $permissions = array();

	/**
	 * Class constructor
	 */
	public function __construct()
	{

	}
    
	/**
	 * Load definition of this array from data array
	 *
	 * @var array $data Associative array with 'permissions' and 'entries'
	 * @return bool True on success, false on failure
	 */
	public function loadByData($data)
	{
		if (!is_array($data))
			return false;

		$this->id = $data['id'];
		$this->name = $data['name'];
		$this->inheritFrom = $data['inheritFrom'];
		$this->inheritFromOld = $data['inheritFromOld'];

		if (is_array($data['entries']))
			$this->setEntries($data['entries']);

		// Make sure this DACL can be accessed by someone
		if (count($this->entries) == 0)
		{
			$this->allowGroup(\Netric\Entity\ObjType\UserEntity::GROUP_USERS);
			$this->allowGroup(\Netric\Entity\ObjType\UserEntity::GROUP_CREATOROWNER);
			$this->allowGroup(\Netric\Entity\ObjType\UserEntity::GROUP_ADMINISTRATORS);
		}
	}	

	/**
	 * Create a JSON encoded string representing this dacl
	 *
	 * @return string The json encoded string for this dacl
	 */
	public function stringifyJson()
	{
		$data = array();

		$data['id'] = $this->id;
		$data['name'] = $this->name;
		$data['inheritFrom'] = $this->inheritFrom;
		$data['inheritFromOld'] = $this->inheritFromOld;
		$data['entries'] = $this->getEntries();
		
		return json_encode($data);
	}

	/**
	 * Get array of list entries for this DACL
	 *
	 * Entries associate a user or group with a permission
	 *
	 * @return array Array of entries
	 */
	public function getEntries()
	{
		$entries = array();

		foreach ($this->entries as $pname=>$ent)
		{
			foreach ($ent->groups as $grp)
				$entries[] = array("permission"=>$pname, "group_id"=>$grp);

			foreach ($ent->users as $uid)
				$entries[] = array("permission"=>$pname, "user_id"=>$uid);
		}

		return $entries;
	}

	/**
	 * Clear entries
	 */
	public function clearEntries()
	{
		$this->entries = array();
	}

	/**
	 * Set local entries from array
	 */
	private function setEntries($entries)
	{
		for ($i = 0; $i < count($entries); $i++)
		{
			$ent = $entries[$i];

			if (!isset($this->entries[$ent['permission']]))
				$this->entries[$ent['permission']] = new Netric\Permissions\Dacl\Entry();

			if (isset($ent['user_id']) && is_numeric($ent['user_id']) && !in_array($ent['user_id'], $this->entries[$ent['permission']]->users))
				$this->entries[$ent['permission']]->users[] = $ent['user_id'];

			if (isset($ent['group_id']) && is_numeric($ent['group_id']) && !in_array($ent['group_id'], $this->entries[$ent['permission']]->groups))
				$this->entries[$ent['permission']]->groups[] = $ent['group_id'];
		}
	}

	/**
	 * Get array of users mentioned in the entries
	 *
	 * @return array(array('id','name')) of users
	 */
	public function getUsers()
	{
		$uids = array();

		// Get distinct list of users
		foreach ($this->entries as $ent)
		{
			foreach ($ent->users as $userId)
			{
				if (!in_array($userId, $uids))
					$uids[] = $userId;
			}
		}

		return $uids;
	}

	/**
	 * Get array of groups mentioned in the entries
	 *
	 * @return array(array('id','name')) of users
	 */
	public function getGroups()
	{
		$gids = array();

		// Get distinct list of users
		foreach ($this->entries as $ent)
		{
			foreach ($ent->groups as $groupId)
			{
				if (!in_array($groupId, $gids))
					$gids[] = $groupId;
			}
		}
        
        return $gids;
	}

	/**
	 * Grant access to a user to a specific permission
	 *
	 * @param int $USERID The user id to grant access to
	 * @param string $permission The permssion to grant access to
	 */
	public function allowUser($USERID, $permission="Full Control")
	{
		if ("Full Control" == $permission)
		{
			foreach ($this->entries as $ent)
			{
				if (!in_array($USERID, $ent->users))
					$ent->users[] = $USERID;
			}
		}

		// Add specific permission
		if (!isset($this->entries[$permission]))
			$this->entries[$permission] = new Netric\Permissions\Dacl\Entry();

		$ent = $this->entries[$permission];
		if ($ent && !in_array($USERID, $ent->users))
			$ent->users[] = $USERID;

		//$this->clearCache();
		$this->removeInheritFrom();
	}

	/**
	 * Grant access to a group to a specific permission
	 *
	 * @param int $gid The group id to grant access to
	 * @param string $permission The permssion to grant access to
	 */
	public function allowGroup($gid, $permission="Full Control")
	{
		// Add specific permission
		if (!isset($this->entries[$permission]))
			$this->entries[$permission] = new Dacl\Entry();

		// Grant group access
		$ent = $this->entries[$permission];
		if (!in_array($gid, $ent->groups))
			$ent->groups[] = $gid;

		if ("Full Control" == $permission)
		{
			foreach ($this->entries as $ent)
			{
				if (!in_array($gid, $ent->groups))
					$ent->groups[] = $gid;
			}
		}

		//$this->removeInheritFrom();
	}

	/**
	 * Deny access to a user to a specific permission
	 *
	 * @param int $USERID The user id to grant access to
	 * @param string $permission The permssion to grant access to
	 */
	public function denyUser($uid, $permission="Full Control")
	{
		if ($this->entries[$permission])
		{
			for ($i = 0; $i < count($this->entries[$permission]->users); $i++)
			{
				if ($this->entries[$permission]->users[$i] == $uid)
					array_splice($this->entries[$permission]->users, $i, 1);
			}
		}
	}

	/**
	 * Deny access to a group to a specific permission
	 *
	 * @param int $USERID The user id to grant access to
	 * @param string $permission The permssion to grant access to
	 */
	public function denyGroup($gid, $permission="Full Control")
	{
		if ($this->entries[$permission])
		{
			for ($i = 0; $i < count($this->entries[$permission]->groups); $i++)
			{
				if ($this->entries[$permission]->groups[$i] == $gid)
				{
					array_splice($this->entries[$permission]->groups, $i, 1);
				}
			}
		}
	}

	/**
	 * Check if a user has access to a permission either directly or through group membership
	 *
	 * @param AntUser $user The user to check for access
	 * @param string $permission The permission to check against. Defaults to 'Full Control'
	 * @param bool isowner Set to true if the $USERID is the owner of the object being secured by this DACL
	 * @param bool ignoreadmin If set to true then the 'god' access of the administrator is ignored
	 */
	public function isAllowed($user, $permission="Full Control", $isowner=false, $ignoreadmin=false)
	{
		$granted = false;
		$groups = $user->getGroups();
		if ($isowner)
			$groups[] = UserEntity::GROUP_CREATOROWNER; // Add to Creator/Owner group

		// Sometimes used for user-specific objects like calendars
		if ($ignoreadmin)
		{
			$tmp_groups = array();
			foreach ($groups as $gid)
			{
				if ($gid != UserEntity::GROUP_ADMINISTRATORS) // Admin
					$tmp_groups[] = $gid;
			}
			unset($groups);
			$groups = $tmp_groups;
		}

		// First check to see if the user has full control
		if ($permission!="Full Control")
		{
			$granted = $this->isAllowed($user, "Full Control", $isowner, $ignoreadmin);
			if ($granted)
				return $granted;
		}

        $per = null;
        if(isset($this->entries[$permission]))
		    $per = $this->entries[$permission];

		if ($per)
		{
			// Test users
			if (count($per->users))
			{
				foreach ($per->users as $uid)
				{
					if ($uid == $user->id)
						$granted = true;
				}
			}

			// Test groups
			if (count($per->groups))
			{
				foreach ($per->groups as $gid)
				{
					if (in_array($gid, $groups))
					{
						$granted = true;
					}
				}
			}
		}

		return $granted;
	}

	/**
	 * Check if a specific user has access to a specific permission
	 *
	 * @param int $USERID The user id to check
	 * @param string $permission The permission to check against
	 * @return bool true if user has permission, false if access is denied
	 */
	public function isAllowedUser($USERID, $permission="Full Control")
	{
		$granted = false;

		$per = $this->entries[$permission];

		if ($per)
			$granted = in_array($USERID, $per->users);

		return $granted;
	}

	/**
	 * Check if a specific group has access to a specific permission
	 *
	 * @param int $GROUPID The group to check
	 * @param string $permission The permission to check against
	 * @return bool true if group has permission, false if access is denied
	 */
	public function isAllowedGroup($GROUPID, $permission="Full Control")
	{
		$granted = false;

		$per = $this->entries[$permission];

		if ($per)
			$granted = in_array($GROUPID, $per->groups);

		return $granted;
	}

	/**
	 * Set this DACl to inherit permissions from a parent DACL
	 */
	public function setInheritFrom($daclid)
	{
		if (!$daclid)
			return false;

		$this->inheritFrom = $daclid;

		return true;
	}

	/**
	 * Unlink this DACL to a parent
	 *
	 * This will create a unique instance rather than inheriting from a parent DACL
	 *
	 * @return bool true on succes, false on failure
	 */
	public function removeInheritFrom()
	{
        $this->inheritFrom = null;

		return true;
	}
}
