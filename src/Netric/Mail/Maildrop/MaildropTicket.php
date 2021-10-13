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
     * Construct the transport service
     *
     * @param EntityLoader $entityLoader Loader to get and save messages
     * @param FileSystem $fileSystem For saving attachments
     * @param IndexInterface $entityIndex The index for querying entities,
     * @param GroupingLoader $groupingLoader For loading mailbox groupings
     */
    public function __construct(
        EntityLoader $entityLoader,
        FileSystem $fileSystem,
        IndexInterface $entityIndex,
        GroupingLoader $groupingLoader
    ) {
        $this->entityLoader = $entityLoader;
        $this->fileSystem = $fileSystem;
        $this->entityIndex = $entityIndex;
        $this->groupingLoader = $groupingLoader;
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
        $ticket->setValue("f_seen", false); // Needs attention!

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

        // We might use this later to detect replying to another
        //$ticket->setValue("in_reply_to", $parser->getHeader('in-reply-to'));
        // $ticket->setValue("sent_from", $parser->getHeader('from'));

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
}
