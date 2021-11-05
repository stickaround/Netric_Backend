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
            $channel->fromArray($channelData, true);
            $defaultDomain = $this->mailSystem->getDefaultDomain($account->getAccountId());
            $this->entityLoader->save(
                $channel,
                $account->getSystemUser()
            );
        }

        return true;
    }
}
