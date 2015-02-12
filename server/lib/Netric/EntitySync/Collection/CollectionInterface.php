<?php
/**
 * Sync collection interface
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\EntitySync\Collection;

interface CollectionInterface
{
	/**
	 * Get a id if it is saved
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * Get a stats list of what has changed locally since the last sync
	 *
	 * @param bool $autoFastForward If true (default) then fast-forward collection commit_id on return
	 * @return array of assoiative array [["id"=><object_id>, "action"=>'change'|'delete']]
	 */
	public function getExportChanged($autoFastForward=true);

	/**
	 * Get a stats of the difference between an import and what is stored locally
	 *
	 * @param array $importList Array of arrays with the following param for each object {uid, revision}
	 * @param int $parentId If set, pull all objects that are a child of the parent id only
	 * @return array(
	 *		array(
	 *			'uid', // Unique id of foreign object 
	 *			'object_id', // Local entity/object (same thing) id
	 *			'action', // 'chage'|'delete'
	 *			'revision' // Revision of local entity at time of last import
	 *		);
	 */
	public function getImportChanged($importList, $parentId=null);

	/**
	 * Get a collection type id
	 *
	 * @return int Type from \Netric\EntitySync::COLL_TYPE_*
	 */
	public function getType();

	/**
	 * Fast forward this collection to current head which resets it to only get future changes
	 */
	public function fastForwardToHead();
}