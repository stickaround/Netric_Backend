<?php

/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 *
 * Most tests are inherited from DmTestsAbstract.php.
 * Only define pgsql specific tests here and try to avoid name collision with the tests
 * in the parent class. For the most part, the parent class tests all public functions
 * so private functions should be tested below.
 */

namespace NetricTest\EntitySync\Commit\DataMapper;

use PHPUnit\Framework\TestCase;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntitySync\Commit\DataMapper\DataMapperRdb;
use NetricTest\Bootstrap;

/**
 * @group integration
 */
class DataMapperRdbTest extends DmTestsAbstract
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    protected $database = null;

    /**
     * Use this funciton in all the datamappers to construct the datamapper
     *
     * @return \Netric\Entity\Commit\DataMaper\DataMapperInterface
     */
    protected function getDataMapper()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->database = $sm->get(RelationalDbFactory::class);

        return new DataMapperRdb($account, $this->database);
    }

    public function testCreateNewSequenceIfMissing()
    {
        $dm = $this->getDataMapper();

        $reflector = new \ReflectionClass(get_class($dm));
        $property = $reflector->getProperty("sSequenceName");
        $property->setAccessible(true);
        $property->setValue($dm, "object_recurrence_id_seq");

        $nextCid = $dm->getNextCommitId('object_recurrence');
        $this->assertTrue($nextCid > 0); // make sure the sequence gets created
    }
}
