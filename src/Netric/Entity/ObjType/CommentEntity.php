<?php

/**
 * Provides extensions for the Comment object
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\EntityDefinition\Field;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Ramsey\Uuid\Uuid;

/**
 * Comment represents a single comment on any entity
 */
class CommentEntity extends Entity implements EntityInterface
{
    /**
     * Callback function used for derrived subclasses
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(AccountServiceManagerInterface $sm)
    {
        $entityLoader = $sm->get(EntityLoaderFactory::class);
        $currentUser = $sm->getAccount()->getUser();
        $objReference = $this->getValue('obj_reference');
        $entityCommentedOn = $entityLoader->getByGuid($objReference);

        // Set comments associations to all directly associated objects if new
        if ($entityCommentedOn) {
            // Update the num_comments field of the entity we are commenting on
            if (!$this->getEntityId() || ($this->isDeleted() && $this->fieldValueChanged('f_deleted'))) {
                // Determine if we should increment or decrement
                $added = ($this->isDeleted()) ? false : true;
                $entityCommentedOn->setHasComments($added);
            }

            // Add object references to the list of associations
            $this->addMultiValue("associations", $entityCommentedOn->getEntityId(), $entityCommentedOn->getName());

            /*
             * Copy associations for everything but status updates
             * since status updates are really just like comments themselves.
             * Only do this if it's a new comment - only needed once
             */
            if ($entityCommentedOn->getObjType() != ObjectTypes::STATUS_UPDATE && !$this->getEntityId()) {
                $fields = $entityCommentedOn->getDefinition()->getFields();
                foreach ($fields as $field) {
                    if ($field->type == FIELD::TYPE_OBJECT && ($field->subtype || $field->name === "obj_reference")) {
                        $val = $entityCommentedOn->getValue($field->name);
                        if (Uuid::isValid($val)) {
                            $this->addMultiValue("associations", $val);
                        }
                    }
                }
            }

            // Make sure followers of this comment are synchronized with the entity
            $this->syncFollowers($entityCommentedOn);

            // Save the entity we are commenting on if there were changes
            if ($entityCommentedOn->isDirty()) {
                $entityLoader->save($entityCommentedOn);
            }

            // Set who this was sent by if not already set
            if (!$this->getValue('sent_by')) {
                $this->setValue("sent_by", $currentUser->getEntityId());
            }
        }
    }

    /**
     * Callback function used for derrived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onAfterSave(AccountServiceManagerInterface $sm)
    {
    }

    /**
     * Called right before the entity is purged (hard delete)
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeDeleteHard(AccountServiceManagerInterface $sm)
    {
    }
}
