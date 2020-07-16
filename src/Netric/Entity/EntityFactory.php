<?php

namespace Netric\Entity;

use Netric\ServiceManager;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\Entity;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoaderFactory;

/**
 * Create a new EntityFactory service
 *
 * @package Netric\FileSystem
 */
class EntityFactory
{
    /**
     * Service manager used to load dependencies
     *
     * @var AccountServiceManagerInterface
     */
    private $serviceManager = null;

    /**
     * Class constructor
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator implementation for injecting dependencies
     */
    public function __construct(AccountServiceManagerInterface $sl)
    {
        $this->serviceManager = $sl;
    }

    /**
     * Service creation factory
     *
     * @param string $objType The name of the type of object the new entity represents
     * @return EntityInterface
     */
    public function create($objType)
    {
        $obj = false;

        // First convert object name to file name - camelCase with upper case first
        $className = ucfirst($objType);
        if (strpos($objType, "_") !== false) {
            $parts = explode("_", $className);
            $className = "";
            foreach ($parts as $word) {
                $className .= ucfirst($word);
            }
        }
        $className = "\\Netric\\Entity\\ObjType\\" . $className . "Factory";

        // Use factory if it exists
        if (class_exists($className)) {
            return $className::create($this->serviceManager);
        }

        $def = $this->serviceManager->get(EntityDefinitionLoaderFactory::class)->get($objType);
        $entityLoader = $this->serviceManager->get(EntityLoaderFactory::class);

        // TODO: if !$def then throw an exception
        return new Entity($def, $entityLoader);
    }

    /**
     * Create a new entity from a definition id
     *
     * @param string $entityDefinitionId
     * @return EntityInterface
     */
    public function createEntityFromDefinitionId(string $entityDefinitionId): EntityInterface
    {
        $def = $this->serviceManager->get(EntityDefinitionLoaderFactory::class)->getById($entityDefinitionId);
        return $this->create($def->getObjType());
    }
}
