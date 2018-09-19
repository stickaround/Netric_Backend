<?php
/**
 * Abstract DataMapper for sync library
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\EntitySync;

use Netric\Account\Account;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\DataMapperAbstract;

abstract class AbstractDataMapper extends DataMapperAbstract
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
     * @param Account $account Account for tennant that we are mapping data for
     * @param RelationalDbInterface $dbh Handle to database
     */
    public function __construct(Account $account, RelationalDbInterface $database)
    {
        $this->setAccount($account);
        $this->database = $database;
    }
}
