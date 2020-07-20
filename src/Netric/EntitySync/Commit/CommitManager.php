<?php

declare(strict_types=1);

namespace Netric\EntitySync\Commit;

use Netric\EntitySync\Commit\DataMapper\DataMapperInterface;

/**
 * Manage handles creating, getting, and working with commits
 */
class CommitManager
{
    /**
     * DataMapper
     *
     * @var DataMapperInterface
     */
    private $commitDataMapper = null;

    /**
     * Class constructor
     *
     * @param DataMapperInterface $commitDataMapper
     */
    public function __construct(DataMapperInterface $commitDataMapper)
    {
        $this->commitDataMapper = $commitDataMapper;
    }

    /**
     * Generate a new commit id for a collection
     *
     * @param string $key A unique key representing the collection
     * @return int A unique and incremental commit id
     */
    public function createCommit(string $key): int
    {
        $cid = $this->commitDataMapper->getNextCommitId();
        $this->commitDataMapper->saveHead($key, $cid);
        return $cid;
    }

    /**
     * Get the last commit id for a collection name
     *
     * @param string The name of the collection to get the head commit for
     * @return int The last commit id for an object type
     */
    public function getHeadCommit($key): int
    {
        return $this->commitDataMapper->getHead($key);
    }
}
