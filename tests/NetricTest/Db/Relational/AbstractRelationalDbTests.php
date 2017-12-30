<?php
namespace NetricTest\Db\Relational;

use PHPUnit\Framework\TestCase;
use icf\core\exception\RuntimeException;
use icf\core\model\DataMapperRDb;
use icf\core\model\IdentityMapper;
use icf\core\rdb\RDbInterface;
use icf\core\rdb\Statement;
use icf\core\test\model\testasset\ExampleInsideEntity;

/**
 * Test all relational databases
 * 
 * Note: Extend this to test any database adapters
 *
 * @group integration
 */
abstract class AbtractRelatinoalDbTest extends TestCase
{
    /**
     * Must be implemented in all derived classes
     *
     * @return RDbInterface
     */
    abstract protected function getRDbHandle();

    public function testPrepareInsert()
    {
        $oRDb = $this->getRDbHandle();
        // Should return a Result object after a successful insert
        $this->assertNotNull($this->insertIntoTestTable($oRDb));
    }

    /**
     * @group integration
     */
    public function testQuery()
    {
        $oRDb = $this->getRDbHandle();

        // Insert into the test table a user named 'joe'
        $this->insertIntoTestTable($oRDb);

        // Query the table for the user
        $oResult = $oRDb->query(
            "SELECT * FROM test WHERE name = :name",
            [":name" => "joe"]
        );
        $this->assertTrue(count($oResult->fetchAll()) > 0);

        // Make sure a non-existent user returns 0 rows
        $oResult = $oRDb->query(
            "SELECT * FROM test WHERE name = ?",
            ["noexist"]
        );
        $this->assertEquals(0, count($oResult->fetchAll()));

        // Test that the PDO exception for parameter indices starting at 0 can be overridden 
        // by explicitly declaring the index.
        $oResult = $oRDb->query(
            "SELECT * FROM test WHERE name = ?",
            [1 => "joe"]
        );
        $this->assertEquals(1, count($oResult->fetchAll()));
    }

    /**
     * When attempting to bind a value that is not expected, test that
     * Statement throws a RuntimeException
     *
     * @group integration
     */
    public function testInvalidParameterValue()
    {
        $oRDb = $this->getRDbHandle();

        // Insert into the test table a user named 'joe'
        $this->insertIntoTestTable($oRDb);

        // Query the table for the user
        $oResult = $oRDb->query(
            "SELECT * FROM test WHERE name = :name",
            [":name" => "joe"]
        );
        $this->assertTrue(count($oResult->fetchAll()) > 0);

        // Create the incorrect query.
        $oBadValue = new \stdClass();
        $this->expectException(RuntimeException::class);
        $oResult = $oRDb->query(
            "SELECT * FROM test WHERE name = :name",
            [
                'name' => $oBadValue
            ]
        );
    }

    /**
     * @group integration
     */
    public function testLastInsertId()
    {
        $oRDb = $this->getRDbHandle();

        // Add something and then get the last id
        $this->insertIntoTestTable($oRDb);
        $lastInsertId = $oRDb->lastInsertId();
        $this->assertTrue($lastInsertId > 0);

        // Make sure it increments again
        $this->insertIntoTestTable($oRDb);
        $this->assertEquals(++$lastInsertId, $oRDb->lastInsertId()); 

        // And one more increment for fun
        // Make sure it increments again
        $this->insertIntoTestTable($oRDb);
        $this->assertEquals(++$lastInsertId, $oRDb->lastInsertId());
    }

    /**
     * @group integration
     */
    public function testTransactionCommmit()
    {
        $oRDb = $this->getRDbHandle();

        $oRDb->startTransaction();

        // Insert into the test table a user named 'joe'
        $this->insertIntoTestTable($oRDb);

        $oRDb->commit();

        // Query the table for the user
        $oResult = $oRDb->query(
            "SELECT * FROM test WHERE name = :name",
            [":name" => "joe"]
        );
        $this->assertTrue(count($oResult->fetchAll()) > 0);

        // Make sure a non-existent user returns 0 rows
        $oResult = $oRDb->query(
            "SELECT * FROM test WHERE name = ?",
            ["noexist"]
        );
        $this->assertEquals(0, count($oResult->fetchAll()));

        // Test that the PDO exception for parameter indices starting at 0 can be overridden
        // by explicitly declaring the index.
        $oResult = $oRDb->query(
            "SELECT * FROM test WHERE name = ?",
            [1 => "joe"]
        );
        $this->assertEquals(1, count($oResult->fetchAll()));
    }

    /**
     * @group integration
     */
    public function testTransactionRollback()
    {
        $oRDb = $this->getRDbHandle();

        $oRDb->startTransaction();

        // Insert into the test table a user named 'joe'
        $this->insertIntoTestTable($oRDb);

        $oRDb->rollback();

        // Make sure the insert did not get committed
        $oResult = $oRDb->query(
            "SELECT * FROM test WHERE name = :name",
            [":name" => "joe"]
        );
        $this->assertEquals(0, count($oResult->fetchAll()));
    }

    /**
     * @throws \icf\core\exception\DomainEntityException
     * @throws \icf\core\exception\RevisionChangeException
     * @group integration
     */
    public function testOrmTransactionCommit()
    {
        $oRDb = $this->getRDbHandle();
        $oRDbDataMapper = new DataMapperRDb($oRDb);
        $oIdentityMapper = new IdentityMapper(ExampleInsideEntity::class, [$oRDbDataMapper], []);

        $iTestValue = 1234;

        $oRDb->startTransaction();

        $oEntity = $oIdentityMapper->createEntity();
        $oEntity->set('field1', $iTestValue);
        $oIdentityMapper->saveEntity($oEntity);

        $oRDb->commit();

        // Query the table for the user
        $oResult = $oRDb->query(
            "SELECT * FROM exampleinside WHERE first_inside_field = :first_inside_field",
            [":first_inside_field" => $iTestValue]
        );
        $this->assertTrue(count($oResult->fetchAll()) > 0);
    }

    /**
     * @throws \icf\core\exception\DomainEntityException
     * @throws \icf\core\exception\RevisionChangeException
     * @group integration
     */
    public function testOrmTransactionRollback()
    {
        $oRDb = $this->getRDbHandle();
        $oRDbDataMapper = new DataMapperRDb($oRDb);
        $oIdentityMapper = new IdentityMapper(ExampleInsideEntity::class, [$oRDbDataMapper], []);

        $iTestValue = 1234;

        $oRDb->startTransaction();

        $oEntity = $oIdentityMapper->createEntity();
        $oEntity->set('field1', $iTestValue);
        $oIdentityMapper->saveEntity($oEntity);

        $oRDb->rollback();

        // Query the table for the user
        $oResult = $oRDb->query(
            "SELECT * FROM exampleinside WHERE first_inside_field = :first_inside_field",
            [":first_inside_field" => $iTestValue]
        );
        $this->assertEquals(0, count($oResult->fetchAll()));
    }

    /**
     * Creates the table(s) needed for the tests
     * 
     * @param RDbInterface $oRDb
     */
    protected function setupTables(RDbInterface $oRDb)
    {
        $oRDb->query('PRAGMA journal_mode = OFF');
        $oRDb->query('DROP TABLE IF EXISTS test');
        $this->assertNotNull($oRDb->query(
            "CREATE TABLE IF NOT EXISTS test(
                  id INTEGER PRIMARY KEY,
                  name TEXT
              )"
        ));

        $oRDb->query('DROP TABLE IF EXISTS exampleinside');
        $oRDb->query('CREATE TABLE exampleinside(example_inside_id BIGINT PRIMARY KEY, first_inside_field INT)');
    }

    /**
     * Removes the table(s) needed for the tests
     * 
     * @param RDbInterface $oRDb
     */
    protected function dropTables(RDbInterface $oRDb)
    {
        $oRDb->query('DROP TABLE IF EXISTS test');
    }

    /**
     * Add a record to the test table
     *
     * @param RDbInterface $oRDb
     * @return Statement
     */
    private function insertIntoTestTable(RDbInterface $oRDb)
    {
        // Create an insert statement and return it
        return $oRDb->query(
            "INSERT INTO test(name) VALUES('joe')"
        );
    }
}
