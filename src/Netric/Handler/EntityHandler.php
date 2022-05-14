<?php

declare(strict_types=1);

namespace Netric\Handler;

use Netric\Entity\EntityLoader;
use Netric\Permissions\DaclLoader;
use Netric\Stats\StatsPublisher;
use NetricApi\EntityIf;
use NetricApi\ErrorException;
use NetricApi\InvalidArgument;
use Ramsey\Uuid\Uuid;

class EntityHandler implements EntityIf
{
    /**
     * Loader used for getting and saving an entity
     */
    private EntityLoader $entityLoader;

    /**
     * Get DACLs to make sure entities are secured
     *
     * @param EntityLoader $entityLoader
     * @param DaclLoader $daclLoader
     */
    private DaclLoader $daclLoader;

    /**
     * Hanlder constructor
     *
     * @param EntityLoader $entityLoader
     */
    public function __construct(EntityLoader $entityLoader, DaclLoader $daclLoader)
    {
        $this->entityLoader = $entityLoader;
        $this->daclLoader = $daclLoader;
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

        // Log stats so we can track how many times this is called
        StatsPublisher::increment("thrift,handler=entity,function=setEntitySeenBy");
    }

    /**
     * Update the user last active
     *
     * @param string $userId The id of the user we are working with
     * @param string $accountId The account id of the user
     * @param int $timestamp The last activity of the user
     * @return void
     */
    public function updateUserLastActive($userId, $accountId, $timestamp): void
    {
        // Handle empty params
        if (empty($userId) || empty($accountId) || empty($timestamp)) {
            throw new InvalidArgument("Cannot be null: ($userId, $accountId, $timestamp)");
        }

        // Get user entity and update the last activity
        $user = $this->entityLoader->getEntityById($userId, $accountId);
        $user->setValue("last_active", $timestamp);
        $this->entityLoader->save($user, $user);

        // Log stats so we can track how many times this is called
        StatsPublisher::increment("thrift,handler=entity,function=setEntitySeenBy");
    }

    /**
     * Get an entity and return the json encoded data
     *
     * @param [type] $entityId
     * @param [type] $userId
     * @param [type] $accountId
     * @return string
     */
    public function getEntityDataById($entityId, $userId, $accountId): string
    {
        $user = $this->entityLoader->getEntityById($userId, $accountId);
        if (!$user) {
            $exception = new ErrorException();
            $exception->message = "Failed to load user for $userId";
            throw $exception;
        }

        // Make sure the entityId is valid
        if (!Uuid::isValid($entityId)) {
            $exception = new ErrorException();
            $exception->message = "entityId must be a valid UUID: $entityId";
            throw $exception;
        }

        StatsPublisher::increment("thrift,handler=entity,function=getEntityDataById");

        // Get the entity utilizing whatever params were passed in
        $entity = $this->entityLoader->getEntityById($entityId, $accountId);

        // No entity found
        if (!$entity) {
            $exception = new ErrorException();
            $exception->message = "Could not load entity: $entityId";
            throw $exception;
        }

        // Export the entity to array if the current user has access to view this entity
        $entityDataApplied = $entity->toArrayWithApplied($user);
        $entityData = $entityDataApplied;

        // Put the current DACL in a special field to keep it from being overwritten when the entity is saved
        $dacl = $this->daclLoader->getForEntity($entity, $user);
        $currentUserPermissions = $dacl->getUserPermissions($user, $entity);

        // If the user does not have view access to this entity, then reset
        // all the data so they don't see anything they shouldn't
        if ($currentUserPermissions['view'] === false) {
            $entityData = $entity->toArrayWithNoPermissions();
        }

        // Add the DACL that was used to vet permissions
        $entityData["applied_dacl"] = $dacl->toArray();

        // Add the current user's permissions when checked against the DACL
        $entityData['applied_user_permissions'] = $currentUserPermissions;

        return json_encode($entityData);
    }
}
