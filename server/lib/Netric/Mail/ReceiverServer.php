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
    private function importMessage()
    {
        // 1. Import text into Mail\Message
        // 2. Import Mail\Message into EmailMessageEntity
        // 3. Save the entity to get an ID
        // 4. Upload the original message text and attach to the entity
        // 5. Record an activity if settings permit
    }
}
