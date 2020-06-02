<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\Notifier;

use Netric\Entity\Entity;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityInterface;
use Netric\EntityQuery;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\ActivityEntity;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityDefinition\ObjectTypes;
use Ramsey\Uuid\Uuid;

/**
 * Manages notifications to followers of an entity
 *
 * Example for comment:
 *
 *  $comment = $entityLoader->create("comment");
 *  $comment->setValue("comment", "[user:1:Test]"); // tag to send notice to user id 1
 *  $entityLoader->save($comment);
 *  $notifier = $sl->get("Netric/Entity/Notifier/Notifier");
 *  $notifier->send($comment, "create");
 *
 * This will create a new unread notification for user id 1 if they are not the
 * ones creating the comment. Users do not need to be notified of comments they add
 * or updates they performed on entities.
 */
class Notifier
{
    /**
     * Current user
     *
     * @var UserEntity
     */
    private $user = null;

    /**
     * Entity loader for getting and saving entities
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * An entity index for querying existing notifications
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * Class constructor and dependency setter
     *
     * @param UserEntity $user The current authenticated user
     * @param EntityLoader $entityLoader To create, find, and save entities
     * @param IndexInterface $index An entity index for querying existing notifications
     */
    public function __construct(UserEntity $user, EntityLoader $entityLoader, IndexInterface $index)
    {
        $this->user = $user;
        $this->entityLoader = $entityLoader;
        $this->entityIndex = $index;
    }

    /**
     * Send notifications to followers of an entity
     *
     * @param EntityInterface $entity The entity that was just acted on
     * @param string $event The event that is triggering from ActivityEntity::VERB_*
     * @return int[] List of notification entities created or updated
     */
    public function send(EntityInterface $entity, $event)
    {
        $objType = $entity->getDefinition()->getObjType();

        // Array of notification entities we either create or update below
        $notificationIds = array();

        // We obviously never want to send notifications about notifications or activities
        if ($objType == ObjectTypes::NOTIFICATION || $objType == ObjectTypes::ACTIVITY) {
            return $notificationIds;
        }

        $objReference = $entity->getGuid();
        $description = $entity->getChangeLogDescription();
        /*
         * Get the object reference which is the entity this notice is about.
         * If this is a comment we are adding a notification for, then update
         * the object reference of the notification to point to the entity being
         * commented on rather than the comment itself. That way when the user
         * clicks on the link for the notification, it will take them to the
         * entity being commented on.
         */
        if ($objType == ObjectTypes::COMMENT) {
            $objReference = $entity->getValue("obj_reference");
            $ownerName = $entity->getValueName("owner_id");
            $description = "$ownerName added a comment: " . $entity->getValue("comment");
        }

        // Get a human-readable name to use for this notification
        $name = $this->getNameFromEventVerb($event, $entity->getDefinition()->getTitle());

        // Get followers of the referenced entity
        $followers = $this->getInterestedUsers($entity);

        // If no values, then return empty array
        if (!is_array($followers)) {
            return $notificationIds;
        }

        foreach ($followers as $userGuid) {
            // If the follower guid is not a valid guid, then we try to look for its user entity
            if (!Uuid::isValid($userGuid)) {
                $userEntity = $this->entityLoader->get(ObjectTypes::USER, $userGuid);

                if ($userEntity) {
                    $userGuid = $userEntity->getGuid();
                }
            }

            /*
             * Create a new notification if it is not the current user - we don't want
             * to notify a user if they are the one performing the action.
             *
             * We also do not want to send notifications to users if the system does
             * something like adding a new email.
             */
            if (Uuid::isValid($userGuid) && $userGuid != $this->user->getGuid() && !$this->user->isSystem() && !$this->user->isAnonymous()) {
                // Create new notification, or update an existing unseen one
                $notification = $this->getNotification($objReference, $userGuid);
                $notification->setValue("name", $name);
                $notification->setValue("description", $description);
                $notification->setValue("f_email", true);
                $notification->setValue("f_popup", false);
                $notification->setValue("f_sms", false);
                $notification->setValue("f_seen", false);
                
                $notificationIds[] = $this->entityLoader->save($notification);
            }
        }

        return $notificationIds;
    }

    /**
     * If a user views an entity, we should mark any unread notifications as read
     *
     * An example of this might be that we send a notification to a user that
     * a new task was created for them, then they go view the task by clicking
     * on the link in the email. We would expect this function to mark the notification
     * we sent them as read when they view the task.
     *
     * @param EntityInterface $entity The entity that was seen by a user
     * @param UserEntity $user Optional user to set seen for, otherwise use current logged in user
     */
    public function markNotificationsSeen(EntityInterface $entity, UserEntity $user = null)
    {
        // If we did not manually pass a user, then use the current user
        if (!$user) {
            $user = $this->user;
        }

        $query = new EntityQuery(ObjectTypes::NOTIFICATION);
        $query->where("owner_id")->equals($user->getId());
        $query->andWhere("obj_reference")->equals($entity->getGuid());
        $query->andWhere("f_seen")->equals(false);
        $result = $this->entityIndex->executeQuery($query);
        $num = $result->getNum();
        for ($i = 0; $i < $num; $i++) {
            $notification = $result->getEntity($i);
            $notification->setValue("f_seen", true);
            $this->entityLoader->save($notification);
        }
    }

    /**
     * Either get an existing notification if unseen, or create a new one for $objReference
     *
     * @param string $objReference The guid of the entity reference
     * @param string $userGuid The guid of the user
     * @return EntityInterface
     */
    private function getNotification(string $objReference, string $userGuid)
    {
        // Initialize the notification variable to return
        $notification = null;

        /*
         * Query past notification entities to see if an entity is outstanding
         * and not yet seen for this entity/object reference.
         */
        $query = new EntityQuery(ObjectTypes::NOTIFICATION);
        $query->where("owner_id")->equals($userGuid);
        $query->andWhere("obj_reference")->equals($objReference);
        $query->andWhere("creator_id")->equals($this->user->getGuid());
        $query->andWhere("f_seen")->equals(false);

        // Make sure we get the latest notification if there are multiple
        $query->orderBy("ts_updated", EntityQuery\OrderBy::DESCENDING);

        // Get the results
        $result = $this->entityIndex->executeQuery($query);
        if ($result->getNum()) {
            $notification = $result->getEntity(0);
        } else {
            // There are no outstanding/unseen notifications, create a new one
            $notification = $this->entityLoader->create(ObjectTypes::NOTIFICATION);
            $notification->setValue("obj_reference", $objReference);
            $notification->setValue("owner_id", $userGuid);
            $notification->setValue("creator_id", $this->user->getGuid(), $this->user->getName());
        }

        return $notification;
    }

    /**
     * Construct a human-readable name from the event verb
     *
     * @param string $event The action taken on the entity
     * @param string $objTypeTitle The title of the object type we are acting on
     * @return string The title for the notification
     */
    private function getNameFromEventVerb($event, $objTypeTitle)
    {
        switch ($event) {
            case ActivityEntity::VERB_CREATED:
                return "Added " . $objTypeTitle;
            case ActivityEntity::VERB_SENT:
                return "Sent " . $objTypeTitle;
            default:
                return ucfirst($event) . "d " . $objTypeTitle;
        }
    }

    /**
     * Return list of users that should be notified of an event
     *
     * @param EntityInterface $entity
     * @return array
     */
    private function getInterestedUsers(EntityInterface $entity): array
    {
        $objType = $entity->getDefinition()->getObjType();
        $followers = [];

        // Get followers of the referenced entity
        if (is_array($entity->getValue("followers"))) {
            $followers = $entity->getValue("followers");
        }

        /*
         * If the entity being created is a comment, then we want to
         * check the followers of the entity being commented on.
         */
        $objReference = $entity->getValue("obj_reference");
        if ($objType == ObjectTypes::COMMENT && Uuid::isValid($objReference)) {
            $refEntity = $this->entityLoader->getByGuid($objReference);
            if ($refEntity && is_array($refEntity->getValue('followers'))) {
                $followers = array_unique(array_merge($followers, $refEntity->getValue('followers')));
            }
        }

        return $followers;
    }
}