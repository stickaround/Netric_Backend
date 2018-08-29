<?php
/**
 * Abstract commit datamapper that uses relational database
 */
namespace Netric\EntitySync\Commit\DataMapper;

class DataMapperRdb extends DataMapperAbstract
{
    /**
     * Sequence name
     *
     * There is currently no reason to use more than one sequence even though
     * it will be rendering a lot of increments across all kinds of object operations.
     *
     * BIGINT supports 922 quadrillion unique entries which means if we were to
     * give a unique id to every star in the milky way (100 billion stars),
     * then we could track 9.2 million milky way size universes before UID collision!
     *
     * For a real world example, let's assume one account (each account has it's own commit id)
     * was sustaining 100,000 commits per second without pause the whole year. One bigint could
     * keep up with those commits for 2,924,712 years before wrapping.
     */
    private $sSequenceName = "object_commit_seq";

    /**
     * Get next id
     *
     * @return int
     */
    public function getNextCommitId()
    {
        $cid = $this->database->getNextVal($this->sSequenceName);

        // The sequence may not be defined, try creating it
        if (!$cid) {
            $this->database->createSequenceName($this->sSequenceName);

            // After creating the sequence, we can now retrieve the sequence id
            $cid = $this->database->getNextVal($this->sSequenceName);
        }

        return $cid;
    }

    /**
     * Set the head commit id for an object
     *
     * @param string $key
     * @param int $cid
     * @return bool true on success, false on failure
     */
    public function saveHead(string $key, int $cid)
    {
        $headData = ["head_commit_id" => $cid];
        $whereData = ["type_key" => $key];

        // Check to see if this exists already
        $sql = "SELECT head_commit_id FROM object_sync_commit_heads WHERE type_key=:type_key";
        $result = $this->database->query($sql, $whereData);

        if ($result->rowCount()) {
            $this->database->update("object_sync_commit_heads", $headData, $whereData);
        } else {
            $this->database->insert("object_sync_commit_heads", array_merge($headData, $whereData));
        }

        return true;
    }

    /**
     * Get the head commit id for an object type
     *
     * @param string $key
     * @return int
     */
    public function getHead(string $key)
    {
        $sql = "SELECT head_commit_id FROM object_sync_commit_heads WHERE type_key=:type_key";
        $result = $this->database->query($sql, ["type_key" => $key]);

        if ($result->rowCount()) {
            $row = $result->fetch();
            return $row["head_commit_id"];
        } else {
            return 0;
        }
    }
}
