<?php
/**
 * Abstract commit datamapper
 *
 * @category    DataMapper
 * @author      Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright   Copyright (c) 2003-2014 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\EntitySync\Commit\DataMapper;

use Netric\Account\Account;
use Netric\Db\Relational\RelationalDbInterface;

abstract class DataMapperAbstract extends \Netric\DataMapperAbstract implements DataMapperInterface
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    protected $database = null;
    
    /**
     * Class constructor
     *
     * @param string $accountName The name of the ANT account that owns this data
     * @param RelationalDbInterface $database Handles to database actions
     */
    public function __construct(Account $account, RelationalDbInterface $database)
    {
        $this->setAccount($account);
        $this->database = $database;
    }
}
