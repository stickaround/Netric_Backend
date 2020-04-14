<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Config\ConfigFactory;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Mail\Transport\TransportInterface;
use Netric\Mail;
use Netric\Mail\Address;
use Netric\Mail\Transport\TransportFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Log\LogFactory;

/**
 * Notification entity
 */
class NotificationEntity extends Entity implements EntityInterface
{
    /**
     * Mail transport for sending messages
     *
     * @var TransportInterface
     */
    private $mailTransport = null;

    /**
     * Callback function used for derived subclasses
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(AccountServiceManagerInterface $sm)
    {
        /*
         * If this is a new notification, then send messages - email, sms.
         * Notifications are almost never updated, and when they are it is usually
         * to indicate that more comments were added but the user has already been notified
         * and not yet seen the entity being commented on, so there is no need to notify
         * them over and over if they have not even seen the last notice.
         */
        if ($this->getValue('obj_reference')) {
            // If the email flag is set, then send an email
            if ($this->getValue("f_email")) {
                $this->sendEmailNotification($sm);
            }

            // If the SMS flag is set, then send sms
            if ($this->getValue("f_sms")) {
                $this->sendSmsNotification();
            }
            return;
        }
    }

    /**
     * Set an alternate transport for sending messages
     *
     * This is useful for unit tests and one-off alternate sending methods
     *
     * @param TransportInterface $mailTransport For sending messages
     */
    public function setMailTransport(TransportInterface $mailTransport)
    {
        $this->mailTransport = $mailTransport;
    }

    /**
     * Send this notice via email to the owner
     *
     * @param AccountServiceManagerInterface $sm Service manager used to load supporting services
     */
    private function sendEmailNotification(AccountServiceManagerInterface $sm)
    {
        // Make sure the notification has an owner or a creator
        if (empty($this->getValue("owner_id")) || empty($this->getValue("creator_id"))) {
            return;
        }

        // If mail transport is not set, then set it here
        if (!$this->mailTransport) {
            $this->mailTransport = $sm->get(TransportFactory::class);
        }

        // Get the user that owns this notice
        $user = $sm->get(EntityLoaderFactory::class)->get(ObjectTypes::USER, $this->getValue("owner_id"));

        // Get the user that triggered this notice
        $creator = $sm->get(EntityLoaderFactory::class)->get(ObjectTypes::USER, $this->getValue("creator_id"));

        // Make sure the user has an email
        if (!$user || !$user->getValue("email")) {
            return;
        }

        // Get the referenced entity
        $objReference = $this->getValue("obj_reference");
        $referencedEntity = $sm->get(EntityLoaderFactory::class)->getByGuid($objReference);
        $def = $referencedEntity->getDefinition();

        $config = $sm->get(ConfigFactory::class);
        $log = $sm->get(LogFactory::class);

        // Set the body
        $body = $creator->getName() . " - " . $this->getName('name') . " on ";
        $body .= date("m/d/Y") . " at " . date("h:iA T") . "\r\n";
        $body .= "---------------------------------------\r\n\r\n";
        $body .= $def->getTitle() . ": " . $referencedEntity->getName();

        // If there is a notification description, then include it in the body
        if ($this->getValue("description")) {
            $body .= "\r\n\r\nDetails: \r";
            $body .= $this->getValue("description");
        }
        
        // Add link to body
        $protocol = ($config->use_https) ? "https://" : "http://";
        $body .= "\r\n\r\nLink: \r";
        $body .= $protocol . $config->application_url . "/browse/" . $referencedEntity->getGuid();
        $body .= "\r\n\r\n---------------------------------------\r\n\r\n";
        $body .= "\r\n\r\nTIP: You can respond by replying to this email.";
        
        // Set from
        $fromEmail = $config->email['noreply'];

        // Add special dropbox that enables users to comment by just replying to an email
        if ($config->email['dropbox_catchall']) {
            $fromEmail = $sm->getAccount()->getName() . "-com-";
            $fromEmail .= $objReference;
            $fromEmail .= $config->email['dropbox_catchall'];
        }

        try {
            $from = $config->email['noreply'];
            $to = $user->getValue("email");
            $subject = $this->getValue("name");

            // Create a new message and send it
            $from = new Address($fromEmail, $creator->getName());
            $message = new Mail\Message();
            $message->addFrom($config->email['noreply']);
            $message->addTo($user->getValue("email"));
            $message->setBody($body);
            $message->setEncoding('UTF-8');
            $message->setSubject($this->getValue("name"));
            $this->mailTransport->send($message);
        } catch (\Exception $ex) {
            /*
             * This should never happen, but in case we cannot send the email for
             * reason we should log it as an error and continue working.
             */
            $log->error("NotificationEntity:: Could not send notification: " . $ex->getMessage(), var_export($config, true));
        }
    }

    /**
     * Send an SMS notice to the owner of this notice
     *
     * @todo Implement an SMS gateway
     */
    private function sendSmsNotification()
    {
        // TODO: Not yet implemented since we have no transport/Gateway for SMS in Netric
    }
}
