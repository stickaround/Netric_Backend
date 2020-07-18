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
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    /**
     * Test factory
     */
    public function testFactory()
    {
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::EMAIL_ACCOUNT);
        $this->assertInstanceOf(EmailAccountEntity::class, $entity);
    }

    public function testOnBeforeSavePasswordEncrypt()
    {
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::EMAIL_ACCOUNT);
        $entity->setValue("address", "test@test.com");
        $entity->setValue("password", "test");

        // Simulate onBeforeSave
        $serviceManager = $this->account->getServiceManager();
        $entity->onBeforeSave($serviceManager);

        $vaultService = $serviceManager->get(VaultServiceFactory::class);

        $blockCypher = new BlockCipher($vaultService->getSecret("EntityEnc"));
        $encrypted = $blockCypher->encrypt("test");
        $this->assertEquals($encrypted, $entity->getValue("password"));

        // Test is decryped
        $decrypted = $blockCypher->decrypt($entity->getValue("password"));
        $this->assertEquals("test", $decrypted);

        // Make sure we don't encrypt it again
        $entity->resetIsDirty();
        $entity->onBeforeSave($serviceManager);
        $encrypted = $blockCypher->encrypt("test");
        $this->assertEquals($encrypted, $entity->getValue("password"));
    }
}
