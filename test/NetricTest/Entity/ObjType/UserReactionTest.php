<?php

namespace NetricTest\Entity\ObjType;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\UserReactionEntity;
use Netric\EntityDefinition\ObjectTypes;
use Ramsey\Uuid\Uuid;

/**
 * Test entity User Reaction class
 */
class UserReactionTest extends TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Administrative user
     *
     * @var \Netric\User
     */
    private $user = null;


    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $def = $this->account->getServiceManager()->get(EntityDefinitionLoader::class)->get(ObjectTypes::USER_REACTION, $this->account->getAccountId());
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::USER_REACTION, $this->account->getAccountId());
        $this->assertInstanceOf(UserReactionEntity::class, $entity);
    }

    /**
     * When we add a reaction to an entity, the referenced entity has a num_reactions field that is updated
     */
    public function testHasReactionsOnReferencedEntity()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $message = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CHAT_MESSAGE, $this->account->getAccountId());
        $reaction = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::USER_REACTION, $this->account->getAccountId());

        // Save message so we have it to work with
        $message->setValue("body", "test num_reactions");
        $messageId = $entityLoader->save($message, $this->account->getSystemUser());

        // Now save the reaction which should increment the num_reactions of $message
        $reaction->setValue("obj_reference", $messageId, $message->getName());
        $reaction->setValue("reaction", "ThumbUpIcon");
        $entityLoader->save($reaction, $this->account->getSystemUser());

        // Now re-open the referenced message just to make sure it was saved right
        $openedMessage = $entityLoader->getEntityById($messageId, $this->account->getAccountId());
        $numReactions = $openedMessage->getValue("num_reactions");
        $this->assertEquals(1, $numReactions);

        // Delete the reaction and make sure num_reactions is decremented
        $entityLoader->archive($reaction, $this->account->getAuthenticatedUser());
        $reopenedMessage = $entityLoader->getEntityById($messageId, $this->account->getAccountId());
        $this->assertNotEquals($numReactions, $reopenedMessage->getValue("num_comments"));

        // Cleanup
        $entityLoader->delete($reaction, $this->account->getAuthenticatedUser());
        $entityLoader->delete($reopenedMessage, $this->account->getAuthenticatedUser());
    }

    /**
     * Entity followers are synchronized with the comment followers
     *
     * This makes sure that all interested parties are notified when we add
     * a new comment to an entity.
     */
    public function testSyncFollowers()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $customer = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $comment = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::COMMENT, $this->account->getAccountId());

        $userGuid = $uuid4 = Uuid::uuid4()->toString();
        // Save customer with a fake user callout for testing
        $customer->setValue("name", "test sync followers");
        $customer->setValue("notes", "Hey [user:$userGuid:Dave], check this out please.");
        $cid = $entityLoader->save($customer, $this->account->getSystemUser());

        // Now create a comment on the customer which should sync the followers
        $comment->setValue("obj_reference", $customer->getEntityId(), $customer->getName());
        $comment->setValue(ObjectTypes::COMMENT, "Test Comment");
        $entityLoader->save($comment, $this->account->getSystemUser());

        // Check to make sure the comment has user 456 as a follower copied from customer
        $followers = $comment->getValue("followers");
        $this->assertTrue(in_array($userGuid, $followers));

        // Cleanup
        $entityLoader->delete($comment, $this->account->getAuthenticatedUser());
        $entityLoader->delete($customer, $this->account->getAuthenticatedUser());
    }
}
