<?php
/**
 * Access control list entry for a permission
 * 
 * This will represent a persmission like "View" and contains
 * which groups and users have access to that permission
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */

namespace Netric\Permissions\Dacl;

/**
 * ACL entry
 */
class Entry 
{
    /**
     * Group IDs with access
     * 
     * @var int[]
     */
    public $groups = array();
    
    /**
     * User IDs with access to this entry
     * 
     * @var string[]
     */
	public $users = array();
    
    /**
     * Unique ID of this entry (if any)
     * 
     * @var string
     */
	public $id = "";
    
    /**
     * If the entry has a parent like "Full Controll" then then ID will be here
     * 
     * @var string
     */
    public $parentId = "";

    /**
     * Class constructor
     * 
     * @param string $id Optional unique id of this entry
     * @param string $parent Optional parent entry id
     */
	public function __construct($id=null, $parent=null)
	{
		$this->id = $id;
		$this->parentId = $parent;
	}
}
