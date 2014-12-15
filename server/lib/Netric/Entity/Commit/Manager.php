<?php
/*
 * Commit manager handles creating commit records for each update to entities
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */

namespace Netric\Entity\Commit;

/**
 * Description of User
 *
 * @author Sky Stebnicki
 */
class Manager
{
    /**
     * DataMapper
     *
     * @var \Netric\Entity\Commmit\DataMapper\DataMapperInterface
     */
    private $dm = null;

    /**
     * Class constructor
     *
     * @param \Netric\Enity\Commit\DataMapper\DataMapperInterface $dm
     */
    public function __construct(DataMapper\DataMapperInterface $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Generate a new commit id for an object type
     *
     * @param int $objTypeid The object type we are commiting changes to
     * @return bigint A unique and incremental commit id
     */
    public function createCommit($objTypeId)
    {
        $cid = $this->dm->getNextCommitId($objTypeId);
        $this->dm->saveHead($objTypeId, $cid);
        return $cid;
    }

    /**
     * Get the last commit id for an object type
     *
     * @param int $objTypeid The object type to get the head cid for
     * @return bigint The last commit id for an object type
     */
    public function getHeadCommit($objTypeId)
    {
        return $this->dm->getHead($objTypeId);
    }
}
