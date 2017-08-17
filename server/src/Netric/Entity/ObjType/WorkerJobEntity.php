<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2017 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;

/**
 * Worker job represents a background job for a worker
 */
class WorkerJobEntity extends Entity implements EntityInterface
{
    /**
     * Callback function used for derived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(AccountServiceManagerInterface $sm)
    {
    }

    /**
     * Callback function used for derived subclasses
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
