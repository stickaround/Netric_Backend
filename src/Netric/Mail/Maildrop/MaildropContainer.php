<?php

declare(strict_types=1);

namespace Netric\Mail\Maildrop;

use Netric\Entity\ObjType\EmailAccountEntity;

/**
 * Create a container for getting maildrop drivers
 */
class MaildropContainer
{
    /**
     * Array of maildrops that can be used for different types
     *
     * @var MaildropInterface[]
     */
    private array $maildrops;


    /**
     * Construct container with available maildrops
     *
     * @param array $maildrops Array of supported maildrops
     * @return void
     */
    public function __construct(array $maildrops)
    {
        $this->maildrops = $maildrops;
    }

    /**
     * Get a maildrop to support an email account type
     *
     * This can be assumed to always return a maildrop, since it will fall back to the
     * default 'email' maildrop if nothing else can be found.
     *
     * @param EmailAccountEntity $emailAccount
     * @return MaildropInterface
     */
    public function getMaildropForEmailAccount(EmailAccountEntity $emailAccount): MaildropInterface
    {
        $default = null;

        // Loop through all the maildrops looking for a match
        foreach ($this->maildrops as $maildrop) {
            // Get the defail email delivery maildrop
            if ($maildrop->getEntityType() === MaildropInterface::TYPE_EMAIL) {
                $default = $maildrop;
            }

            // Check for a and return any special dropboxes that match the type
            if (
                $emailAccount->getValue('type') === EmailAccountEntity::TYPE_DROPBOX &&
                $emailAccount->getValue('dropbox_create_type') === $maildrop->getEntityType()
            ) {
                return $maildrop;
            }
        }

        return $default;
    }
}
