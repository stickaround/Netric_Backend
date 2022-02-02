<?php

/**
 * Test EmailAccount entity
 */

namespace NetricTest\Entity\ObjType;

use Netric\Crypt\VaultServiceFactory;
use Netric\Entity;
use Netric\Crypt\BlockCipher;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\EntityDefinition\ObjectTypes;

class EmailAccountEntityTest extends TestCase
{
    /**
     * Tenant account
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
     * List of test entities to cleanup
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];


    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
        $this->entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
    }

    /**
     * Cleanup after each test
     */
    protected function tearDown(): void
    {
        // Make sure any test entities created are deleted
        foreach ($this->testEntities as $entity) {
            // Second param is a 'hard' delete which actually purges the data
            $this->entityLoader->delete($entity, $this->account->getAuthenticatedUser());
        }
    }

    /**
     * Test factory
     */
    public function testFactory()
    {
        $entity = $this->entityLoader->create(ObjectTypes::EMAIL_ACCOUNT, $this->account->getAccountId());
        $this->assertInstanceOf(EmailAccountEntity::class, $entity);
    }

    public function testOnBeforeSavePasswordEncrypt()
    {
        $entity = $this->entityLoader->create(ObjectTypes::EMAIL_ACCOUNT, $this->account->getAccountId());
        $entity->setValue("address", "test@test.com");
        $entity->setValue("password", "test");

        // Simulate onBeforeSave
        $serviceManager = $this->account->getServiceManager();
        $entity->onBeforeSave($serviceManager, $this->user);

        $vaultService = $serviceManager->get(VaultServiceFactory::class);

        $blockCypher = new BlockCipher($vaultService->getSecret("EntityEnc"));
        $encrypted = $blockCypher->encrypt("test");
        $this->assertEquals($encrypted, $entity->getValue("password"));

        // Test is decryped
        $decrypted = $blockCypher->decrypt($entity->getValue("password"));
        $this->assertEquals("test", $decrypted);

        // Make sure we don't encrypt it again
        $entity->resetIsDirty();
        $entity->onBeforeSave($serviceManager, $this->user);
        $encrypted = $blockCypher->encrypt("test");
        $this->assertEquals($encrypted, $entity->getValue("password"));
    }

    public function testOnBeforeSaveUniqueAddressEmailAccount()
    {
        // Create an email account so we have an entity to check later
        $entity = $this->entityLoader->create(ObjectTypes::EMAIL_ACCOUNT, $this->account->getAccountId());
        $entity->setValue("name", "support");
        $entity->setValue("address", "support@test.com");
        $entity->setValue("type", EmailAccountEntity::TYPE_IMAP);
        $this->entityLoader->save($entity, $this->account->getSystemUser());        
        $this->testEntities[] = $entity;
        
        $duplicateEntity = $this->entityLoader->create(ObjectTypes::EMAIL_ACCOUNT, $this->account->getAccountId());
        $duplicateEntity->setValue("name", "support");
        $duplicateEntity->setValue("address", "support@test.com");
        $duplicateEntity->setValue("type", EmailAccountEntity::TYPE_DROPBOX);
                
        $this->expectException(\RuntimeException::class);

        // Simulate onBeforeSave
        $serviceManager = $this->account->getServiceManager();
        $duplicateEntity->onBeforeSave($serviceManager, $this->user);
    }
}
