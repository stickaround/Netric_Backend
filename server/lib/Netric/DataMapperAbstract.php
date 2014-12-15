<?php
/**
 * A DataMapper is responsible for writing and reading data from a persistant store
 *
 * @category	DataMapper
 * @author		Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright	Copyright (c) 2003-2013 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric;

abstract class DataMapperAbstract
{
	/**
	 * Handle to current account we are mapping data for
	 *
	 * @var Netric\Account
	 */
	protected $account = "";
    
	/**
	 * Get account
	 * 
	 * @return Netric\Account
	 */
	public function getAccount()
	{
		return $this->account;
	}

	/**
	 * Set account
	 * 
	 * @param Netric\Account $account The account of the current tennant
	 */
	public function setAccount($account)
	{
		$this->account = $account; 
	}
}
