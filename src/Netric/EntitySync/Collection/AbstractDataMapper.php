<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Netric\Db\Relational\RelationalDbContainer;
use Netric\DataMapperAbstract;

abstract class AbstractDataMapper extends DataMapperAbstract
{
    /**
     * Class constructor
     *
     * @param RelationalDbContainer $database Handles the database actions
     */
    public function __construct(RelationalDbContainer $dbContainer)
    {
        $this->setUp($dbContainer);
    }
}
