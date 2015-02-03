<?php
/**
 * Abstract commit datamapper
 *
 * @category	DataMapper
 * @author		Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright	Copyright (c) 2003-2014 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Entity\Commit\DataMapper;

interface DataMapperInterface
{
   /**
    * Get next id
    *
    * @param int $objectTypeid
    * @return bigint
    */
  public function getNextCommitId($objTyppeId);

   /**
    * Set the head commit id for an object
    *
    * @param int $objectTypeId
    * @param bigint $cid
    */
  public function saveHead($objTyppeId, $cid);

  /**
   * Get the head commit id for an object type
   *
   * @param $objectTypeid
   * @return bigint
   */
  public function getHead($objTypeId);
}