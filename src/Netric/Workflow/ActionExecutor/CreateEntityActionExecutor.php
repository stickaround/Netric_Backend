<?php

/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2015 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Workflow\WorkFlowLegacyInstance;

/**
 * Action to create new entities from a workflow
 */
class CreateEntityActionExecutor extends AbstractActionExecutor implements ActionInterface
{
    /**
     * Execute this action
     *
     * @param WorkFlowLegacyInstance $workflowInstance The workflow instance we are executing in
     * @return bool true on success, false on failure
     */
    public function execute(WorkFlowLegacyInstance $workflowInstance)
    {
        // Get the entity we are executing against
        $entity = $workflowInstance->getEntity();

        // Get merged params
        $params = $this->getParams($entity);

        // Make sure we have what we need
        if (!$params['obj_type']) {
            throw new \InvalidArgumentException("Cannot create an entity without obj_type param");
        }

        // Create new entity
        $newEntity = $this->entityLoader->create($params['obj_type']);
        foreach ($params as $fname => $fval) {
            if ($newEntity->getDefinition()->getField($fname)) {
                if (is_array($fval)) {
                    foreach ($fval as $subval) {
                        $newEntity->addMultiValue($fname, $subval);
                    }
                } else {
                    $newEntity->setValue($fname, $fval);
                }
            }
        }

        return ($this->entityLoader->save($newEntity)) ? true : false;
    }
}
