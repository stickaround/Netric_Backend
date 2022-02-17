<?php

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
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
}
