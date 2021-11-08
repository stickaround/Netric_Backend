<?php

declare(strict_types=1);

namespace NetricTest\Account\InitData\Sets;

use Netric\Account\InitData\Sets\EmailAccountsInitDataFactory;
use Netric\Account\InitData\Sets\TicketChannelsInitDataFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class TicketChannelsInitDataTest extends TestCase
{
    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testSetInitialData()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();
        $dataSet = $serviceManager->get(TicketChannelsInitDataFactory::class);
        $this->assertTrue($dataSet->setInitialData($account));

        // Test the general-support-channel
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $channel = $entityLoader->getByUniqueName(
            ObjectTypes::TICKET_CHANNEL,
            "general-support-channel",
            $account->getAccountId()
        );
        $this->assertNotNull($channel);
    }

    /**
     * Test that the email account to channel linking worked
     */
    public function testSetInitialDataLinkedWithEmailAccount()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $serviceManager = $account->getServiceManager();

        // First we need the email accounts created
        // because we later link the general support email address and channel
        $emailAccoutnDataSet = $serviceManager->get(EmailAccountsInitDataFactory::class);
        $emailAccoutnDataSet->setInitialData($account);

        // Now create the default channel
        $dataSet = $serviceManager->get(TicketChannelsInitDataFactory::class);
        $this->assertTrue($dataSet->setInitialData($account));

        // Test the general-support-channel is linked to the email account
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $channel = $entityLoader->getByUniqueName(
            ObjectTypes::TICKET_CHANNEL,
            "general-support-channel",
            $account->getAccountId()
        );
        $emailAccount = $entityLoader->getByUniqueName(
            ObjectTypes::EMAIL_ACCOUNT,
            "general-support-dropbox",
            $account->getAccountId()
        );

        // The channel initialization should have set the email account to the support dropbox
        $this->assertEquals(
            $emailAccount->getEntityId(),
            $channel->getValue("email_account_id")
        );

        // It should also have updated the dropbox reference to the channel
        // so that inbound messages are attached to the channel
        $this->assertEquals(
            $channel->getEntityId(),
            $emailAccount->getValue("dropbox_obj_reference")
        );
    }
}
