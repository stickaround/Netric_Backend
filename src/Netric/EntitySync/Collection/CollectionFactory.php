<?php

declare(strict_types=1);

namespace Netric\EntitySync\Collection;

use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\EntitySync\EntitySync;
use RuntimeException;
use DateTime;

class CollectionFactory implements CollectionFactoryInterface
{
    /**
     * ServiceLocator for injecting dependencies
     *
     * @var ServiceContainerInterface
     */
    private $serviceLocator = null;

    /**
     * Construct an instance of this factory so we can inject it as a dependency
     *
     * @param ServiceContainerInterface $serviceLocator ServiceLocator for injecting dependencies
     */
    public function __construct(ServiceContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Instantiated version of the static create function
     *
     * @param string $accountId The account that owns the collection
     * @param int $type The type to load as defined by \Netric\EntitySync::COLL_TYPE_*
     * @param array $data Optional data to initialize into the collection
     * @return CollectionInterface
     */
    public function createCollection(string $accountId, int $type, array $data = null)
    {
        return self::create($accountId, $type, $data);
    }

    /**
     * Factory for creating collections and injecting all dependencies
     *
     * @param string $accountId The account that owns the collection
     * @param int $type The type to load as defined by \Netric\EntitySync::COLL_TYPE_*
     * @param array $data Optional data to initialize into the collection
     * @return CollectionInterface
     * @throws \Exception if an unsupported collection type is added
     */
    public function create(string $accountId, int $type, array $data = null)
    {
        $collection = null;

        switch ($type) {
            case EntitySync::COLL_TYPE_ENTITY:
                $collection = $this->serviceLocator->get(EntityCollectionFactory::class);
                break;
            case EntitySync::COLL_TYPE_GROUPING:
                $collection = $this->serviceLocator->get(GroupingCollectionFactory::class);
                break;
            case EntitySync::COLL_TYPE_ENTITYDEF:
                break;
            default:
                throw new RuntimeException("Unrecognized type of entity!");
                break;
        }

        // Initialize data if set
        if ($accountId && $data && $collection) {
            $collection->setAccountId($accountId);
            $collection->fromArray($data);
        }

        return $collection;
    }
}
