<?php
/**
 * Abstract commit datamapper
 *
 * @category	DataMapper
 * @author		Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright	Copyright (c) 2003-2014 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Entity\Commit\DataMapper;

class Pgsql extends DataMapperAbstract
{
	/**
	 * Database handle
	 *
	 * @var \Netric\Db\DbInterface
	 */
	private $dbh = null;

	/**
	 * Sequence name
	 */
	private $sSequenceName = "object_commit_seq";

	/**
	 * Setup this class called from the parent constructor
	 * 
	 * @param ServiceLocator $sl The ServiceLocator container
	 */
	protected function setUp()
	{
		$this->dbh = $this->account->getServiceManager()->get("Db");
	}

	/**
	 * Get next id
	 *
	 * @param int $objectTypeid
	 * @return bigint
	 */
	public function getNextCommitId($objTyppeId)
	{
		$cid = $this->getNextSeqVal();
		
		// The sequence may not be defined, try creating it
		if (!$cid)
		{
			$cid = $this->createSeq();
			$cid = $this->getNextSeqVal();
		}

		return $cid;
	}

	/**
	 * Set the head commit id for an object
	 *
	 * @param int $objectTypeId
	 * @param bigint $cid
	 */
	public function saveHead($objTyppeId, $cid)
	{
		$res = $this->dbh->query("UPDATE app_object_types SET head_commit_id='$cid' WHERE id=" . $this->dbh->escapeNumber($objTyppeId));
		return ($res) ? true : false;
	}

	/**
	 * Get the head commit id for an object type
	 *
	 * @param $objectTypeid
	 * @return bigint
	 */
	public function getHead($objTypeId)
	{
		$res = $this->dbh->query("SELECT head_commit_id FROM app_object_types WHERE id=" . $this->dbh->escapeNumber($objTypeId));
		if ($res)
			return $this->dbh->getValue($res, 0, "head_commit_id");
		else
			return 0;
	}

	/**
	 * Get the next value of the sequenece
	 */
	private function getNextSeqVal()
	{
		$res = $this->dbh->query("SELECT nextval('" . $this->sSequenceName . "');");
		if ($res)
		{
			return $this->dbh->getValue($res, 0, "nextval");
		}
	}

	/**
	 * Try to create the sequence
	 *
	 * @return int|bool current id of the sequence on success, false on failure
	 */
	private function createSeq()
	{
		$this->dbh->query("CREATE SEQUENCE " . $this->sSequenceName . ";");
	}
}