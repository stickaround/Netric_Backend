<?php
/**
 * Sync collection for entity groupings
 *
 * @category  AntObjectSync
 * @package   Collection
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\EntitySync\Collection;

use Netric\Entity;
use Netric\EntitySync\DataMapperInterface;
use Netric\EntitySync\EntitySync;
use Netric\EntitySync\Commit;

/**
 * Class used to represent a sync partner or endpoint
 */
class GroupingCollection extends AbstractCollection implements CollectionInterface
{
	/**
	 * Index for querying entities
	 *
	 * @var \Netric\EntityQuery\Index\IndexInterface
	 */
	private $entityDataMapper = null;

	/**
	 * Constructor
	 *
	 * @param \Netric\EntitySync\DataMapperInterface $dm The sync datamapper
	 * @param \Netirc\EntitySync\Commit\CommitManager $commitManager Manage system commits
	 * @param \Netric\Entity\DataMapperInterface $entityDataMapper Entity DataMapper
	 */
	public function __construct(
		DataMapperInterface $dm, 
		Commit\CommitManager $commitManager, 
		Entity\DataMapperInterface $entityDataMapper)
	{
		$this->entityDataMapper = $entityDataMapper;

		// Pass datamapper to parent
		parent::__construct($dm, $commitManager);
	}

	/**
	 * Get a stats list of what has changed locally since the last sync
	 *
	 * @param bool $autoFastForward If true (default) then fast-forward collection commit on return
	 * @return array of assoiative array [["id"=><object_id>, "action"=>'change'|'delete']]
	 */
	public function getExportChanged($autoFastForward=true)
	{
		if (!$this->getObjType())
		{
			throw new \InvalidArgumentException("Object type not set! Cannot export changes.");
		}

		if (!$this->getFieldName())
		{
			throw new \InvalidArgumentException("Field name is not set! Cannot export changes.");
		}

		// Set return array
		$retStats = array();

		// Get last commit id for this collection
		$headCommit = $this->commitManager->getHeadCommit($this->getCommitHeadIdent());

		// Get the current commit for this collection
		$lastCollectionCommit = $this->getLastCommitId();

		if ($lastCollectionCommit < $headCommit)
		{
			// Query local objects for commit_id with EntityList
			$query = new \Netric\EntityQuery($this->getObjType());
	        $query->orderBy('commit_id');
	        $query->setLimit(250);

	        // Set base/common condition
	        $query->where('commit_id')->isGreaterThan($lastCollectionCommit);

	        // Add any collection conditions
	        $conditions = $this->getConditions();
	        foreach ($conditions as $cond)
	        {
	        	if ($cond['blogic'] == 'or')
	        	{
	        		$query->orWhere($cond['field'], $cond['operator'], $cond['condValue']);
	        	}
	        	else
	        	{
	        		$query->andWhere($cond['field'], $cond['operator'], $cond['condValue']);
	        	}
	        }

	        // Execute query and get num results
	        $res = $this->index->executeQuery($query);
	  		$num = $res->getNum();

	        // Loop through each change
	        for ($i = 0; $i < $num; $i++)
	        {
	        	$ent = $res->getEntity($i);

	        	$retStats[] = array(
	        		"id" => $ent->getId(),
	        		"action" => (($ent->isDeleted()) ? 'delete' : 'change'),
	        	);

	        	if ($autoFastForward)
				{
					// Fast-forward $lastCommitId to last commit_id sent
					$this->setLastCommitId($ent->getValue("commit_id"));

					// Save to exported log
					$this->logExported(
						$ent->getId(), 
						$ent->getValue("commit_id")
					);
				}
	        }

	        /*
	         * If no new changes were found, then get previously exported
	         * objects that have been updated but apparently no longer meet
	         * the conditions of this collection.
	         * 
	         * Only do this if we have conditions that might have moved an entity
	         * outside of a subset of entities (query). If all entities are being 
	         * returned by this collection then every change will be replayed in order
	         * by the above query.
	         */
	        if (0 == count($retStats))
	        {
	        	$retStats = $this->getExportedStale();
	        }

	        // TODO: Save lastCommit if changed
	        if (count($retStats) && $autoFastForward && $this->getId())
	        {
	        	// saveCollection is currently private, research...
	        	// $this->dataMapper->saveCollection($this);
	        }
		}

		return $retStats;
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

	/**
	 * Get a collection type id
	 *
	 * @return int Type from \Netric\EntitySync::COLL_TYPE_*
	 */
	public function getType()
	{
		return EntitySync::COLL_TYPE_GROUPING;
	}

	/**
	 * Fast forward this collection to current head which resets it to only get future changes
	 */
	public function fastForwardToHead()
	{
		$headCommitId = $this->commitManager->getHeadCommit($this->getCommitHeadIdent());

		if ($headCommitId)
			$this->setLastCommitId($headCommitId);
	}

	/**
	 * Construct unique commit identifier for this collection
	 *
	 * @return string
	 */
	private function getCommitHeadIdent()
	{
		// Convert collection conditions to simpler filters for groupings
		$conditions = $this->getConditions();
        foreach ($conditions as $cond)
        {
        	if ($cond['blogic'] == 'and' && $cond['operator'] == 'id_equal')
        	{
        		$filters[$cond['field']] = $cond['condValue'];
        	}
        }

        $filtersHash = \Netric\EntityGrouping::getFiltersHash($filters);

		// TODO: if private then add the user_id as a filter field
		return "groupings/" . $this->getObjType() . "/" . $this->getFieldName() . "/" . $filtersHash;
	}
}
