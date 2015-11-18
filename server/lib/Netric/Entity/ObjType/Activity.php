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

/**
 * Description of User
 *
 * @author Sky Stebnicki
 */
class Activity extends Entity implements EntityInterface
{
    /**
     * Callback function used for derrived subclasses
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(\Netric\ServiceManager\ServiceLocatorInterface $sm)
    {
        // Set association for the object which is used for queries
        if ($this->getValue('obj_reference'))
        {
            $objRef = $this->getValue('obj_reference');
            if ($objRef)
            {
                $this->addMultiValue("associations", 
                    $objRef, 
                    $this->getValueName('obj_reference')
                );
            }
        }
    }
}
