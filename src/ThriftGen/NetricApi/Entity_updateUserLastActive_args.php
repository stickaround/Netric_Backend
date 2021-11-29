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

class Entity_updateUserLastActive_args
{
    static public $isValidate = false;

    static public $_TSPEC = array(
        1 => array(
            'var' => 'userId',
            'isRequired' => false,
            'type' => TType::STRING,
        ),
        2 => array(
            'var' => 'accountId',
            'isRequired' => false,
            'type' => TType::STRING,
        ),
        3 => array(
            'var' => 'timestamp',
            'isRequired' => false,
            'type' => TType::I16,
        ),
    );

    /**
     * @var string
     */
    public $userId = null;
    /**
     * @var string
     */
    public $accountId = null;
    /**
     * @var int
     */
    public $timestamp = null;

    public function __construct($vals = null)
    {
        if (is_array($vals)) {
            if (isset($vals['userId'])) {
                $this->userId = $vals['userId'];
            }
            if (isset($vals['accountId'])) {
                $this->accountId = $vals['accountId'];
            }
            if (isset($vals['timestamp'])) {
                $this->timestamp = $vals['timestamp'];
            }
        }
    }

    public function getName()
    {
        return 'Entity_updateUserLastActive_args';
    }


    public function read($input)
    {
        $xfer = 0;
        $fname = null;
        $ftype = 0;
        $fid = 0;
        $xfer += $input->readStructBegin($fname);
        while (true) {
            $xfer += $input->readFieldBegin($fname, $ftype, $fid);
            if ($ftype == TType::STOP) {
                break;
            }
            switch ($fid) {
                case 1:
                    if ($ftype == TType::STRING) {
                        $xfer += $input->readString($this->userId);
                    } else {
                        $xfer += $input->skip($ftype);
                    }
                    break;
                case 2:
                    if ($ftype == TType::STRING) {
                        $xfer += $input->readString($this->accountId);
                    } else {
                        $xfer += $input->skip($ftype);
                    }
                    break;
                case 3:
                    if ($ftype == TType::I16) {
                        $xfer += $input->readI16($this->timestamp);
                    } else {
                        $xfer += $input->skip($ftype);
                    }
                    break;
                default:
                    $xfer += $input->skip($ftype);
                    break;
            }
            $xfer += $input->readFieldEnd();
        }
        $xfer += $input->readStructEnd();
        return $xfer;
    }

    public function write($output)
    {
        $xfer = 0;
        $xfer += $output->writeStructBegin('Entity_updateUserLastActive_args');
        if ($this->userId !== null) {
            $xfer += $output->writeFieldBegin('userId', TType::STRING, 1);
            $xfer += $output->writeString($this->userId);
            $xfer += $output->writeFieldEnd();
        }
        if ($this->accountId !== null) {
            $xfer += $output->writeFieldBegin('accountId', TType::STRING, 2);
            $xfer += $output->writeString($this->accountId);
            $xfer += $output->writeFieldEnd();
        }
        if ($this->timestamp !== null) {
            $xfer += $output->writeFieldBegin('timestamp', TType::I16, 3);
            $xfer += $output->writeI16($this->timestamp);
            $xfer += $output->writeFieldEnd();
        }
        $xfer += $output->writeFieldStop();
        $xfer += $output->writeStructEnd();
        return $xfer;
    }
}
