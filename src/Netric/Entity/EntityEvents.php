<?php

namespace Netric\Entity;

/**
 * Publush events used when interacting with entities so we are
 * not using plain text versions of these all over.
 */
interface EntityEvents
{
    /**
     * Events that are used to act on entities
     */
    const EVENT_CREATE = 'create';
    const EVENT_UPDATE = 'update';
    const EVENT_DELETE = 'delete';
}
