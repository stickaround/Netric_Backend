<?php
/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2016 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Mail;

use Netric\Error\AbstractHasErrors;
use Netric\Entity\ObjType\EmailMessageEntity;

/**
 * Service used for sending email messages
 */
class SenderService extends AbstractHasErrors
{
    /**
     * Handle sending an email message
     *
     * @param EmailMessageEntity $emailMessage
     */
    public function send(EmailMessageEntity $emailMessage)
    {
        // Get Mime Message from the entity
        $message = $emailMessage->toMailMessage();
    }
}
