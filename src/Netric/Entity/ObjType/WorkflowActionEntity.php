<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;

/**
 * A Workflow Action/Step is a specific action that will be executed under a workflow
 */
class WorkflowActionEntity extends Entity implements EntityInterface
{
    /**
     * Get data array for this action, since each action has different params
     *
     * @return array Associative array of action data
     */
    public function getData(): array
    {
        $data = $this->getValue('data');
        if ($data) {
            return json_decode($data, true);
        }

        return [];
    }
}
