<?php

declare(strict_types=1);

namespace Netric\Mail\Maildrop;

use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\TicketEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\FileSystem\FileSystem;
use PhpMimeMailParser\Parser as MailParser;

/**
 * Deliver an email message into a ticket for support
 */
class MaildropTicket extends AbstractMaildrop implements MaildropInterface
{
    /**
     * Entity loader
     */
    private EntityLoader $entityLoader;

    /**
     * Filesystem for saving attachments
     */
    private FileSystem $fileSystem;

    /**
     * Index used to search for entities
     *
     * @var IndexInterface
     */
    private IndexInterface $entityIndex;

    /**
     * Get status groupings
     *
     * @var GroupingLoader
     */
    private GroupingLoader $groupingLoader;

    /**
     * Maildrop for delivering comments rather than a ticket if this is a reply
     *
     * @var MaildropInterface
     */
    private MaildropInterface $maildropComment;

    /**
     * Construct the transport service
     *
     * @param EntityLoader $entityLoader Loader to get and save messages
     * @param FileSystem $fileSystem For saving attachments
     * @param IndexInterface $entityIndex The index for querying entities,
     * @param GroupingLoader $groupingLoader For loading mailbox groupings
     * @param MaildropInterface $maildropComment
     */
    public function __construct(
        EntityLoader $entityLoader,
        FileSystem $fileSystem,
        IndexInterface $entityIndex,
        GroupingLoader $groupingLoader,
        MaildropInterface $maildropComment
    ) {
        $this->entityLoader = $entityLoader;
        $this->fileSystem = $fileSystem;
        $this->entityIndex = $entityIndex;
        $this->groupingLoader = $groupingLoader;
        $this->maildropComment = $maildropComment;
    }

    /**
     * The type of entity this maildrop creates
     *
     * @return string one of self::TYPE_
     */
    public function getEntityType(): string
    {
        return MaildropInterface::TYPE_TICKET;
    }

    /**
     * Process an email message into an entity
     *
     * @param string $messgaeFilePath a local message file, this will be deleted after processing
     * @param EmailAccountEntity $emailAccount The account we are delivering the message to
     * @return string UUID of the created enitty
     */
    public function createEntityFromMessage(string $messageFilePath, EmailAccountEntity $emailAccount): string
    {
        // Get the user to deliver for - this is the onwer of the EmailAccount
        $user = $this->entityLoader->getEntityById(
            $emailAccount->getOwnerId(),
            $emailAccount->getAccountId()
        );

        // Ready to deliver the message, create a parser and point it to the email message file
        $parser = new MailParser();
        $parser->setPath($messageFilePath);

        // TODO: check $parser->getHeader('in-reply-to') to see if we're replying to an
        // existing ticket, in which case we should just create a comment.
        if ($this->getInReplyToEntityId($parser)) {
            // Store in variable so we can cleanup
            $ticketId = $this->getInReplyToEntityId($parser);

            // Cleanup resources
            $parser = null;

            // Re-open ticket and set it as open and unseen
            $ticket = $this->entityLoader->getEntityById($ticketId, $emailAccount->getAccountId());
            if ($ticket) {
                $ticket->setValue("is_closed", false);
                $ticket->setValue("is_seen", false); // Needs attention!
                $this->entityLoader->save($ticket, $user);
            }

            return $this->routeMessageToCommentMaildrop(
                $messageFilePath,
                $emailAccount,
                $ticketId
            );
        }

        // First check if the message was flagged as spam by the spam filters
        // TODO: We shhould probably not deliver this?
        // $spamFlagText = $parser->getHeader('x-spam-flag') ? $parser->getHeader('x-spam-flag') : '';
        // $spamFlag = trim(strtolower($spamFlagText)) === "yes";

        // Get ticket bodies
        $plainbody = $parser->getMessageBody('text');
        $htmlbody = $parser->getMessageBody('html');

        if ($htmlbody && !$plainbody) {
            $plainbody = $this->htmlBodyToPlainText($htmlbody);
        }

        // Create new ticket and set the properties
        $ticket = $this->entityLoader->create(
            ObjectTypes::TICKET,
            $emailAccount->getAccountId()
        );
        $ticket->setValue("description", $plainbody);
        $ticket->setValue("name", $parser->getHeader('subject'));
        $ticket->setValue("is_closed", false);
        $ticket->setValue("is_seen", false); // Needs attention!

        // If dropbox_obj_reference is set, it is the ID of a support channel
        if ($emailAccount->getValue('dropbox_obj_reference')) {
            $ticket->setValue("channel_id", $emailAccount->getValue('dropbox_obj_reference'));
        }

        // Set source_id to email
        $sources = $this->groupingLoader->get(ObjectTypes::TICKET . '/source_id', $emailAccount->getAccountId());
        $emailSource = $sources->getByName(TicketEntity::SOURCE_EMAIL);
        $ticket->setValue('source_id', $emailSource->getGroupId(), $emailSource->getName());

        // Try to get a sending user
        $fromUser = $this->getOrCreateFromUser($parser, $user);
        if ($fromUser->getValue('contact_id')) {
            $ticket->setValue('contact_id', $fromUser->getValue('contact_id'));
        }

        // Add follower for notifications (if public)
        $ticket->addMultiValue('followers', $fromUser->getEntityId(), $fromUser->getName());

        $attachments = $parser->getAttachments();
        foreach ($attachments as $att) {
            $this->importAttachments($att, $ticket, $user, $this->fileSystem);
        }

        $this->entityLoader->save($ticket, $user);

        // Cleanup resources
        $parser = null;

        return $ticket->getEntityId();
    }

    /**
     * Try to find or create a public user for the sender
     */
    private function getOrCreateFromUser(MailParser $parser, UserEntity $accountUser): EntityInterface
    {
        // returns [["display"=>"test", "address"=>"test@example.com", "is_group"=>false]]
        $arrayHeaderFrom = $parser->getAddresses('from');
        $displayName = $arrayHeaderFrom[0]['display'];
        $address = strtolower($arrayHeaderFrom[0]['address']);

        // Search for existing user with email address
        $query = new EntityQuery(ObjectTypes::USER, $accountUser->getAccountId(), $accountUser->getEntityId());
        $query->andWhere('email')->equals($address);
        $result = $this->entityIndex->executeQuery($query);
        if ($result->getNum() > 0) {
            return $result->getEntity(0);
        }

        // If none is found, create the user
        $sendingUser = $this->entityLoader->create(ObjectTypes::USER, $accountUser->getAccountId());
        $sendingUser->setValue('type', UserEntity::TYPE_PUBLIC);
        $sendingUser->setValue('name', str_replace('@', ".", $address));
        $sendingUser->setValue('full_name', $displayName);
        $sendingUser->setValue('email', $address);
        $this->entityLoader->save($sendingUser, $accountUser);
        return $sendingUser;
    }

    /**
     * See if we can get an entity ID out of the value of in-reply-to
     *
     * When a notification of a ticket, or a reply to a ticket, is sent
     * we will put the entity id of the ticket in the 'In-Reply-To' header with
     * the format: <[uuid-of-ticket]>
     *
     * @param MailParser $parser
     * @return string
     */
    private function getInReplyToEntityId(MailParser $parser): string
    {
        $inReplyTo = $parser->getHeader('in-reply-to');

        if (empty($inReplyTo)) {
            return '';
        }

        // Strip <> from the value
        if ($inReplyTo[0] === "<") {
            $innerHeader = substr($inReplyTo, 1, -1);
            // Make sure we data before and after @
            $ourterParts = explode('@', $innerHeader);
            if (count($ourterParts) !== 2) {
                return '';
            }

            // Message IDs from netric comments will be [uuid-of-object].[uuid-of-comment]
            $leftParts = explode('.', $ourterParts[0]);
            if (count($leftParts) !== 2) {
                return '';
            }

            // Return the referenced ticket
            return $leftParts[0];
        }

        // Malformed
        return '';
    }

    /**
     * Route this message to deliver a comment on a ticket that already exists
     *
     * @param string $messageFilePath
     * @param EmailAccountEntity $emailAccount
     * @param string $inReplyTo
     * @return string UUID of comment
     */
    private function routeMessageToCommentMaildrop(
        string $messageFilePath,
        EmailAccountEntity $emailAccount,
        string $inReplyTo
    ): string {
        // Create a dynamic email account (we never save it though)
        $commentAccount = $this->entityLoader->create(ObjectTypes::EMAIL_ACCOUNT, $emailAccount->getAccountId());
        $commentAccount->setValue('type', EmailAccountEntity::TYPE_DROPBOX);
        $commentAccount->setvalue('address', $emailAccount->getValue('address'));
        $commentAccount->setValue('dropbox_create_type', MaildropInterface::TYPE_COMMENT);
        $commentAccount->setValue('dropbox_obj_reference', $inReplyTo);

        return $this->maildropComment->createEntityFromMessage($messageFilePath, $commentAccount);
    }
}
