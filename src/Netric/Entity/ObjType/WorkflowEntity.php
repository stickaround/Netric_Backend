<?php

namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\EntityDefinition;

/**
 * Workflow represents a workflow that can be used automate things
 */
class WorkflowEntity extends Entity implements EntityInterface
{
    /**
     * Events that can trigger work flows
     */
    const EVENT_CREATE = 'create';
    const EVENT_UPDATE = 'update';
    const EVENT_DELETE = 'delete';
    const EVENT_DAILY = 'daily';

    /**
     * Units of time for relative times
     *
     * @var const
     */
    const TIME_UNIT_MINUTE = 1;
    const TIME_UNIT_HOUR = 2;
    const TIME_UNIT_DAY = 3;
    const TIME_UNIT_WEEK = 4;
    const TIME_UNIT_MONTH = 5;
    const TIME_UNIT_YEAR = 6;

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader The loader for a specific entity
     */
    public function __construct(EntityDefinition $def, EntityLoader $entityLoader)
    {
        parent::__construct($def, $entityLoader);
    }
}
