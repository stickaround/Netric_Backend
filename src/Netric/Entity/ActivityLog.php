<?php

namespace Netric\Entity;

use Netric\Entity\ObjType\ActivityEntity;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\Field;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Log\LogInterface;
use Netric\EntityDefinition\ObjectTypes;

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
        GroupingLoader $groupingLoader
    ) {
        $this->log = $log;
        $this->entityLoader = $entityLoader;
        $this->groupingLoader = $groupingLoader;
    }

    /**
     * Log an activity performed on an entity
     *
     * Theory of operation includes three main elements:
     *  subject (what did the action)
     *  verb (what the action was)
     *  object (what the verb was performed on), notes
     *
     * @param UserEntity $user The entity performing the action - usually a user
     * @param string $verb The action performed from EntityEvents::EVENT_*
     * @param EntityInterface $object The entity being acted on
     * @return EntityInterface|null The created activity or null on failure
     */
    public function log(UserEntity $user, string $verb, EntityInterface $object)
    {
        $objDef = $object->getDefinition();
        $objType = $objDef->getObjType();

        // We don't add activities of activities - that could create an endless loop
        // We also don't really want notifications causing activity log entries either
        if ($objType == ObjectTypes::ACTIVITY || $objType == ObjectTypes::NOTIFICATION) {
            return null;
        }

        /*
         * Get the name of the object acted on.
         * Since activities are entities also, we use the name of the
         * object acted on as the name of the activity.
         */
        $referencedName = $object->getName();
        $objReference = $object->getValue("obj_reference");

        // If we created a comment, then get the name from the object commented on
        if (($objType === ObjectTypes::COMMENT) && $objReference) {
            // Get the referenced entity
            $entityReferenced = $this->entityLoader->getEntityById($objReference, $user->getAccountId());

            if ($entityReferenced) {
                // Only if the entity exists
                $referencedName = $entityReferenced->getName();
                // Update the obj_reference for the activity to be the object commented on
                $objReference = $entityReferenced->getValue('obj_reference');
            }
        }

        // Set the name
        $name = $user->getName();

        // Add the verb description
        $name .= " " . $this->getVerbDescription($verb, $object);

        // Add the referenced name
        $name .= " " . $referencedName;

        // Get notes from the entity
        $notes = "";
        if ($verb == EntityEvents::EVENT_CREATE) {
            $notes = $object->getChangeLogDescription();
        }
        if ($verb == EntityEvents::EVENT_UPDATE) {
            $notes = $object->getDescription();
        }

        $actEntity = $this->entityLoader->create(ObjectTypes::ACTIVITY, $user->getAccountId());
        $actEntity->setValue("name", $name);
        $actEntity->setValue("notes", $notes);
        $actEntity->setValue("verb", $verb);

        // If the entity we acted on is private, then mark this activity as private
        $actEntity->setValue("is_private", $objDef->isPrivate());

        // The entity that is the object of this action
        $actEntity->setValue("obj_reference", $object->getEntityId());

        /*
         * obj_reference is a reference to the entity object being acted on.
         * If we are acting on a comment, then record the action as being on the object
         * being commented on, otherwise just record the action on the object itself.
         */
        if ($objType == ObjectTypes::COMMENT && $object->getValue("obj_reference")) {
            $actEntity->setValue("obj_reference", $object->getValue("obj_reference"));
        }

        // Log the current user as the actor/subject of the verb
        $actEntity->setValue("subject", $user->getEntityId(), $user->getName());

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
            if ($field->type == Field::TYPE_OBJECT && $objReference) {
                $referencedEntity = $this->entityLoader->getEntityById($objReference, $user->getAccountId());

                if ($referencedEntity) {
                    $actEntity->addMultiValue("associations", $referencedEntity->getEntityId(), $referencedEntity->getName());
                }
            }
        }

        // Associate user with the entity
        if ($user->getEntityId()) {
            $actEntity->addMultiValue(
                "associations",
                $user->getEntityId(),
                $user->getName()
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
        $level = ($user && $user->isSystem()) ? 1 : $objDef->defaultActivityLevel;
        $actEntity->setValue("level", $level);

        // Try saving the new activity
        if ($this->entityLoader->save($actEntity, $user)) {
            return $actEntity;
        }

        return null;
    }

    /**
     * Create a human readable description of the verb/action performed
     *
     * @param string $verb
     * @param EntityInterface $object
     * @return string
     */
    private function getVerbDescription(string $verb, EntityInterface $object): string
    {
        $typeName = strtolower($object->getDefinition()->getTitle());

        // If this is a new comment then return 'commented on' instead of 'created'
        if ($verb === EntityEvents::EVENT_CREATE &&
            $object->getDefinition()->getObjType() == ObjectTypes::COMMENT
        ) {
            return 'commented on ' . $typeName;
        }

        if ($verb === EntityEvents::EVENT_CREATE) {
            return 'created ' . $typeName;
        }

        if ($verb === EntityEvents::EVENT_UPDATE) {
            return 'updated ' . $typeName;
        }

        if ($verb === EntityEvents::EVENT_DELETE) {
            return 'deleted ' . $typeName;
        }

        // Default to just returning the verb
        return $verb . ' ' . $typeName;
    }
}
