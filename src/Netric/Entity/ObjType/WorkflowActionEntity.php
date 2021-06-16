<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
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
     * @param EntityLoader $entityLoader The loader for a specific entity
     */
    public function __construct(EntityDefinition $def, EntityLoader $entityLoader)
    {
        parent::__construct($def, $entityLoader);
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
