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
     * Factory to create an entity
     *
     * @param string $objType The name of the type of object the new entity represents
     * @param string $accountId The ID of the account that will own this entity
     * @return EntityInterface
     */
    public function create(string $objType, string $accountId): EntityInterface
    {
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
            $entity = $className::create($this->serviceManager);
            $entity->setValue('account_id', $accountId);
            return $entity;
        }

        $def = $this->serviceManager->get(EntityDefinitionLoaderFactory::class)->get($objType, $accountId);
        $entityLoader = $this->serviceManager->get(EntityLoaderFactory::class);

        // TODO: if !$def then throw an exception
        $entity = new Entity($def, $entityLoader);
        $entity->setvalue('account_id', $accountId);
        return $entity;
    }

    /**
     * Create a new entity from a definition id
     *
     * @param string $entityDefinitionId
     * @param string $accountId
     * @return EntityInterface
     */
    public function createEntityFromDefinitionId(string $entityDefinitionId, string $accountId): EntityInterface
    {
        $def = $this->serviceManager->get(EntityDefinitionLoaderFactory::class)->getById($entityDefinitionId, $accountId);
        return $this->create($def->getObjType(), $accountId);
    }
}
