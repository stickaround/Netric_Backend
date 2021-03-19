<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Config\ConfigFactory;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Mail\Transport\TransportInterface;
use Netric\Mail;
use Netric\Mail\Address;
use Netric\Mail\Transport\TransportFactory;
use Netric\Entity\Notifier\NotificationPusherFactory;
use Netric\Log\LogFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Account\AccountContainerInterface;

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
     * The loader for a specific entity
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader The loader for a specific entity
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     */
    public function __construct(EntityDefinition $def, EntityLoader $entityLoader, AccountContainerInterface $accountContainer)
    {
        $this->entityLoader = $entityLoader;
        $this->accountContainer = $accountContainer;
        parent::__construct($def, $entityLoader);
    }

    /**
     * Callback function used for derived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
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
                $this->sendEmailNotification($serviceLocator, $user);
            }

            // If the SMS flag is set, then send sms
            if ($this->getValue("f_sms")) {
                $this->sendSmsNotification();
            }

            // If the push flag is set, then send push notifications to subscritpions
            if ($this->getValue('f_push')) {
                $this->sendPushNotification($serviceLocator, $user);
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
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    private function sendEmailNotification(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        // Get the account
        $account = $this->accountContainer->loadById($this->getAccountId());

        // Make sure the notification has an owner or a creator
        if (empty($this->getValue("owner_id")) || empty($this->getValue("creator_id"))) {
            return;
        }

        // If mail transport is not set, then set it here
        if (!$this->mailTransport) {
            $this->mailTransport = $serviceLocator->get(TransportFactory::class);
        }

        // Get the user that owns this notice
        $user = $this->entityLoader->getEntityById(
            $this->getValue("owner_id"),
            $user->getAccountId()
        );

        // Get the user that triggered this notice
        $creator = $this->entityLoader->getEntityById(
            $this->getValue("creator_id"),
            $user->getAccountId()
        );

        // Make sure the user has an email
        if (!$user || !$user->getValue("email")) {
            return;
        }

        // Get the referenced entity
        $objReference = $this->getValue("obj_reference");
        $referencedEntity = $this->entityLoader->getEntityById(
            $objReference,
            $user->getAccountId()
        );
        $def = $referencedEntity->getDefinition();

        $config = $serviceLocator->get(ConfigFactory::class);
        $log = $serviceLocator->get(LogFactory::class);

        // Set the body
        $body = $creator->getName() . " - " . $this->getName('name') . " on ";
        $body .= date("m/d/Y") . " at " . date("h:iA T") . "\r\n";
        $body .= "---------------------------------------\r\n\r\n";
        $body .= $def->getTitle() . ": " . $referencedEntity->getName();

        // If there is a notification description, then include it in the body
        $description = $this->getValue("description");
        if ($description) {
            $body .= "\r\n\r\n";

            // If the description is already directed to a user, there is no need to add the Details text
            if (!preg_match('/(directed a comment at you:)/', $description)) {
                $body .= "Details: ";
            }

            $body .= "\r$description";
        }

        // Add link to body
        $body .= "\r\n\r\nLink: \r";
        $body .= $config->application_url . "/browse/" . $referencedEntity->getEntityId();
        $body .= "\r\n\r\n---------------------------------------\r\n\r\n";
        $body .= "\r\n\r\nTIP: You can respond by replying to this email.";

        // Set from
        $fromEmail = $config->email['noreply'];

        // Add special dropbox that enables users to comment by just replying to an email
        if ($config->email['dropbox_catchall']) {
            $fromEmail = $account->getName() . "-com-";
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

    /**
     * Send this notice via email to the owner
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    private function sendPushNotification(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        // Get the account
        $account = $this->accountContainer->loadById($this->getAccountId());

        // Make sure the notification has an owner or a creator
        if (empty($this->getValue("owner_id")) || empty($this->getValue("creator_id"))) {
            return;
        }

        // Get the notification pusher client
        $notificationPusher = $serviceLocator->get(NotificationPusherFactory::class);

        // Get the user that owns this notice
        $user = $this->entityLoader->getEntityById(
            $this->getValue("owner_id"),
            $user->getAccountId()
        );

        // Get the referenced entity
        $referencedEntity = $this->entityLoader->getEntityById(
            $this->getValue("obj_reference"),
            $user->getAccountId()
        );

        // Send
        $notificationPusher->send(
            'netric',
            $user->getEntityId(),
            $this->getValue("name"),
            $this->getValue("description"),
            ['entityId' => $referencedEntity->getEntityId()]
        );
    }
}
