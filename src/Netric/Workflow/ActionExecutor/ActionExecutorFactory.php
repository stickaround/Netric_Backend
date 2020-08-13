<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Workflow\ActionExecutor\Exception\ActionNotFoundException;
use InvalidArgumentException;

/**
 * General action container used to construct new actions
 */
class ActionExecutorFactory
{
    /**
     * Service manager used to load dependencies
     *
     * @var AccountServiceManagerInterface
     */
    private AccountServiceManagerInterface $serviceManager;

    /**
     * Class constructor
     *
     * @param AccountServiceManagerInterface $serviceLocator For injecting dependencies
     */
    public function __construct(AccountServiceManagerInterface $serviceLocator)
    {
        $this->serviceManager = $serviceLocator;
    }

    /**
     * Create a new action based on a name
     *
     * @param string $type The name of the type of action
     * @param WorkflowActionEntity $action Entity with action state
     * @return ActionExecutorInterface
     * @throws ActionNotFoundException if the $type is not a valid action
     * @throws InvalidArgumentException If the caller tries to send an empty string for type
     */
    public function create(string $type, WorkflowActionEntity $action): ActionExecutorInterface
    {
        if (!$type) {
            throw new InvalidArgumentException("Type is required");
        }

        /*
         * First convert object name to file name - camelCase with upper case first.
         * Example: 'test' becomes 'Test'
         * Example: 'my_action' becomes 'MyAction'.
         */
        $className = ucfirst($type);
        if (strpos($type, "_") !== false) {
            $parts = explode("_", $className);
            $className = "";
            foreach ($parts as $word) {
                $className .= ucfirst($word);
            }
        }

        // Every action must have a factory
        $className = "\\Netric\\Workflow\\ActionExecutor\\" . $className . "ActionFactory";

        // Use factory if it exists
        if (!class_exists($className)) {
            throw new ActionNotFoundException("Action factory $className could not be found");
        }

        return $className::create($this->serviceManager, $action);
    }
}
