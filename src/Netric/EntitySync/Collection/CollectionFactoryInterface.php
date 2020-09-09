<?php

declare(strict_types=1);

/**
 * Collection factory interface
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\EntitySync\Collection;

interface CollectionFactoryInterface
{
    /**
     * Factory for creating collections and injecting all dependencies
     * 
     * @param string $accountId The account that owns the collection
     * @param int $type The type to load as defined by \Netric\EntitySync::COLL_TYPE_*
     * @param array $data Optional data to initialize into the collection
     * @return CollectionInterface
     * @throws \Exception if an unsupported collection type is added
     */
    public function create(string $accountId, int $type, array $data = null);

    /**
     * Instantiated version of the static create function
     *
     * @param string $accountId The account that owns the collection
     * @param int $type The type to load as defined by \Netric\EntitySync::COLL_TYPE_*
     * @param array $data Optional data to initialize into the collection
     * @return CollectionInterface
     */
    public function createCollection(string $accountId, int $type, array $data = null);
}
