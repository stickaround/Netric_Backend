<?php

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;

class ContactEntity extends Entity implements EntityInterface
{
    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     */
    public function __construct(EntityDefinition $def)
    {
        parent::__construct($def);
    }
}
