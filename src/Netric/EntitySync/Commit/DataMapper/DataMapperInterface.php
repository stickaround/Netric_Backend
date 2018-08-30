<?php
/**
 * Abstract commit datamapper
 *
 * @category    DataMapper
 * @author      Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright   Copyright (c) 2003-2014 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\EntitySync\Commit\DataMapper;

interface DataMapperInterface
{
   /**
    * Get next id
    *
    * @return bigint
    */
    public function getNextCommitId();

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
   * @return bigint
   */
    public function getHead(string $typekey);
}
