<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;

/**
 * A Workflow Action/Step is a specific action that will be executed under a workflow
 */
class WorkflowActionEntity extends Entity implements EntityInterface
{
    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     */
    public function __construct(EntityDefinition $def)
    {
        parent::__construct($def);
    }

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
