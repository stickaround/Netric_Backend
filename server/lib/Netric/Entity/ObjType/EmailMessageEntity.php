<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\EntityDefinition;
use Netric\EntityLoader;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Mail;
use Netric\Mime;
use Netric\FileSystem\FileSystem;

/**
 * Email entity extension
 *
 * Example
 * <code>
 * 	$email = $entityLoader->create("email_message");
 * 	$email->setValue("sent_from", "sky.stebnicki@aereus.com");
 * 	$email->setValue("send_to", "someone@somewhere.com");
 * 	$email->setValue("body", "Hello there");
 *  $email->setValue("body_type", "plain");
 * 	$email->addAttachment("/path/to/my/file.txt");
 * 	$email->send();
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
     * Class constructor
     *
     * @param EntityDefinition $def The definition of the email message
     * @param EntityLoader $entityLoader Loader to get/save entities
     * @param IndexInterface $entityIndex Index to query entities
     * @param FileSystem $fileSystem Used for working with netric files
     */
    public function __construct(
        EntityDefinition $def,
        EntityLoader $entityLoader,
        IndexInterface $entityIndex,
        FileSystem $fileSystem)
    {
        $this->entityLoader = $entityLoader;
        $this->entityIndex = $entityIndex;
        $this->fileSystem = $fileSystem;
        parent::__construct($def);
    }

    /**
     * Callback function used for derived subclasses
     *
     * @param ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onBeforeSave(ServiceLocatorInterface $sm)
    {
        /*
        // Check message_id
		$this->getMessageId(); // will set message_id property

		// Get number of attachments and save
		$numatt = $this->getValue("num_attachments");
		if (!is_numeric($numatt))
			$numatt = 0;

		if (is_array($this->attachments))
		{
			// Do not count inline attachments
			foreach ($this->attachments as $att)
			{
				if (!$att->contentId)
					$numatt++;
			}
			//$numatt += count($this->attachments);
		}

		$this->setValue("num_attachments", $numatt);

		// Update thread
		// -------------------------------------------------
		$thread = null;
		if (!$this->getValue("thread"))
		{
			$thread = $this->createNewThread();

			// Update fields in thread if this is a new one, otherwise data was previously set
			$mcnt = $thread->getValue("num_messages");
			if (!is_numeric($mcnt)) $mcnt = 0;
			$thread->setValue("num_messages", ++$mcnt);

			if ($this->getValue("num_attachments"))
			{
				$acnt = $thread->getValue("num_attachments");
				if (!is_numeric($acnt)) $acnt = 0;
				$thread->setValue("num_attachments", $acnt + $this->getValue("num_attachments"));
			}
			else
			{
				$thread->setValue("num_attachments", 0);
			}

			$thread->setValue("subject", $this->getValue("subject"));
			$existingBody = $thread->getValue("body");
			$thread->setValue("body", $this->getValue("body") . "\n\n" . $existingBody); // mostly for searching & snippets

            $senders = $thread->updateSenders($this->getValue("sent_from"), $thread->getValue("senders"));
			$thread->setValue("senders", $senders);

            $receivers = $thread->updateSenders($this->getValue("send_to"), $thread->getValue("receivers"));
			$thread->setValue("receivers", $receivers);

			$thread->setValue("ts_delivered", $this->getValue("message_date"));
		}
		else // already part of a thread so no need to make too many modifications to the thread
		{
			$thread = CAntObject::factory($this->dbh, "email_thread", $this->getValue("thread"), $this->user);
		}

		if ($this->getValue("mailbox_id") && !$thread->getMValueExists("mailbox_id", $this->getValue("mailbox_id")))
		{
			$thread->setMValue("mailbox_id", $this->getValue("mailbox_id")); // Add to same mailbox as the message
		}

		$thread->setValue("f_seen", $this->getValue("flag_seen"));

		$tid = $thread->save();
		if (!$this->getValue("thread") && $tid)
			$this->setValue("thread", $tid);
         */
    }

    /**
     * Callback function used for derived subclasses
     *
     * @param ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onAfterSave(ServiceLocatorInterface $sm)
    {
        if ($this->isDeleted())
        {
            /*
            // Remove all other messages and the tread
            if ($this->getValue("thread") && !$this->skipProcessThread)
            {
                $thread = CAntObject::factory($this->dbh, "email_thread", $this->getValue("thread"), $this->user);
                if ($hard)
                    $thread->removeHard();
                else
                    $thread->remove();
            }
            */
        }
    }

    /**
     * Called right before the entity is purged (hard delete)
     *
     * @param ServiceLocatorInterface $sm Service manager used to load supporting services
     */
    public function onBeforeDeleteHard(ServiceLocatorInterface $sm)
    {
        /*
        // Remove original (raw) message
        if ($hard)
        {
            $this->dbh->Query("SELECT lo_unlink(lo_message) FROM email_message_original WHERE message_id='".$this->id."'");
            $this->dbh->Query("DELETE FROM email_message_original WHERE message_id='".$this->id."'");
        }
        */
    }

    /**
     * Export the contents of this entity to a mime message for sending
     *
     * @return Mail\Message
     */
    public function toMailMessage()
    {
        $message = new Mail\Message();
        $message->setEncoding('UTF-8');
        $message->setSubject($this->getValue("name"));

        // Set from
        $from = $this->getAddressListFromString($this->getValue("sent_from"));
        if ($from) {
            $message->addFrom($from);
        }

        // Set to
        $to = $this->getAddressListFromString($this->getValue("send_to"));
        if ($to) {
            $message->addTo($to);
        }

        // Set cc
        $cc = $this->getAddressListFromString($this->getValue("cc"));
        if ($cc) {
            $message->addCc($cc);
        }

        $bcc = $this->getAddressListFromString($this->getValue("bcc"));
        if ($bcc) {
            $message->addBcc($bcc);
        }

        /*
         * Setup the body and attachments - mime message
         */

        // HTML part
        $htmlPart = new Mime\Part($this->getHtmlBody());
        $htmlPart->setEncoding(Mime\Mime::ENCODING_QUOTEDPRINTABLE);
        $htmlPart->setType(Mime\Mime::TYPE_HTML);
        $htmlPart->setCharset("UTF-8");

        // Plain text part
        $textPart = new Mime\Part($this->getPlainBody());
        $textPart->setEncoding(Mime\Mime::ENCODING_QUOTEDPRINTABLE);
        $textPart->setType(Mime\Mime::TYPE_TEXT);
        $textPart->setCharset("UTF-8");

        // Create a multipart/alternative message for the text and html parts
        $bodyMessage = new Mime\Message();
        $bodyMessage->addPart($textPart);
        $bodyMessage->addPart($htmlPart);

        // Create mime message to wrap both the body and any attachments
        $mimeMessage = new Mime\Message();

        // Add text & html alternatives to the mime message wrapper
        $bodyPart = new Mime\Part($bodyMessage->generateMessage());
        $bodyPart->setType(Mime\Mime::MULTIPART_ALTERNATIVE);
        $bodyPart->setBoundary($bodyMessage->getMime()->boundary());
        $mimeMessage->addPart($bodyPart);

        // Add attachments to the mime message
        $attachments = $this->getValue("attachments");
        foreach ($attachments as $fileId)
        {
            $file = $this->fileSystem->openFileById($fileId);

            // Get a stream to reduce memory footprint
            $fileStream = $this->fileSystem->openFileStreamById($fileId);
            $attachment = new Mime\Part($fileStream);

            // Set meta-data
            $attachment->setType($file->getMimeType());
            $attachment->setFileName($file->getName());
            $attachment->setDisposition(Mime\Mime::DISPOSITION_ATTACHMENT);

            // Setting the encoding is recommended for binary data
            $attachment->setEncoding(Mime\Mime::ENCODING_BASE64);
            $mimeMessage->addPart($attachment);
        }

        /*
         * Finished mime message
         */

        // Add the message to the mail/Message and return
        $message->setBody($mimeMessage);

        return $message;
    }

    /**
     * Import entity from a mesage
     *
     * This is often used for importing new messages from a backend
     *
     * @param Mail\Message $message
     */
    public function fromMailMessage(Mail\Message $message)
    {
        // TODO: Import - including temp messages
    }

    /**
     * Get an address list from a comma separated list of addresses
     *
     * @return Mail\AddressList
     */
    private function getAddressListFromString($addresses)
    {
        if (!$addresses)
            return null;

        $addressList = new Mail\AddressList();

        $addressParts = preg_split("/[;,]+/", $addresses);
        foreach ($addressParts as $part) {
            if ($part) {
                $addressList->addFromString($part);
            }
        }

        return ($addressList->count()) ? $addressList : null;
    }

    /**
     * Get the HTML version of email body
     *
     * This function will convert plain to HTML if the body
     * is plain text.
     *
     * @return string
     */
    private function getHtmlBody()
    {
        $body = $this->getValue("body");

        if ($this->getValue("body_type") != "plain") {
            return $body;
        }

        // Replace \n new lines with <br />
        $body = nl2br(htmlspecialchars($body));

        // Replace tab with three spaces
        $body = str_replace("\t", "&nbsp;&nbsp;&nbsp;", $body);

        // Replace space with &bnsp; to make sure things look okay
        $body = str_replace(" ", "&nbsp;", $body);

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
    private function getPlainBody()
    {
        if ($this->getValue("body_type") == "plain") {
            return $this->getValue("body");
        }

        // Convert an HTML message to plain
        return strip_tags(str_replace("<br>", "\n", $this->getValue("body")));
    }
}
