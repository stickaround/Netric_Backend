<?php

declare(strict_types=1);

namespace Netric\Handler;

use Netric\Entity\EntityLoader;
use NetricApi\EntityIf;

class EntityHandler implements EntityIf
{
    /**
     * Loader used for getting and saving an entity
     */
    private EntityLoader $entityLoader;

    /**
     * Hanlder constructor
     *
     * @param EntityLoader $entityLoader
     */
    public function __construct(EntityLoader $entityLoader)
    {
        $this->entityLoader = $entityLoader;
    }

    /**
     * Update list of users who have seen an entity
     *
     * @param string $entityId
     * @param string $userId
     * @param string $accountId
     * @return void
     */
    public function setEntitySeenBy($entityId, $userId, $accountId): void
    {
        // Get entity and user and set the seen_by field
        $entity = $this->entityLoader->getEntityById($entityId, $accountId);
        $user = $this->entityLoader->getEntityById($userId, $accountId);
        $entity->addMultiValue('seen_by', $userId, $user->getName());
        $this->entityLoader->save($entity, $user);
    }
}
