<?php

namespace NetricTest\Entity\ObjType;

use Netric\Account\Account;

use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use PHPUnit\Framework\TestCase;
use Netirc\Entity\Validator\EntityValidator;
use Netric\Entity\Validator\EntityValidatorFactory;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Check entity validator
 */
class EntityValidatorTest extends TestCase
{
    /**
     * Tennant account
     *
     * @var Account
     */
    private $account = null;

    /**
     * Validator instance to test
     *
     * @var EntityValidator
     */
    private $validator = null;

    /**
     * Test entities to cleanup on tearDown
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
        $this->validator = $this->account->getServiceManager()->get(EntityValidatorFactory::class);
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    /**
     * Cleanup test entities
     */
    protected function tearDown(): void
    {
        $dataMapper = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
        foreach ($this->testEntities as $entityToDelete) {
            $dataMapper->delete($entityToDelete, $this->account->getAuthenticatedUser());
        }
    }

    /**
     * Make sure if we try to manually set a uname for the same entity it will fail
     *
     * @return void
     */
    public function testIsValidNotunique()
    {
        $serviceManager = $this->account->getServiceManager();
        $entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);

        $uname = 'utest-cust-' . rand(0, 1000);

        // Create first dashboard with uname
        $entity1 = $serviceManager->get(EntityLoaderFactory::class)->create(ObjectTypes::DASHBOARD, $this->account->getAccountId());
        $entity1->setValue('name', $uname); // will automatically set uname
        $entityDataMapper->save($entity1, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity1;
        $isValid = $this->validator->isValid($entity1, $entityDataMapper);
        $this->assertTrue($isValid);

        // Now try to create another dashboard with the same uname
        $entity2 = $serviceManager->get(EntityLoaderFactory::class)->create(ObjectTypes::DASHBOARD, $this->account->getAccountId());
        $entity2->setValue('name', $uname . '-copy');
        $entity2->setValue('uname', $uname); // manually set to same as above

        $isValid = $this->validator->isValid($entity2, $entityDataMapper);
        $this->assertFalse($isValid);
    }
}
