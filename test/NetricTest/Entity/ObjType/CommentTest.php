<?php

namespace NetricTest\Entity\ObjType;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\CommentEntity;
use Netric\EntityDefinition\ObjectTypes;
use Ramsey\Uuid\Uuid;

/**
 * Test entity activity class
 */
class CommentTest extends TestCase
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
        $def = $this->account->getServiceManager()->get(EntityDefinitionLoader::class)->get(ObjectTypes::COMMENT, $this->account->getAccountId());
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::COMMENT, $this->account->getAccountId());
        $this->assertInstanceOf(CommentEntity::class, $entity);
    }

    /**
     * When we add a comment to an entity, the referenced entity has a num_comments field that is updated
     */
    public function testHasCommentsOnReferencedEntity()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $customer = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $comment = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::COMMENT, $this->account->getAccountId());

        // Save customer so we have it to work with
        $customer->setValue("name", "test num_comments");
        $cid = $entityLoader->save($customer, $this->account->getSystemUser());

        // Now save the comment which should increment the num_comments of $customer
        $comment->setValue("obj_reference", $customer->getEntityId(), $customer->getName());
        $comment->setValue(ObjectTypes::COMMENT, "Test Comment");
        $entityLoader->save($comment, $this->account->getSystemUser());

        // Now re-open the referenced customer just to make sure it was saved right
        $openedCustomer = $entityLoader->getEntityById($cid, $this->account->getAccountId());
        $this->assertEquals(1, $openedCustomer->getValue("num_comments"));

        // Delete the comment and make sure num_comments is decremented
        $entityLoader->archive($comment, $this->account->getAuthenticatedUser());
        $reopenedCustomer = $entityLoader->getEntityById($cid, $this->account->getAccountId());
        $this->assertEquals(0, $reopenedCustomer->getValue("num_comments"));

        // Cleanup
        $entityLoader->delete($comment, $this->account->getAuthenticatedUser());
        $entityLoader->delete($openedCustomer, $this->account->getAuthenticatedUser());
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
