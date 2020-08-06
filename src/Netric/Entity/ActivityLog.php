<?php

/**
 * Activity log for entities
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity;

use Netric\EntityDefinition\Field;
use Netric\Entity\ObjType\ActivityEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\EntityLoader;
use Netric\EntityGroupings;
use Netric\EntityGroupings\Group;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Log\LogInterface;
use Netric\EntityDefinition\ObjectTypes;
use Ramsey\Uuid\Uuid;

/**
 * Class for managing an entity activity log
 */
class ActivityLog
{
    /**
     * Handle to the entity loader for creating and loading entities
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Currently logged in user
     *
     * @var ObjType\UserEntity
     */
    private $currentUser = null;

    /**
     * Loader to get and save entity groupings
     *
     * @var GroupingLoader|null
     */
    private $groupingLoader = null;

    /**
     * Log in case we have errors
     *
     * @var LogInterface
     */
    private $log = null;

    /**
     * Class constructor to set up dependencies
     *
     * @param LogInterface $log
     * @param EntityLoader $entityLoader Loader for getting referenced entities
     * @param GroupingLoader $groupingLoader Loader for getting/setting groupings
     * @param ObjType\UserEntity $currentUser
     */
    public function __construct(
        LogInterface $log,
        EntityLoader $entityLoader,
        GroupingLoader $groupingLoader,
        ObjType\UserEntity $currentUser
    ) {
        $this->log = $log;
        $this->entityLoader = $entityLoader;
        $this->groupingLoader = $groupingLoader;
        $this->currentUser = $currentUser;
    }

    /**
     * Log an activity performed on an entity
     *
     * Theory of operation includes three main elements:
     *  subject (what did the action)
     *  verb (what the action was)
     *  object (what the verb was performed on), notes
     *
     * @param Entity $subject The entity performing the action - usually a user
     * @param string $verb The action performed from ActivityEntity::VERB_*
     * @param Entity $object The entity being acted on
     * @param string $notes Details for the activity
     * @param int $level Optional log level
     * @return EntityInterface|null The created activity or null on failure
     */
    public function log(Entity $subject, $verb, Entity $object, $notes = "", $level = null)
    {
        $objDef = $object->getDefinition();
        $objType = $objDef->getObjType();

        // We don't add activities of activities - that could create an endless loop
        if ($objType == ObjectTypes::ACTIVITY) {
            return null;
        }

        /*
         * Get the name of the object acted on.
         * Since activities are entities also, we use the name of the
         * object acted on as the name of the activity.
         */
        $name = "";
        $objReference = $object->getValue("obj_reference");

        // If we created a comment, then get the name from the object commented on
        if (($objType == ObjectTypes::COMMENT) && $objReference) {
            // Get the referenced entity
            $entityReferenced = $this->entityLoader->getEntityById($objReference, $this->currentUser->getAccountId());

            if ($entityReferenced) {
                // Only if the entity exists
                $name = $entityReferenced->getName();
            }
        }

        // Default to the name of the object acted on
        if (!$name) {
            $name = $object->getName();
        }

        // Get notes from the entity
        if (!$notes) {
            $notes = "";
            if ($verb == ActivityEntity::VERB_UPDATED) {
                $notes = $object->getChangeLogDescription();
            }
            if ($verb == ActivityEntity::VERB_CREATED) {
                $notes = $object->getDescription();
            }
        }

        $actEntity = $this->entityLoader->create(ObjectTypes::ACTIVITY, $subject->getAccountId());
        $actEntity->setValue("name", $name);
        $actEntity->setValue("notes", $notes);
        $actEntity->setValue("verb", $verb);

        // If the object we acted on is private, then mark this activity as private
        $actEntity->setValue("f_private", $objDef->isPrivate);

        // In most cases we reference the object being acted on
        $actEntity->setValue("obj_reference", $object->getEntityId());

        /*
         * obj_reference is a reference to the entity object being acted on.
         * If we are acting on a comment, then record the action as being on the object
         * being commented on, otherwise just record the action on the object itself.
         */
        if ($objType == ObjectTypes::COMMENT && $object->getValue("obj_reference")) {
            $actEntity->setValue("obj_reference", $object->getValue("obj_reference"));
        }

        // Get the type of activity which is just a grouping entiry for the objType
        $group = $this->getActivityTypeGroup($objDef);

        $actEntity->setValue("type_id", $group->getGroupId(), $group->name);

        // Log which entity performed the action
        $actEntity->setValue("subject", $subject->getEntityId(), $subject->getName());

        // Add referenced entity to activity associations
        $actEntity->addMultiValue("associations", $object->getEntityId(), $object->getName());

        /*
         * Copy associations from the referenced object so that
         * we can associate this activity log with all associated entities
         */
        $associations = $object->getValue("associations");
        if (is_array($associations) && count($associations)) {
            foreach ($associations as $assoc) {
                $assocName = $object->getValue("associations", $assoc);
                $actEntity->addMultiValue("associations", $assoc, $assocName);
            }
        }

        /*
         * Now associate activity with all referenced objects not in 'associations'
         * which should technically never happen, but better safe than sorry.
         */
        $fields = $objDef->getFields();
        foreach ($fields as $field) {
            $objReference = $object->getValue($field->name);
            if ($field->type == FIELD::TYPE_OBJECT && $objReference) {
                $referencedEntity = $this->entityLoader->getEntityById($objReference, $this->currentUser->getAccountId());

                if ($referencedEntity) {
                    $actEntity->addMultiValue("associations", $referencedEntity->getEntityId(), $referencedEntity->getName());
                }
            }
        }

        // Associate with the currently active user
        if ($this->currentUser) {
            $actEntity->addMultiValue(
                "associations",
                $this->currentUser->getEntityId(),
                $this->currentUser->getName()
            );
        }

        // If we're working with a comment copy attachments
        if ($objType == ObjectTypes::COMMENT) {
            $attachments = $object->getValue("attachments");
            if (is_array($attachments) && count($attachments)) {
                foreach ($attachments as $attId) {
                    $attName = $object->getValueName("attachments", $attId);
                    $actEntity->addMultiValue("attachments", $attId, $attName);
                }
            }
        }

        // Now set level - if system activity then put it low to keep logs clean
        $level = ($this->currentUser && $this->currentUser->isSystem()) ? 1 : $objDef->defaultActivityLevel;
        $actEntity->setValue("level", $level);

        // Try saving the new activity
        try {
            if ($this->entityLoader->save($actEntity, $this->currentUser)) {
                return $actEntity;
            }

            // Return failure
            return null;
        } catch (\InvalidArgumentException $ex) {
            // There was a problem with the activity and it should not have been saved
            // But since activities are non-critical we will continue and log the error
            // $this->log->error(
            //     'Could not save activity for ' .
            //         $object->getObjRef() . ' - ' . $actEntity->getValue('obj_reference') . ', error:' .
            //         $ex->getMessage()
            // );
            // Commented the above out because re-deleting an entity is apparently common in unit tests
            return null;
        }
    }

    /**
     * Get the activity grouping id for a given objType
     *
     * @param EntityDefinition $objDef The type of object to get the grouping type for
     * @param bool $createIfMissing If true then create a grouping if missing
     * @return Group
     */
    private function getActivityTypeGroup(EntityDefinition $objDef, $createIfMissing = true)
    {
        $groupings = $this->groupingLoader->get(ObjectTypes::ACTIVITY . "/type_id");

        $existing = $groupings->getByName($objDef->title);
        if ($existing) {
            return $existing;
        }

        if (!$createIfMissing) {
            return null;
        }

        // This is a new type grouping, add it
        $group = new Group();
        $group->name = $objDef->title;
        $groupings->add($group);
        $this->groupingLoader->save($groupings);

        // Return the newly created group id
        return $group;
    }
}
