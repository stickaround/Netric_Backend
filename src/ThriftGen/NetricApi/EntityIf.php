<?php
namespace NetricApi;

/**
 * Autogenerated by Thrift Compiler (0.14.2)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use Thrift\Base\TBase;
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TProtocol;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Exception\TApplicationException;

/**
 * Entity service
 */
interface EntityIf
{
    /**
     * Indicate that an entity has been seen by a given user
     * 
     * @param string $entityId
     * @param string $userId
     * @param string $accountId
     */
    public function setEntitySeenBy($entityId, $userId, $accountId);
    /**
     * Update the user last active
     * 
     * @param string $userId
     * @param string $accountId
     * @param int $timestamp
     */
    public function updateUserLastActive($userId, $accountId, $timestamp);
}
