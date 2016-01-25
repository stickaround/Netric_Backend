<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Mail;

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

        $message->setBody($body);
        $message->setEncoding('UTF-8');
        $message->setSubject($this->getValue("name"));

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
}
