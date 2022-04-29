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

class EntityQuery_execute_result
{
    static public $isValidate = false;

    static public $_TSPEC = array(
        0 => array(
            'var' => 'success',
            'isRequired' => false,
            'type' => TType::STRING,
        ),
        1 => array(
            'var' => 'error',
            'isRequired' => false,
            'type' => TType::STRUCT,
            'class' => '\NetricApi\ErrorException',
        ),
        2 => array(
            'var' => 'badRequest',
            'isRequired' => false,
            'type' => TType::STRUCT,
            'class' => '\NetricApi\InvalidArgument',
        ),
    );

    /**
     * @var string
     */
    public $success = null;
    /**
     * @var \NetricApi\ErrorException
     */
    public $error = null;
    /**
     * @var \NetricApi\InvalidArgument
     */
    public $badRequest = null;

    public function __construct($vals = null)
    {
        if (is_array($vals)) {
            if (isset($vals['success'])) {
                $this->success = $vals['success'];
            }
            if (isset($vals['error'])) {
                $this->error = $vals['error'];
            }
            if (isset($vals['badRequest'])) {
                $this->badRequest = $vals['badRequest'];
            }
        }
    }

    public function getName()
    {
        return 'EntityQuery_execute_result';
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
                case 0:
                    if ($ftype == TType::STRING) {
                        $xfer += $input->readString($this->success);
                    } else {
                        $xfer += $input->skip($ftype);
                    }
                    break;
                case 1:
                    if ($ftype == TType::STRUCT) {
                        $this->error = new \NetricApi\ErrorException();
                        $xfer += $this->error->read($input);
                    } else {
                        $xfer += $input->skip($ftype);
                    }
                    break;
                case 2:
                    if ($ftype == TType::STRUCT) {
                        $this->badRequest = new \NetricApi\InvalidArgument();
                        $xfer += $this->badRequest->read($input);
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
        $xfer += $output->writeStructBegin('EntityQuery_execute_result');
        if ($this->success !== null) {
            $xfer += $output->writeFieldBegin('success', TType::STRING, 0);
            $xfer += $output->writeString($this->success);
            $xfer += $output->writeFieldEnd();
        }
        if ($this->error !== null) {
            $xfer += $output->writeFieldBegin('error', TType::STRUCT, 1);
            $xfer += $this->error->write($output);
            $xfer += $output->writeFieldEnd();
        }
        if ($this->badRequest !== null) {
            $xfer += $output->writeFieldBegin('badRequest', TType::STRUCT, 2);
            $xfer += $this->badRequest->write($output);
            $xfer += $output->writeFieldEnd();
        }
        $xfer += $output->writeFieldStop();
        $xfer += $output->writeStructEnd();
        return $xfer;
    }
}
