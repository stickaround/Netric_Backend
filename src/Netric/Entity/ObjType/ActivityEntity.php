<?php
/**
 * Activity entity extension
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\EntityLoaderFactory;

/**
 * Activty entity used for logging activity logs
 */
class ActivityEntity extends Entity implements EntityInterface
{
    /**
     * Verbs
     *
     * @const int
     */
    const VERB_CREATED = 'created';
    const VERB_UPDATED = 'updated';
    const VERB_DELETED = 'deleted';
    const VERB_READ = 'read';
    const VERB_SHARED = 'shared';
    const VERB_SENT = 'sent';
    const VERB_COMPLETED = 'completed';
    const VERB_APPROVED = 'approved';

    /**
     * Callback function used for derrived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(AccountServiceManagerInterface $sm)
    {
        // Set association for the object which is used for queries
        if ($this->getValue('obj_reference')) {
            $objRef = $this->getValue('obj_reference');
            if ($objRef) {
                $this->addMultiValue(
                    "associations",
                    $objRef,
                    $this->getValueName('obj_reference')
                );
            }
        }

        /*
         * If the activity subject is empty but we have associations
         * Then we will try to get our subect from it
         */
        if (empty($this->getValue("subject")) && !empty($this->getValue('associations'))) {
            $assoc = $this->getValue('associations');

            // Decode the association value and check if we have a valid object reference value
            $assocParts = $this->decodeObjRef($assoc[0]);
            $assocObjType = $assocParts["obj_type"];
            $assocId = $assocParts["id"];
            $assocName = $assocParts["name"];

            // Make sure that we have objType and id before we load the object reference
            if (empty($assocName) && !empty($assocObjType) && !empty($assocId)) {
                $entityLoader = $sm->get(EntityLoaderFactory::class);
                $assocEntity = $entityLoader->get($assocObjType, $assocId);

                if ($assocEntity) {
                    $assocName = $assocEntity->getName();
                } else {
                    $assocName = $assoc[0];
                }
            }

            $this->setValue("subject", $assocName);
        }

        // Make sure the required data is set
        if (empty($this->getValue("subject")) ||
            empty($this->getValue("verb"))) {
            throw new \InvalidArgumentException(
                "subject and verb are required: " .
                $this->getValue("subject") . "," .
                $this->getValue("verb") . "," .
                var_export($this->toArray(), true)
            );
        }
    }
}
