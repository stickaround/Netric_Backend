<?php

namespace Netric\Entity\ObjType;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;

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
     * Callback function used for derrived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(AccountServiceManagerInterface $sm)
    {
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onAfterSave(AccountServiceManagerInterface $sm)
    {
    }

    /**
     * Called right before the entity is purged (hard delete)
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeDeleteHard(AccountServiceManagerInterface $sm)
    {
    }
}
