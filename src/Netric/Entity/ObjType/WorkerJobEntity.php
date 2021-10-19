<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2017 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;

/**
 * Worker job represents a background job for a worker
 */
class WorkerJobEntity extends Entity implements EntityInterface
{
    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     */
    public function __construct(EntityDefinition $entityDefinition)
    {
        parent::__construct($entityDefinition);
    }
}
