<?php

declare(strict_types=1);

namespace Netric\EntitySync\Commit\DataMapper;

/**
 * Abstract commit datamapper
 */
interface DataMapperInterface
{
    /**
     * Get next id
     *
     * @return int
     */
    public function getNextCommitId(): int;

    /**
     * Set the head commit id for a collection
     *
     * @param string $typekey
     * @param int $cid
     */
    public function saveHead(string $typekey, int $cid);

    /**
     * Get the head commit id for a collection
     *
     * @param string $typekey
     * @return int
     */
    public function getHead(string $typekey): int;
}
