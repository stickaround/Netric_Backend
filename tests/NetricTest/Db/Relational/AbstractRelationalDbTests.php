<?php
namespace NetricTest\Db\Relational;

use PHPUnit\Framework\TestCase;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Db\Relational\Exception\DatabaseException;

/**
 * Test all relational databases
 * 
 * Note: Extend this to test any database adapters
 *
 * @group integration
 */
abstract class AbstractRelationalDbTests extends TestCase
{
    /**
     * Namespaces to cleanup
     *
     * @return []
     */
    private $namespacesToCleanup = [];

    /**
     * Must be implemented in all driver classes
     *
     * @return RelationalDbInterface
     */
    abstract protected function getDatabase();

    public function setUp()
    {
        $database = $this->getDatabase();
        $database->query('DROP TABLE IF EXISTS utest_people');
        $database->query(
            'CREATE TABLE utest_people(
                id SERIAL PRIMARY KEY,
                name TEXT
            )'
        );

        $database->query('DROP TABLE IF EXISTS utest_nopkey');
        $database->query('CREATE TABLE utest_nopkey(name TEXT)');
    }

    public function tearDown()
    {
        $database = $this->getDatabase();
        $database->query('DROP TABLE IF EXISTS utest_people');
        $database->query('DROP TABLE IF EXISTS utest_nopkey');

        foreach ($this->namespacesToCleanup as $namespace) {
            try {
                $database->deleteNamespace($namespace);
            } catch (DatabaseException $ex) {
                print('Could not delete namespace! ' . $ex->getMessage());
            }
        }
    }

    /**
     * Add a record to the test table
     *
     * @param RelationalDbInterface $database
     * @return Statement
     */
    private function insertIntoTestTable(RelationalDbInterface $database)
    {
        // Create an insert statement and return it
        return $database->query(
            'INSERT INTO utest_people(name) VALUES(:name)',
            ['name' => 'david']
        );
    }

    /**
     * Check if we can run a raw query
     *
     * @return void
     */
    public function testQuery()
    {
        $database = $this->getDatabase();

        // Insert into the test table a user named 'david'
        $this->insertIntoTestTable($database);

        // Query the table for the user
        $result = $database->query(
            'SELECT * FROM utest_people WHERE name = :name',
            ["name" => "david"]
        );
        $this->assertTrue(count($result->fetchAll()) > 0);

        // Make sure a non-existent user returns 0 rows
        $result = $database->query(
            "SELECT * FROM utest_people WHERE name = :name",
            ["name" => "noexist"]
        );
        $this->assertEquals(0, count($result->fetchAll()));
    }

    /**
     * Test inserting a new row into the database
     *
     * @return void
     */
    public function testInsert()
    {
        $database = $this->getDatabase();

        $lastId = $database->insert(
            'utest_people',
            ['name' => 'sky']
        );

        $this->assertGreaterThan(0, $lastId);
    }

    /**
     * Test inserting a new row into a table without a serialized pkay
     *
     * @return void
     */
    public function testInsert_NoPkey()
    {
        $database = $this->getDatabase();

        $lastId = $database->insert(
            'utest_nopkey',
            ['name' => 'sky']
        );

        $this->assertEquals(0, $lastId);
    }

    /**
     * Test updating the database with conditions
     *
     * @return void
     */
    public function testUpdate()
    {
        $database = $this->getDatabase();

        // Insert a few rows to make sure conditions limit
        $database->insert('utest_people', ['name' => 'Name1']);
        $database->insert('utest_people', ['name' => 'Name2']);
        $lastId = $database->insert('utest_people', ['name' => 'Name3']);

        $data = ['name' => 'Sky'];
        $conditions = ['id' => $lastId];
        $numUpdated = $database->update('utest_people', $data, $conditions);

        // Update should have only updated the last record
        $this->assertEquals(1, $numUpdated);
    }

    /**
     * Test getting the last inserted serialized pkey
     *
     * @return void
     */
    public function testGetLastInsertId()
    {
        $database = $this->getDatabase();

        // Add something and then get the last id
        $database->beginTransaction();
        $this->insertIntoTestTable($database);
        $lastInsertId = $database->getLastInsertId();
        $database->commitTransaction();
        $this->assertTrue($lastInsertId > 0);

        // Make sure it increments again
        $database->beginTransaction();
        $this->insertIntoTestTable($database);
        $nextInsertedId = $database->getLastInsertId();
        $database->commitTransaction();
        $this->assertEquals(++$lastInsertId, $nextInsertedId);
    }

    /**
     * Make sure an exception is thrown if getLastInsertedId is called
     * on a table without a primary key.
     *
     * @return void
     */
    public function testGetLastInsertId_NoPkey()
    {
        $database = $this->getDatabase();

        // Add something and then get the last id
        $database->beginTransaction();
        $database->query(
            'INSERT INTO utest_nopkey(name) VALUES(:name)',
            ['name' => 'david']
        );
        $this->expectException(DatabaseException::class);
        $lastInsertId = $database->getLastInsertId();
        $database->commitTransaction();
        $this->assertTrue($lastInsertId > 0);
    }

    public function testTransactionCommmit()
    {
        $database = $this->getDatabase();

        $database->beginTransaction();

        // Insert into the test table a user named 'david'
        $this->insertIntoTestTable($database);

        $database->commitTransaction();

        // Query the table for the user
        $result = $database->query(
            "SELECT * FROM utest_people WHERE name = :name",
            ["name" => "david"]
        );
        $this->assertTrue(count($result->fetchAll()) > 0);

        // Make sure a non-existent user returns 0 rows
        $result = $database->query(
            "SELECT * FROM utest_people WHERE name = :name",
            ["name" => "noexist"]
        );
        $this->assertEquals(0, count($result->fetchAll()));
    }

    public function testTransactionRollback()
    {
        $database = $this->getDatabase();

        $database->beginTransaction();

        // Insert into the test table a user named 'david'
        $this->insertIntoTestTable($database);

        $database->rollbackTransaction();

        // Make sure the insert did not get committed
        $result = $database->query(
            "SELECT * FROM utest_people WHERE name = :name",
            ["name" => "david"]
        );
        $this->assertEquals(0, count($result->fetchAll()));
    }

    /**
     * Test deleting a unique namespace/schema for an account or user
     *
     * @return void
     */
    public function testCreateNamespace()
    {
        $database = $this->getDatabase();
        $this->namespacesToCleanup[] = 'utest_created';
        $ret = $database->createNamespace('utest_created');
        $this->assertTrue($ret);
    }

    /**
     * Test setting a unique namespace/schema for an account or user
     *
     * @return void
     */
    public function testSetNamespace()
    {
        $database = $this->getDatabase();
        $this->namespacesToCleanup[] = 'utest_set_namespace';
        $database->createNamespace('utest_set_namespace');

        // Try querying tables found in the old namespace
        $this->expectException(DatabaseException::class);

        // This table is not in the utest_set_namespace namespace
        $database->setNamespace('utest_set_namespace');
        $database->query(
            "SELECT * FROM utest_people WHERE name = :name",
            ["name" => "david"]
        );
    }
}
