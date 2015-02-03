<?php
/**
 * Abstract DataMapper for sync library
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */

 namespace Netric\EntitySync;

 abstract class AbstractDataMapper extends \Netric\DataMapperAbstract
 {
	/**
	 * Handle to database
	 *
	 * @var \Netric\Db\Pgsql
	 */
	private $dbh = null;
	
	/**
	 * Class constructor
	 * 
	 * @param \Netric\Account $account Account for tennant that we are mapping data for
	 * @param \Netric\Db\Pgsql $dbh Handle to database
	 */
	public function __construct($account, $dbh)
	{
		$this->setAccount($account);

		$this->dbh = $dbh;
	}

 	/**
	 * Get a partner by id
	 *
	 * @param string partnerId
	 * @return Netric\EntitySync\Partner or null if id does not exist
	 */
	public function getPartner($partnerId)
	{
		if (empty($partnerId))
			return false;

		$result = $this->dbh->Query("SELECT id, owner_id FROM object_sync_partners WHERE pid='".$this->dbh->Escape($partnerId)."'");
		if ($this->dbh->GetNumberRows($result))
		{
			$row = $this->dbh->GetRow($result, 0);
			$this->id = $row['id'];
			$this->ownerId = $row['owner_id'];
		}
		else if ($createIfMissing)
		{
			$result = $this->dbh->Query("INSERT INTO object_sync_partners(pid, owner_id)
			   							 VALUES('".$this->dbh->Escape($partnerId)."', ".$this->dbh->EscapeNumber($this->ownerId).");
										 SELECT currval('object_sync_partners_id_seq') as id;");
			if ($this->dbh->GetNumberRows($result))
				$this->id = $this->dbh->GetValue($result, 0, "id");
		}

		if ($this->id)
		{
			$this->loadCollections();
			return true;
		}
		else
		{
			return false;
		}
	}

	public function savePartner(Partner $partner)
	{
		if (!$this->partnerId)
			return false;

		// Save partnership info
		$data = array(
			"pid" => $this->partnerId,
			"owner_id" => $this->ownerId,
		);

		if ($this->id)
		{
			$update = "";
			foreach ($data as $col=>$val)
			{
				if ($update)
					$update .= ", ";
				$update .= $col . "='" . $this->dbh->Escape($val) . "'";
			}

			$query = "UPDATE object_sync_partners SET $update WHERE id='" . $this->id . "';";
		}
		else
		{
			$flds = "";
			$vals = "";

			foreach ($data as $col=>$val)
			{
				if ($flds)
				{
					$flds .= ", ";
					$vals .= ", ";
				}

				$flds .= $col;
				$vals .= "'" . $this->dbh->Escape($val) . "'";
			}

			$query = "INSERT INTO object_sync_partners($flds) VALUES($vals); 
					  SELECT currval('object_sync_partners_id_seq') as id;";
		}

		$result = $this->dbh->Query($query);
		if ($result == false)
			return false;

		if (!$this->id)
			$this->id = $this->dbh->GetValue($result, 0, "id");


		// Save collections 
		$this->saveCollections();

		// Send the id as confirmation that the partnership has been saved
		return $this->id;
	}
}
