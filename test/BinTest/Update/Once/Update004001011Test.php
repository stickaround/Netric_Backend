<?php
namespace BinTest\Update\Once;

use Netric\Console\BinScript;
use Netric\Entity\EntityInterface;
use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Make sure the bin/scripts/update/once/004/001/011.php script works
 *
 * @group integration
 */
class Update004001011Test extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Path to the script to test
     *
     * @var string
     */
    private $scriptPath = null;

    /**
     * Entities to clean up
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Setup each test
     */
    protected function setUp(): void
{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/011.php";
    }

    /**
     * Cleanup any test entities
     */
    protected function tearDown(): void
{
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $entityLoader->delete($entity, true);
        }
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right
     */
    public function testExists()
    {
        $this->assertTrue(file_exists($this->scriptPath), $this->scriptPath . " not found!");
    }

    /**
     * Make sure the script can work if we have duplciate accounts
     */
    public function testRun()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create two duplicate email accounts
        $account1 = $entityLoader->create(ObjectTypes::EMAIL_ACCOUNT);
        $account1->setValue("address", "bintest@bintest.com");
        $account1->setValue("owner_id", "1");
        $entityLoader->save($account1);
        $this->testEntities[] = $account1;

        $account2 = $entityLoader->create(ObjectTypes::EMAIL_ACCOUNT);
        $account2->setValue("address", "bintest@bintest.com");
        $account2->setValue("owner_id", "1");
        $entityLoader->save($account2);
        $this->testEntities[] = $account2;

        // Add a duplicate email to each account
        $emailMessage1 = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE);
        $emailMessage1->setValue(ObjectTypes::EMAIL_ACCOUNT, $account1->getId());
        $emailMessage1->setValue("subject", "test");
        $entityLoader->save($emailMessage1);
        $this->testEntities[] = $emailMessage1;

        $emailMessage2 = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE);
        $emailMessage2->setValue(ObjectTypes::EMAIL_ACCOUNT, $account2->getId());
        $emailMessage2->setValue("subject", "test");
        $entityLoader->save($emailMessage2);
        $this->testEntities[] = $emailMessage2;

        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // The update script should have deleted the second email account and email message
        $loadedAccount1 = $entityLoader->get(ObjectTypes::EMAIL_ACCOUNT, $account1->getId());
        $loadedAccount2 = $entityLoader->get(ObjectTypes::EMAIL_ACCOUNT, $account2->getId());
        $this->assertFalse($loadedAccount1->isDeleted());
        $this->assertTrue($loadedAccount2->isDeleted());

        // Check that the message in a account 1 still exists, but the message in account 2 was deleted
        $loadedMessage1 = $entityLoader->get(ObjectTypes::EMAIL_MESSAGE, $emailMessage1->getId());
        $loadedMessage2 = $entityLoader->get(ObjectTypes::EMAIL_MESSAGE, $emailMessage2->getId());
        $this->assertFalse($loadedMessage1->isDeleted());
        $this->assertNull($loadedMessage2);
    }
}
