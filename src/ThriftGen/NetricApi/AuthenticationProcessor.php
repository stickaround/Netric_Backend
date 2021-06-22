<?php
namespace NetricApi;

/**
 * Autogenerated by Thrift Compiler (0.14.1)
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

class AuthenticationProcessor
{
    protected $handler_ = null;
    public function __construct($handler)
    {
        $this->handler_ = $handler;
    }

    public function process($input, $output)
    {
        $rseqid = 0;
        $fname = null;
        $mtype = 0;

        $input->readMessageBegin($fname, $mtype, $rseqid);
        $methodname = 'process_'.$fname;
        if (!method_exists($this, $methodname)) {
              $input->skip(TType::STRUCT);
              $input->readMessageEnd();
              $x = new TApplicationException('Function '.$fname.' not implemented.', TApplicationException::UNKNOWN_METHOD);
              $output->writeMessageBegin($fname, TMessageType::EXCEPTION, $rseqid);
              $x->write($output);
              $output->writeMessageEnd();
              $output->getTransport()->flush();
              return;
        }
        $this->$methodname($rseqid, $input, $output);
        return true;
    }

    protected function process_isTokenValid($seqid, $input, $output)
    {
        $bin_accel = ($input instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary_after_message_begin');
        if ($bin_accel) {
            $args = thrift_protocol_read_binary_after_message_begin(
                $input,
                '\NetricApi\Authentication_isTokenValid_args',
                $input->isStrictRead()
            );
        } else {
            $args = new \NetricApi\Authentication_isTokenValid_args();
            $args->read($input);
        }
        $input->readMessageEnd();
        $result = new \NetricApi\Authentication_isTokenValid_result();
        $result->success = $this->handler_->isTokenValid($args->token);
        $bin_accel = ($output instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
        if ($bin_accel) {
            thrift_protocol_write_binary(
                $output,
                'isTokenValid',
                TMessageType::REPLY,
                $result,
                $seqid,
                $output->isStrictWrite()
            );
        } else {
            $output->writeMessageBegin('isTokenValid', TMessageType::REPLY, $seqid);
            $result->write($output);
            $output->writeMessageEnd();
            $output->getTransport()->flush();
        }
    }
}