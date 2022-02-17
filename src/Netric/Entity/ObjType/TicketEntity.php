<?php

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition\EntityDefinition;

/**
 * Ticket represents a single ticket entity
 */
class TicketEntity extends Entity implements EntityInterface
{
    /**
     * Constant statuses
     */
    const STATUS_NEW = 'New';
    const STATUS_IN_PROGRESS = "In-Progress"; // Agent is working on it
    const STATUS_WAITING = 'Waiting'; // Waiting for requestor response
    const STATUS_ON_HOLD = 'On-hold'; // Waiting for something internally
    const STATUS_SOLVED = 'Solved'; // Issues was resolved
    const STATUS_UNSOLVABLE = 'Unsolvable'; // Nothing can be done

    /**
     * Constant Priorities
     */
    const PRIORITY_HIGH = 'High';
    const PRIORITY_MEDIUM = 'Medium';
    const PRIORITY_LOW = 'Low';

    /**
     * Sources - where tickets come form
     */
    const SOURCE_EMAIL = 'Email';
    // TODO: Soon we'll be adding
    // sms, facebook, twitter, website
}
