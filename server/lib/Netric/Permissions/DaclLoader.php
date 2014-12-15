<?php
/**
 * Identity mapper for DACLs to make sure we are only loading each one once
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */
namespace Netric\Permissions;

/**
 * DACL Identity Mapper
 */
class DaclLoader 
{
    /**
	 * Array holding already loaded lists
	 *
	 * @var array
	 */
	private $dacls = array();
    
    /**
     * DataMapper
     * 
     * @var \Netric\Permissions\Dacl\DataMapperDb
     */
    private $dm = null;
    
    /**
     * Class constructor
     * 
     * @param \Netric\Permissions\Dacl\DataMapperDb $dataMapper
     */
    public function __construct($dataMapper) 
    {
        $this->dm = $dataMapper;
    }
    
    /**
	 * Get an access controll list by name
	 * 
	 * @param string $key The name of the list to pull
	 * @return Dacl
	 */
	public function byName($key, $cache=true)
	{
        /* Old code... should now get from $this->dm
		$key = $this->dbh->dbname . "/" . $key;

		if (isset($this->dacls[$key]) && $cache)
			return $this->dacls[$key];

		// Not yet loaded, create then store
		if ($cache)
		{
			$this->dacls[$key] = new Dacl($this->dbh, $key);
			return $this->dacls[$key];
		}
		else
		{
			$dacl = new Dacl($this->dbh, $key);
			return $dacl;
		}
         */
	}

	/**
	 * Initialize a DACL by dada array
	 * 
	 * @param string $key The name of the list to pull
	 * @param array $data Data array to be passed to Dacl::loadByData
	 * @return Dacl
	 */
	public function byData($key, $data)
	{
		$dacl = new Dacl($key);
		$dacl->loadByData($data);
		return $dacl;
	}

	/**
	 * Clear object definition cache by name
	 * 
	 * @param string $key The name of the list to pull
	 * @return CAntObjectFields
	 */
	public function unloadDacl($key)
	{
		$this->dacls[$key] = null;
	}
}
