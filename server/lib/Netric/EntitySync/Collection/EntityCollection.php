<?php
/**
 * Sync collection for entities
 *
 * @category  AntObjectSync
 * @package   Collection
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\EntitySync\Collection;

use Netric\EntityQuery\Index;
use Netric\EntitySync\DataMapperInterface;

/**
 * Class used to represent a sync partner or endpoint
 */
class EntityCollection extends AbstractCollection implements CollectionInterface
{
	/**
	 * Index for querying entities
	 *
	 * @var \Netric\EntityQuery\Index\IndexInterface
	 */
	private $index = null;

	/**
	 * Constructor
	 *
	 * @param \Netric\EntitySync\DataMapperInterface $dm The sync datamapper
	 * @param \Netric\EntityQuery\Index\IndexInterface $idx Index for querying entities
	 */
	public function __construct(DataMapperInterface $dm, Index\IndexInterface $idx)
	{
		$this->index = $idx;

		// Pass datamapper to parent
		parent::__construct($dm);
	}

	/**
	 * Get a stats list of what has changed locally since the last sync
	 *
	 * @param int $parentId If set, pull all objects that are a child of the parent id only
	 * @param bool $autoClear If true (default) then purge stats as soon as they are returned
	 * @return array of assoiative array [["id"=><object_id>, "action"=>'change'|'delete']]
	 */
	public function getExportChanged($parentId=null, $autoClear=true)
	{
		// Query local objects for commit_id with EntityList
	}

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
	public function getImportChanged($importList, $parentId=null)
	{

	}
}
