<?php

declare(strict_types=1);

namespace Netric\Workflow;

use Netric\Workflow\ActionExecutor\ActionExecutorInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\ActionExecutor\Exception\ActionNotFoundException;
use InvalidArgumentException;

/**
 * General action container used to construct new actions
 */
class ActionExecutorFactory
{
    /**
     * Service ServiceLocator for injecting dependencies
     *
     * @var ServiceLocatorInterface
     */
    private ServiceLocatorInterface $serviceManager;

    /**
     * Class constructor
     *
     * @param ServiceLocatorInterface $serviceLocator For injecting dependencies
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceManager = $serviceLocator;
    }

    /**
     * Create a new action based on a name
     *
     * @param WorkflowActionEntity $action Entity with action state
     * @return ActionExecutorInterface
     * @throws ActionNotFoundException if the $type is not a valid action
     * @throws InvalidArgumentException If the caller tries to send an empty string for type
     */
    public function create(WorkflowActionEntity $action): ActionExecutorInterface
    {
        if (!$action->getValue('type_name')) {
            throw new InvalidArgumentException("Type is required");
        }

        /*
         * First convert object name to file name - camelCase with upper case first.
         * Example: 'test' becomes 'Test'
         * Example: 'my_action' becomes 'MyAction'.
         */
        $className = ucfirst($action->getValue('type_name'));
        if (strpos($action->getValue('type_name'), "_") !== false) {
            $parts = explode("_", $className);
            $className = "";
            foreach ($parts as $word) {
                $className .= ucfirst($word);
            }
        }

        // Every action must have a factory
        $className = "\\Netric\\Workflow\\ActionExecutor\\" . $className . "ActionExecutorFactory";

        // Use factory if it exists
        if (!class_exists($className)) {
            throw new ActionNotFoundException("Action factory $className could not be found");
        }

        return $className::create($this->serviceManager, $action);
    }
}
