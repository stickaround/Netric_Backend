<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
// use Netric\Mail;
// use Netric\Mime;
use Netric\FileSystem\FileSystem;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Account\AccountContainerInterface;
use Netric\EntityGroupings\GroupingLoader;

/**
 * Email entity extension
 *
 * Example
 * <code>
 *  $email = $entityLoader->create("email_message", $currentUser->getAccountId());
 *  $email->setValue("from", "sky.stebnicki@aereus.com");
 *  $email->setValue("to", "someone@somewhere.com");
 *  $email->setValue("body", "Hello there");
 *  $email->setValue("body_type", EmailMessageEntity::BODY_TYPE_PLAIN);
 *  $email->addAttachment("/path/to/my/file.txt");
 *  $sender = $serviceManager->get("Netric\Mail\Sender");
 *  $sender->send($email);
 * </code>
 */
class EmailMessageEntity extends Entity implements EntityInterface
{
    /**
     * Loader used to get email threads and attachments
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Entity query index for finding threads
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * FileSystem to work with files
     *
     * @var FileSystem
     */
    private $fileSystem = null;

    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Body types
     */
    const BODY_TYPE_PLAIN = 'plain';
    const BODY_TYPE_HTML = 'html';

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader Loader to get/save entities
     * @param GroupingLoader $groupingLoader Grouping loader
     * @param IndexInterface $entityIndex Index to query entities
     * @param FileSystem $fileSystem Used for working with netric files
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     */
    public function __construct(
        EntityDefinition $def,
        EntityLoader $entityLoader,
        GroupingLoader $groupingLoader,
        IndexInterface $entityIndex,
        FileSystem $fileSystem,
        AccountContainerInterface $accountContainer
    ) {
        $this->entityIndex = $entityIndex;
        $this->fileSystem = $fileSystem;
        $this->accountContainer = $accountContainer;

        parent::__construct($def, $entityLoader, $groupingLoader);
    }

    /**
     * Callback function used for derived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        // Make sure a unique message_id is set
        if (!$this->getValue('message_id')) {
            $this->setValue('message_id', $this->generateMessageId());
        }

        // Update num_attachments
        $attachments = $this->getValue("attachments");
        $this->setValue("num_attachments", (is_array($attachments)) ? count($attachments) : 0);

        // If this email message is not part of a thread, create one
        if (!$this->getValue("thread")) {
            $this->attachToThread();
        } else {
            $this->updateThreadFromMessage();
        }
    }

    /**
     * Callback function used for derived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onAfterSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        if ($this->isArchived()) {
            $thread = $this->getEntityLoader()->getEntityById(
                $this->getValue("thread"),
                $user->getAccountId()
            );

            // Decrement the number of messages in the thread if it exists
            if ($thread) {
                // If this is the last message, then delete the thread
                if (intval($thread->getValue("num_messages")) === 1) {
                    $thread->setValue("num_messages", 0);
                    $this->getEntityLoader()->delete($thread, $user);
                } else {
                    // Otherwise reduce the number of messages
                    $numMessages = $thread->getValue("num_messages");
                    $thread->setValue("num_messages", --$numMessages);
                    $this->getEntityLoader()->save($thread, $user);
                }
            }
        }
    }

    /**
     * Called right before the entity is purged (hard delete)
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeDeleteHard(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        // If purging, then clear the raw file holding our message data
        if ($this->getValue('file_id')) {
            $file = $this->fileSystem->openFileById($this->getValue('file_id'), $user);
            if ($file) {
                $this->fileSystem->deleteFile($file, $user, true);
            }
        }
    }

    /**
     * Called right before the entity is purged (hard delete)
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onAfterDeleteHard(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        $thread = $this->getEntityLoader()->getEntityById(
            $this->getValue("thread"),
            $user->getAccountId()
        );

        /*
         * If this is the last message, then purge the thread.
         * We need to perform the deleting of thread right after we deleted the message, to avoid infinite loop
         */
        if ($thread && intval($thread->getValue("num_messages")) === 1) {
            $this->getEntityLoader()->delete($thread, $user);
        }
    }

    /**
     * Get the HTML version of email body
     *
     * This function will convert plain to HTML if the body
     * is plain text.
     *
     * @return string
     */
    public function getHtmlBody()
    {
        $body = $this->getValue("body");

        if ($this->getValue("body_type") != self::BODY_TYPE_PLAIN) {
            return $body;
        }

        // Replace space with &bnsp; to make sure things look okay
        $body = str_replace(" ", "&nbsp;", $body);

        // Replace tab with three spaces
        $body = str_replace("\t", "&nbsp;&nbsp;&nbsp;", $body);

        // Replace \n new lines with <br />
        $body = nl2br(htmlspecialchars($body));

        // Replace links with HTML links
        $body = preg_replace('/\s(\w+:\/\/)(\S+)/', ' <a href="\\1\\2">\\1\\2</a>', $body);

        // Return converted body
        return $body;
    }

    /**
     * Get the plain text version of email body
     *
     * @return string
     */
    public function getPlainBody()
    {
        if ($this->getValue("body_type") == self::BODY_TYPE_PLAIN) {
            return $this->getValue("body");
        }

        $body = $this->getValue("body");

        // Convert breaks to new lines
        $body = str_replace("<br>", "\n", $body);

        // Convert breaks to new lines
        $body = str_replace("<br />", "\n", $body);

        // Remove css style tags
        $body = preg_replace("/<style.*?<\/style>/is", "", $body);

        // Remove all other html tags
        $body = strip_tags($body);

        // Return the results
        return $body;
    }

    /**
     * Search email threads to see if this message should be part of an existing thread
     *
     * @return EmailThreadEntity If a suitable thread was found
     */
    public function discoverThread()
    {
        $thread = null;

        /*
         * The easiest way to link a thread is to check and see if the created
         * message is in reply to a thread already created. We can probably do
         * a better job of detecting other possible candidates, but this should work
         * at least for cases where the sender includes in-reply-to in the header.
         */
        if (trim($this->getValue("in_reply_to"))) {
            $query = new EntityQuery(ObjectTypes::EMAIL_MESSAGE, $this->getAccountId());
            $query->where("message_id")->equals($this->getValue("in_reply_to"));
            $query->andWhere("owner_id")->equals($this->getValue("owner_id"));
            $results = $this->entityIndex->executeQuery($query);
            if ($results->getNum()) {
                $emailMessage = $results->getEntity(0);
                $thread = $this->getEntityLoader()->getEntityById($emailMessage->getValue("thread"), $this->getAccountId());
            }
        }

        return $thread;
    }

    /**
     * Either find and attach this message to an existing thread, or create a new one
     *
     * This should only be called one time if no thread has yet been defined for
     * an email message, once it's been set this funciton will never be called again.
     *
     * @throws \RuntimeException If this email message is already attached to a thread
     */
    private function attachToThread()
    {
        if ($this->getValue("thread")) {
            throw new \RuntimeException("Message is already attached to a thread");
        }

        // First check to see if we can find an existing thread we should attach to
        $thread = $this->discoverThread();

        // If we could not find a thread that already exists, then create a new one
        if (!$thread) {
            $thread = $this->getEntityLoader()->create(ObjectTypes::EMAIL_THREAD, $this->getAccountId());
            $thread->setValue("owner_id", $this->getValue("owner_id"));
            $thread->setValue("num_messages", 0);
        }

        // Change subject of thread to the subject of this (last) message
        $thread->setValue("subject", $this->getValue("subject"));

        // Increment the number of messages in the thread
        $numMessages = (int) $thread->getValue("num_messages");
        $thread->setValue("num_messages", ++$numMessages);

        // Update num_attachments in thread
        if ($this->getValue("num_attachments")) {
            $numAtt = $thread->getValue("num_attachments");
            $thread->setValue("num_attachments", $numAtt + $this->getValue("num_attachments"));
        }

        // Add email message from to thread senders
        $thread->addToSenders($this->getValue("from"));

        // Add email message to to thread receivers
        $thread->addToReceivers($this->getValue("to"));

        // Add message body to thread body - mostly for snippets and searching
        $existingBody = $thread->getValue("body");
        $thread->setValue("body", $this->getValue("body") . "\n\n" . $existingBody);

        // Now update some common fields and save the thread
        $this->updateThreadFromMessage($thread);

        // Set the thread of this message to the discovered (or created) thread
        $this->setValue("thread", $thread->getEntityId());
    }

    /**
     * Update the thread this message is attached to based on this message's field values
     *
     * These are values that should be updated every time the email message is saved.
     *
     * @param EmailThreadEntity $thread Optional reference to opened thread to update
     * @throws \InvalidArgumentException if no thread has been set for this message
     */
    private function updateThreadFromMessage(EmailThreadEntity $thread = null)
    {
        if (!$this->getValue("thread") && !$thread) {
            throw new \InvalidArgumentException("Thread must be passed or set first");
        }

        // If the message is deleted then do not update the thread
        if ($this->isArchived()) {
            return;
        }

        // If a thread was not passed, the load it from value
        if (!$thread) {
            $thread = $this->getEntityLoader()->getEntityById($this->getValue("thread"), $this->getAccountId());
        }

        /*
         * If the seen flag of any single message is updated in the thread,
         * the thread flag should be updated as well.
         */
        $thread->setValue("f_seen", $this->getValue("flag_seen"));

        /*
         * Add this mailbox to the thread if not already set.
         * The 'mailbox_id' field in threads is a groupings (fkey_multi)
         * type and in email messages it's a single fkey field because
         * a message can only be in one mailbox but a thread can be in
         * multiple mailboxes - groupings.
         */
        if ($this->getValue("mailbox_id")) {
            // addMultiValue will not allow duplicates
            $thread->addMultiValue("mailbox_id", $this->getValue("mailbox_id"));
        }

        // Update the delivered date
        if ($this->getValue("message_date")) {
            // Only update if this is newer than the last message added
            if (
                !$thread->getValue("ts_delivered")
                || $thread->getValue("ts_delivered") < $this->getValue("message_date")
            ) {
                // Set  the last delivered date of the thread to this message date
                $thread->setValue("ts_delivered", $this->getValue("message_date"));
            }
        }

        $threadUser = $this->getEntityLoader()->getEntityById($thread->getValue('owner_id'), $this->getAccountId());

        // Save the changes
        if (!$this->getEntityLoader()->save($thread, $threadUser)) {
            throw new RuntimeException("Failed saving thread!");
        }
    }

    /**
     * Create a unique message id for this email message
     */
    private function generateMessageId()
    {
        return '<' . sha1(microtime()) . '@netric.com>';
    }

    /**
     * Get the email portion of the from header
     *
     * @return string
     */
    public function getFromData(): array | null
    {
        $parts = $this->getAddressListData($this->getValue('from'));
        if (count($parts) === 0) {
            return null;
        }

        return $parts[0];
    }

    /**
     * Get the display portion (if set) from the 'from' header
     */
    public function getReplyToData(): array | null
    {
        $parts = $this->getAddressListData($this->getValue('reply_to'));
        if (count($parts) === 0) {
            return null;
        }

        return $parts[0];
    }

    /**
     * Get the recipients in 'to' header as an array
     *
     * @return array [['address'=>'email@example.com', 'display'=>'Full Name']]
     */
    public function getToData(): array
    {
        return $this->getAddressListData($this->getValue('to'));
    }

    /**
     * Get the recipients in 'cc' header as an array
     *
     * @return array [['address'=>'email@example.com', 'display'=>'Full Name']]
     */
    public function getCcData(): array
    {
        return $this->getAddressListData($this->getValue('cc'));
    }

    /**
     * Get the recipients in 'bcc' header as an array
     *
     * @return array [['address'=>'email@example.com', 'display'=>'Full Name']]
     */
    public function getBccData(): array
    {
        return $this->getAddressListData($this->getValue('bcc'));
    }

    /**
     * Add an address with optional display name to the to field
     *
     * @param string $address
     * @param string $display
     * @return void
     */
    public function addTo(string $address, string $display = ''): void
    {
        $existingValue = "";
        if ($this->getValue('to')) {
            $existingValue = $this->getValue('to');
        }

        // Add address to the end of the string
        $this->setValue(
            'to',
            $this->appendEmailAddress($existingValue, $address, $display)
        );
    }

    /**
     * Add an address with optional display name to the cc field
     *
     * @param string $address
     * @param string $display
     * @return void
     */
    public function addCc(string $address, string $display = ''): void
    {
        $existingValue = "";
        if ($this->getValue('cc')) {
            $existingValue = $this->getValue('cc');
        }

        // Add address cc the end of the string
        $this->setValue(
            'cc',
            $this->appendEmailAddress($existingValue, $address, $display)
        );
    }

    /**
     * Add an address with optional display name to the bcc field
     *
     * @param string $address
     * @param string $display
     * @return void
     */
    public function addBcc(string $address, string $display = ''): void
    {
        $existingValue = "";
        if ($this->getValue('bcc')) {
            $existingValue = $this->getValue('bcc');
        }

        // Add address bcc the end of the string
        $this->setValue(
            'bcc',
            $this->appendEmailAddress($existingValue, $address, $display)
        );
    }

    /**
     * Append an address to a comma-separated string of addresses
     *
     * @param [type] $currentValue
     * @param [type] $address
     * @param [type] $display
     * @return string
     */
    private function appendEmailAddress(string $currentValue, string $address, string $display): string
    {
        $recipients = [];

        if ($currentValue) {
            $recipients = $this->getAddressListData($currentValue);
        }

        // Add the compiled address to the list of recipients
        $recipients[] = [
            'address' => $address,
            'display' => (!empty($display)) ? $display : $address
        ];

        $composedParts = [];
        foreach ($recipients as $parsedRecipients) {
            // If the 'display' portion is empty or just the email address, then just add the email address
            if (empty($parsedRecipients['display']) || $parsedRecipients['display'] == $parsedRecipients['address']) {
                $composedParts[] = $parsedRecipients['address'];
            } else {
                $composedParts[] = "\"" . $parsedRecipients['display'] . "\" <" . $parsedRecipients['address'] . ">";
            }
        }

        // Recompile the to header into a comma-separated text field
        return implode(',', $composedParts);
    }

    /**
     * Take a comma-separated address list string and return data array
     *
     * @param string $value
     * @return array [['address'=>'email@example.com', 'display'=>"Display Name"]]
     */
    private function getAddressListData(string $value): array
    {
        $data = mailparse_rfc822_parse_addresses($value);
        return $data;
    }
}
