<?php
/**
 * Interface defining what an EntitySync must implement
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\EntitySync;

interface DataMapperInterface
{
	/**
	 * Get a partner by id
	 *
	 * @param string partnerId
	 * @return Netric\EntitySync\Partner or null if id does not exist
	 */
	public function getPartner($partnerId);

	/**
	 * Get a partner by id
	 *
	 * @param Netric\EntitySync\Partner $partner Will set the id if new partner
	 * @return bool true on success, false on failure
	 */
	public function savePartner(Partner $partner);

	/**
	 * Delete a partner by id
	 *
	 * @param string $id The unique id of the partnership to delete
	 * @return bool true on success, false on failure
	 */
	public function deletePartner($id);

	/**
	 * Get listening partnership for this object type
	 *
	 * @param string $fieldName If the fieldname is set then try to find devices listening for an object grouping change
	 * @return AntObjectSync_Device[]
	 */
	//public function getListeningPartners($fieldName=null);
	/*
	{
		$ret = array();

		$field = ($fieldName) ? $this->obj->def->getField($fieldName) : false;

		$sql = "SELECT pid from object_sync_partners WHERE id IN (";
		$sql .= "SELECT partner_id FROM object_sync_partner_collections WHERE ";
		if (is_array($field)) // field id is unique so no object type is needed
			$sql .= " field_id='" . $field['id'] . "'";
		else
			$sql .= " object_type_id='" . $this->obj->object_type_id . "'";
		$sql .= ");";

		$result = $this->dbh->Query($sql);
		$num = $this->dbh->GetNumberRows($result);
		for ($i = 0; $i < $num; $i++)
		{
			$ret[] = new AntObjectSync_Partner($this->dbh, $this->dbh->GetValue($result, $i, "pid"), $this->user);
		}

		return $ret;
	}
	*/
}