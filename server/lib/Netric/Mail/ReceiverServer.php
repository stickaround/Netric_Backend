<?php
/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2016 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Mail;

use Netric\Error\AbstractHasErrors;
use Netric\Entity\ObjType\EmailMessageEntity;

/**
 * Service responsible for receiving messages and synchronizing with remote mailboxes
 */
class ReceiverService extends AbstractHasErrors
{
    /**
     * Synchronize a mailbox with a remote server
     */
    public function sync()
    {
    }
}