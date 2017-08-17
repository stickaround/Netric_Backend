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
    /*
     * Right now this entity does nothing special, if we wanted to extend the
     * base entity we could easily do so with:
     * onBeforeSave
     * onAfterSave
     * onBeforeDeleteHard
     */
}
