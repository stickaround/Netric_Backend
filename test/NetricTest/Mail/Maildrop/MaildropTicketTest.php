<?php

declare(strict_types=1);

namespace NetricTest\Mail;

use Netric\Account\Account;
use Netric\Account\AccountContainer;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Entity\ObjType\TicketEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\EntityGroupings;
use Netric\EntityGroupings\Group;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\FileSystem\FileSystem;
use Netric\Mail\Maildrop\MaildropTicket;
use Netric\Mail\MailSystem;
use Netric\Mail\MailSystemInterface;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\ClassAlreadyExistsException;
use PHPUnit\Framework\MockObject\ClassIsFinalException;
use PHPUnit\Framework\MockObject\DuplicateMethodException;
use PHPUnit\Framework\MockObject\InvalidMethodNameException;
use PHPUnit\Framework\MockObject\OriginalConstructorInvocationRequiredException;
use PHPUnit\Framework\MockObject\ReflectionException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\MockObject\UnknownTypeException;
use PhpMimeMailParser\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException as RecursionContextInvalidArgumentException;

/**
 * Test the global mailsystem service
 */
class MaildropTicketTest extends TestCase
{
    const TEST_EMAIL = 'autotest@autotest.netric.com';
    const TEST_ACCOUNT_ID  = 'UUID-ACCOUNT';
    const TEST_USER_ID  = 'UUID-USER';
    const TEST_GROUP_SOURCE_EMAIL = 'UUID-EMAIL-SOURCE';

    private EntityLoader $entityLoaderMock;
    private FileSystem $fileSystemMock;
    private IndexInterface $indexMock;
    private GroupingLoader $groupingLoaderMock;
    private MaildropTicket $maildrop;
    private EmailAccountEntity $mockEmailAccount;
    private UserEntity $userMock;

    /**
     * Lots of interesting mocks to setup here
     */
    protected function setUp(): void
    {
        // Setup a mock user
        $this->userMock = $this->createStub(UserEntity::class);
        $this->userMock->method('getEntityId')->willReturn(self::TEST_USER_ID);
        $this->userMock->method('getAccountId')->willReturn(self::TEST_ACCOUNT_ID);

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

        $this->maildrop = new MaildropTicket(
            $this->entityLoaderMock,
            $this->fileSystemMock,
            $this->indexMock,
            $this->groupingLoaderMock
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
        $mockTicket->expects($this->exactly(5))
            ->method('setValue')
            ->withConsecutive(
                [$this->equalTo('description'), $this->equalTo('Again a simple message')],
                [$this->equalTo('name'), $this->equalTo('Test Subject')],
                [$this->equalTo('is_closed'), $this->equalTo(false)],
                [$this->equalTo('f_seen'), $this->equalTo(false)],
                [$this->equalTo('source_id'), $this->equalTo(self::TEST_GROUP_SOURCE_EMAIL)],
            );

        // Make sure we save the entity with the given user
        $this->entityLoaderMock->expects($this->once())
            ->method('save')
            ->with(
                $this->equalTo($mockTicket),
                $this->equalTo($this->userMock)
            );

        $entityId = $this->maildrop->createEntityFromMessage(
            __DIR__ . '/../_files/m1.example.org.unseen',
            $this->mockEmailAccount
        );

        $this->assertEquals($mockTicket->getEntityId(), $entityId);
    }
}
