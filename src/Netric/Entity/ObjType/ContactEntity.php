<?php

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;

/**
 * Used mostly to impose strict entity type checking
 */
class ContactEntity extends Entity implements EntityInterface
{
    const STAGE_LEAD = "Lead";
    const STAGE_PROSPECT = "Prospect";
    const STAGE_CUSTOMER = "Customer";
    const STAGE_INACTIVE = "Inactive";
}
