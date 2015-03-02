<?php
/**
 * Factory creates entities
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity;

use Netric\ServiceManager;
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
     * @var \Netric\ServiceManager\ServiceLocatorInterface
     */
    private $serviceManager = null;

    /**
     * Class constructor
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator implementation for injecting dependencies
     */
    public function __construct(ServiceManager\ServiceLocatorInterface $sl)
    {
        $this->serviceManager = $sl;
    }

    /**
     * Service creation factory
     *
     * @param string $objType The name of the type of object the new entity represents
     * @return \Netric\Entity|EntityInterface
     */
    public function create($objType)
    {
        $obj = false;

        // First convert object name to file name - camelCase with upper case first
        $className = ucfirst($objType);
        if (strpos($objType, "_") !== false)
        {
            $parts = explode("_", $className);
            $className = "";
            foreach ($parts as $word)
                $className .= ucfirst($word);
        }
        $className = "\\Netric\\Entity\\ObjType\\". $className . "Factory";

        // Use factory if it exists
        if (class_exists($className))
        {
            $obj = $className::create($this->serviceManager);
        }
        else
        {
            $def = $this->serviceManager->get("EntityDefinitionLoader")->get($objType);
            // TODO: if !$def then throw an exception
            $obj = new \Netric\Entity($def);
        }

        return $obj;
    }
}
