<?php
/**
 * PgSQL datamapper for synchronization library
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */

 namespace Netric\EntitySync;

 class DataMapperPgsql extends AbstractDataMapper implements DataMapperInterface
 {
	/**
	 * Populate collections array for a given partner
	 */
	private function loadPartnerCollections($partner)
	{
		if (!$this->id)
			return false;


		$result = $this->dbh->Query("SELECT id FROM object_sync_partner_collections WHERE partner_id='".$this->id."'");
		$num = $this->dbh->GetNumberRows($result);
		for ($i = 0; $i < $num; $i++)
		{
			$row = $this->dbh->GetRow($result, $i);

			$this->collections[] = new AntObjectSync_Collection($this->dbh, $row['id'], $this->user);
		}
	}

	/**
	 * Save partner collections
	 */
	private function savePartnerCollections($partner)
	{
		if (!is_numeric($this->id))
			return false;

		for ($i = 0; $i < count($this->collections); $i++)
		{
			$this->collections[$i]->partnerId = $this->id;
			$this->collections[$i]->save();
			//$this->saveCollection($this->collections[$i]);
		}

		return true;
	}

	/**
	 * Delete a partner by id
	 *
	 * @param string $id The unique id of the partnership to delete
	 * @return bool true on success, false on failure
	 */
	public function deletePartner($id)
	{
		if ($this->id)
			$this->dbh->Query("DELETE FROM object_sync_partners WHERE id='" . $this->id . "'");
		else if ($this->partnerId)
			$this->dbh->Query("DELETE FROM object_sync_partners WHERE pid='" . $this->dbh->Escape($this->partnerId) . "'");

		return true;
	}
 }