<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Account;
use Netric\Account\InitData\InitDataInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Mail\MailSystemInterface;

/**
 * Initializer to make sure accounts have a default set of groupings
 */
class TicketChannelsInitData implements InitDataInterface
{
    /**
     * List of ticket channels to create
     */
    private array $channelsData = [];

    /**
     * Entity loader
     */
    private Entityloader $entityLoader;

    /**
     * Constructor
     */
    public function __construct(
        array $channelsData,
        EntityLoader $entityLoader,
        MailSystemInterface $mailSytem
    ) {
        $this->channelsData = $channelsData;
        $this->entityLoader = $entityLoader;
        $this->mailSystem = $mailSytem;
    }

    /**
     * Insert or update initial data for account
     *
     * @param Account $account
     * @return bool
     */
    public function setInitialData(Account $account): bool
    {
        foreach ($this->channelsData as $channelData) {
            // Get the existing channel by uname
            $channel = $this->entityLoader->getByUniqueName(
                ObjectTypes::TICKET_CHANNEL,
                $channelData['uname'],
                $account->getAccountId()
            );

            // If it does not already exist, then create it
            if (!$channel) {
                $channel = $this->entityLoader->create(
                    ObjectTypes::TICKET_CHANNEL,
                    $account->getAccountId()
                );
            }

            // Set fields from data array and save
            // second param will only update provided fields so we don't
            // overwrite entity_id and such
            $channel->setValue('uname', $channelData['uname']);
            $channel->setValue('name', $channelData['name']);

            // This is where we link an existing email account if set
            $emailAccount = null;
            if (
                $channelData['lookup_email_account_uname'] &&
                empty($channel->getValue('email_account_id'))
            ) {
                $emailAccount = $this->entityLoader->getByUniqueName(
                    ObjectTypes::EMAIL_ACCOUNT,
                    $channelData['lookup_email_account_uname'],
                    $account->getAccountId()
                );
            }

            // Link the email account to this channel if loaded
            if ($emailAccount) {
                $channel->setValue('email_account_id', $emailAccount->getEntityId());
            }

            // Save the channel
            $this->entityLoader->save(
                $channel,
                $account->getSystemUser()
            );

            // Now set the object reference for this email account to be the channel
            // so that when new tickets are created from email sent to this dropbox,
            // it will automatically add the ticket to this channel
            if ($emailAccount && empty($emailAccount->getValue('dropbox_obj_reference'))) {
                $emailAccount->setValue('dropbox_obj_reference', $channel->getEntityId());
                $this->entityLoader->save(
                    $emailAccount,
                    $account->getSystemUser()
                );
            }
        }

        return true;
    }
}
