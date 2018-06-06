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

        // Make sure the required data is set
        if (empty($this->getValue("subject")) ||
            empty($this->getValue("verb")) ||
            empty($this->getValue("obj_reference"))) {
            throw new \InvalidArgumentException(
                "subject, verb, and obj_reference are required: " .
                $this->getValue("subject") . "," .
                $this->getValue("verb") . "," .
                $this->getValue("obj_reference") . "," .
                var_export($this->toArray(), true)
            );
        }
    }
}
