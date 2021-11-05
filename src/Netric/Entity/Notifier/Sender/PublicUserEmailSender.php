<?php

declare(strict_types=1);

namespace Netric\Entity\Notifier\Sender;

use Netric\Entity\ObjType\NotificationEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoader;
use Netric\Mail\MailSystemInterface;
use Netric\Mail\SenderService;

class PublicUserEmailSender implements NotificationSenderInterface
{
    /**
     * Entity loader to get referenced entities
     *
     * @var EntityLoader
     */
    private EntityLoader $entityLoader;

    /**
     * Used to send emails
     *
     * @var SenderService
     */
    private SenderService $mailSender;

    /**
     * Mailsystem to get addresses and domains mostly
     *
     * @var MailSystemInterface
     */
    private MailSystemInterface $mailSystem;

    /**
     * Constructor
     *
     * @param EntityLoader $entityLoader
     * @param SenderService $mailSender
     * @param MailSystemInterface $mailSystem
     */
    public function __construct(EntityLoader $entityLoader, SenderService $mailSender, MailSystemInterface $mailSystem)
    {
        $this->entityLoader = $entityLoader;
        $this->mailSender = $mailSender;
        $this->mailSystem = $mailSystem;
    }

    /**
     * Send an email notification to a public user
     *
     * @param NotificationEntity $notification
     * @param UserEntity $user
     * @return bool True if a notice was sent, otherwise false (not necessarily an error)
     */
    public function sendNotification(NotificationEntity $notification, UserEntity $user): bool
    {
        // Make sure the notification has an owner or a creator
        if (
            empty($notification->getValue("owner_id")) ||
            empty($notification->getValue("creator_id"))
        ) {
            return false;
        }

        // Get the user that owns this notice
        $user = $this->entityLoader->getEntityById(
            $notification->getValue("owner_id"),
            $user->getAccountId()
        );

        // Get the user that triggered this notice
        $creator = $this->entityLoader->getEntityById(
            $notification->getValue("creator_id"),
            $user->getAccountId()
        );

        // Make sure the user has an email
        if (!$user || !$user->getValue("email")) {
            return false;
        }

        // Get the referenced entity
        $objReference = $notification->getValue("obj_reference");
        $referencedEntity = $this->entityLoader->getEntityById(
            $objReference,
            $user->getAccountId()
        );
        $def = $referencedEntity->getDefinition();

        // Set the body
        $body = "";

        // If there is a notification description, then include it in the body
        $description = $notification->getValue("description");
        if ($description) {
            $body .= "\r\n\r\n";

            // If the description is already directed to a user, there is no need to add the Details text
            if (!preg_match('/(directed a comment at you:)/', $description)) {
                $body .= "Details: ";
            }

            $body .= "\r$description";
        }

        // // Set from
        $fromEmail = 'comment.' . $referencedEntity->getEntityId() . '@aereus.netric.com';

        // TODO: Handle from
        // If this is a support, then we should reply from the email address,
        // otherwise we can use the comment dropbox

        $this->mailSender->send(
            $user->getValue("email"),
            $user->getValue("full_name"),
            $fromEmail,
            "Support",
            $notification->getName('name'),
            $body
        );

        return false;
    }
}
