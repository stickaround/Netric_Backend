<?php

declare(strict_types=1);

namespace NetricTest\Mail;

use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Entity\ObjType\TicketEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\EntityGroupings;
use Netric\EntityGroupings\Group;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery\Results;
use Netric\FileSystem\FileSystem;
use Netric\Mail\Maildrop\MaildropInterface;
use Netric\Mail\Maildrop\MaildropTicket;
use PHPUnit\Framework\TestCase;

/**
 * Test the global mailsystem service
 */
class MaildropTicketTest extends TestCase
{
    const TEST_EMAIL = 'autotest@autotest.netric.com';
    const TEST_ACCOUNT_ID  = 'UUID-ACCOUNT';
    const TEST_USER_ID  = 'UUID-USER';
    const TEST_GROUP_SOURCE_EMAIL = 'UUID-EMAIL-SOURCE';
    const TEST_CONTACT_ID = 'UUID-CONTACT';

    private EntityLoader $entityLoaderMock;
    private FileSystem $fileSystemMock;
    private IndexInterface $indexMock;
    private GroupingLoader $groupingLoaderMock;
    private MaildropTicket $maildrop;
    private EmailAccountEntity $mockEmailAccount;
    private UserEntity $userMock;
    private MaildropInterface $maildropCommentMock;

    /**
     * Lots of interesting mocks to setup here
     */
    protected function setUp(): void
    {
        // Setup a mock user
        $this->userMock = $this->createStub(UserEntity::class);
        $this->userMock->method('getEntityId')->willReturn(self::TEST_USER_ID);
        $this->userMock->method('getAccountId')->willReturn(self::TEST_ACCOUNT_ID);
        $this->userMock->method('getValue')->with('contact_id')->willReturn(self::TEST_CONTACT_ID);

        // Setup a test email account
        $this->mockEmailAccount = $this->createStub(EmailAccountEntity::class);
        $this->mockEmailAccount->method('getOwnerId')->willReturn(self::TEST_USER_ID);
        $this->mockEmailAccount->method('getAccountId')->willReturn(self::TEST_ACCOUNT_ID);

        // Setup entity loader
        $this->entityLoaderMock = $this->createMock(EntityLoader::class);
        $this->entityLoaderMock->method('getEntityById')
            ->with(self::TEST_USER_ID, self::TEST_ACCOUNT_ID)
            ->will($this->returnValue($this->userMock));

        $this->fileSystemMock = $this->createMock(FileSystem::class);
        $this->indexMock = $this->createMock(IndexInterface::class);

        $this->groupingLoaderMock = $this->createMock(GroupingLoader::class);
        $groups = new EntityGroupings(ObjectTypes::TICKET . "/source_id", self::TEST_ACCOUNT_ID);
        $emailSource = new Group();
        $emailSource->setName(TicketEntity::SOURCE_EMAIL);
        $emailSource->setGroupId(self::TEST_GROUP_SOURCE_EMAIL);
        $groups->add($emailSource);
        $this->groupingLoaderMock->method('get')
            ->with(ObjectTypes::TICKET . "/source_id", self::TEST_ACCOUNT_ID)
            ->will($this->returnValue($groups));

        $this->maildropCommentMock = $this->createMock(MaildropInterface::class);

        $this->maildrop = new MaildropTicket(
            $this->entityLoaderMock,
            $this->fileSystemMock,
            $this->indexMock,
            $this->groupingLoaderMock,
            $this->maildropCommentMock
        );
    }

    /**
     * Make sure we can create an entity from a plain text email
     */
    public function testCreateEntityFromMessage(): void
    {
        // Create an observable ticket entity and return it from the EntityLoader:create mock
        $mockTicket = $this->createMock(TicketEntity::class);
        $mockTicket->method('getEntityId')->willReturn("UUID-TICKET");
        $this->entityLoaderMock->method('create')->with(
            ObjectTypes::TICKET,
            self::TEST_ACCOUNT_ID
        )->will(
            $this->returnValue($mockTicket)
        );

        // Make sure values are set from the email
        $mockTicket->expects($this->exactly(6))
            ->method('setValue')
            ->withConsecutive(
                [$this->equalTo('description'), $this->equalTo('Again a simple message')],
                [$this->equalTo('name'), $this->equalTo('Test Subject')],
                [$this->equalTo('is_closed'), $this->equalTo(false)],
                [$this->equalTo('f_seen'), $this->equalTo(false)],
                [$this->equalTo('source_id'), $this->equalTo(self::TEST_GROUP_SOURCE_EMAIL)],
                // This means it queried and found a user with matching email
                [$this->equalTo('contact_id'), $this->equalTo(self::TEST_CONTACT_ID)],
            );

        // Make sure we save the entity with the given user
        $this->entityLoaderMock->expects($this->once())
            ->method('save')
            ->with(
                $this->equalTo($mockTicket),
                $this->equalTo($this->userMock)
            );

        // Simulate returning an existing that the ticket will be created for
        $queryResults = new Results(new EntityQuery(ObjectTypes::USER, self::TEST_ACCOUNT_ID, self::TEST_USER_ID));
        $queryResults->addEntity($this->userMock);
        $this->indexMock->method('executeQuery')->willReturn($queryResults);

        $entityId = $this->maildrop->createEntityFromMessage(
            __DIR__ . '/../_files/m1.example.org.unseen',
            $this->mockEmailAccount
        );

        $this->assertEquals($mockTicket->getEntityId(), $entityId);
    }

    /**
     * Make sure we can create an entity from a plain text email
     */
    public function testCreateNewPublicUser(): void
    {
        // Create a ticket entity and return it from the EntityLoader:create mock
        $mockTicket = $this->createMock(TicketEntity::class);
        $mockTicket->method('getEntityId')->willReturn("UUID-TICKET");

        // Create an observable user entity so we can see if creating a new user works as expected
        $observeNewUser = $this->createMock(UserEntity::class);
        $observeNewUser->method('getEntityId')->willReturn("UUID-NEW-USER");
        $observeNewUser->method('getValue')->with('contact_id')->willReturn(self::TEST_CONTACT_ID);

        // Create gets called twice, once for the ticket, and again for the user
        $this->entityLoaderMock->method('create')->withConsecutive(
            [ObjectTypes::TICKET, self::TEST_ACCOUNT_ID],
            [ObjectTypes::USER, self::TEST_ACCOUNT_ID]
        )->willReturnOnConsecutiveCalls(
            $mockTicket,
            $observeNewUser
        );

        // Make sure new user values are what is expected
        $observeNewUser->expects($this->exactly(4))
            ->method('setValue')
            ->withConsecutive(
                [$this->equalTo('type'), $this->equalTo(UserEntity::TYPE_PUBLIC)],
                // Name should be generated from external@netric.com to external.netric.com (replace @)
                [$this->equalTo('name'), $this->equalTo('external.netric.com')],
                // Full name should be extracted from the From header
                [$this->equalTo('full_name'), $this->equalTo('Some User')],
                [$this->equalTo('email'), $this->equalTo('external@netric.com')],
            );

        // Make sure we save the entity with the given user
        $this->entityLoaderMock->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive(
                [$this->equalTo($observeNewUser), $this->equalTo($this->userMock)],
                [$this->equalTo($mockTicket), $this->equalTo($this->userMock)],
            );

        // Simulate not finding an existing user which should cause it to create one
        $queryResults = new Results(new EntityQuery(ObjectTypes::USER, self::TEST_ACCOUNT_ID, self::TEST_USER_ID));
        $this->indexMock->method('executeQuery')->willReturn($queryResults);

        $this->maildrop->createEntityFromMessage(
            __DIR__ . '/../_files/mail.support.simple',
            $this->mockEmailAccount
        );
    }

    /**
     * Test that we can route commens to the commment maildrop
     *
     * @return void
     */
    public function testReplyComment(): void
    {
        // Make sure the test email account returns the test email address
        $this->mockEmailAccount->method('getValue')->with('address')->willReturn(self::TEST_EMAIL);

        // Create an observable email account so we can make sure values are set correctly
        $mockEmailAccount = $this->createMock(EmailAccountEntity::class);
        $this->entityLoaderMock->method('create')->with(
            ObjectTypes::EMAIL_ACCOUNT,
            self::TEST_ACCOUNT_ID
        )->will(
            $this->returnValue($mockEmailAccount)
        );

        // Make sure email account values are correct
        $mockEmailAccount->expects($this->exactly(4))
            ->method('setValue')
            ->withConsecutive(
                [$this->equalTo('type'), $this->equalTo(EmailAccountEntity::TYPE_DROPBOX)],
                [$this->equalTo('address'), $this->equalTo(self::TEST_EMAIL)],
                [$this->equalTo('dropbox_create_type'), $this->equalTo(MaildropInterface::TYPE_COMMENT)],
                // Match the UUID from the email header
                [$this->equalTo('dropbox_obj_reference'), $this->equalTo('UUID-TEST-TICKET')],
            );

        // Make sure we route the message to the comment maildrop
        $this->maildropCommentMock->expects($this->once())
            ->method('createEntityFromMessage');

        $entityId = $this->maildrop->createEntityFromMessage(
            __DIR__ . '/../_files/mail.support.inreply',
            $this->mockEmailAccount
        );
    }
}
