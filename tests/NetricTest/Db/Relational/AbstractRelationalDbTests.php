<?php
namespace NetricTest\Db\Relational;

use PHPUnit\Framework\TestCase;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Db\Relational\Statement;
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
                const_name TEXT,
                name TEXT
            )'
        );

        $database->query('DROP TABLE IF EXISTS utest_nopkey');
        $database->query('CREATE TABLE utest_nopkey(name TEXT)');
        $database->query('ALTER TABLE utest_people ADD CONSTRAINT utest_constraint UNIQUE (const_name)');
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
    public function testInsertNoPkey()
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
     * Test deleting rows with conditions
     *
     * @return void
     */
    public function testDelete()
    {
        $database = $this->getDatabase();

        // Insert a few rows to make sure conditions limit
        $database->insert('utest_people', ['name' => 'Name1']);
        $database->insert('utest_people', ['name' => 'Name2']);
        $lastId = $database->insert('utest_people', ['name' => 'Name3']);

        $conditions = ['id' => $lastId];
        $numDeleted = $database->delete('utest_people', $conditions);

        // Delete should have only deleted the last record
        $this->assertEquals(1, $numDeleted);
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
     * Test getting the last inserted ID when the ID is defined
     *
     * @return void
     */
    public function testGetLastInsertIdWithCustomId()
    {
        $database = $this->getDatabase();

        $lastId = $database->insert('utest_people', ['id' => 1000, 'name' => 'Name1']);

        // It should have returned the ID that was manually passed rather than the sequence
        $this->assertEquals(1000, $lastId);
    }

    /**
     * Make sure an exception is thrown if getLastInsertedId is called
     * on a table without a primary key.
     *
     * @return void
     */
    public function testGetLastInsertIdNoPkey()
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

    /**
     * Make sure the databaase is aware if wheither or not a table has a column
     *
     * @return void
     */
    public function testColumnExists()
    {
        $database = $this->getDatabase();
        
        // The created utest_people in setup has a name, but not a noexist column
        $this->assertTrue($database->columnExists('utest_people', 'name'));
        $this->assertFalse($database->columnExists('utest_people', 'noexist'));
    }

    /**
     * Test if the database knows if a table exists or not
     *
     * @return void
     */
    public function testTableExists()
    {
        $database = $this->getDatabase();
        
        // utest_people is created on setup, but nonexisttable is not
        $this->assertTrue($database->tableExists('utest_people'));
        $this->assertFalse($database->tableExists('nonexisttable'));
    }

    /**
     * Test if the database is aware if a namespace(db/schema) exists
     *
     * @return void
     */
    public function testNamespaceExists()
    {
        $database = $this->getDatabase();
        $this->namespacesToCleanup[] = 'utest_namespace_exists';
        $database->createNamespace('utest_namespace_exists');

        $this->assertTrue($database->namespaceExists('utest_namespace_exists'));
        $this->assertFalse($database->namespaceExists('utest_namespace_noexists'));
    }

    /**
     * Test if the database has a constraint
     *
     * @return void
     */
    public function testConstraintName()
    {
        $database = $this->getDatabase();

        $this->assertTrue($database->constraintExists('utest_people', 'utest_constraint'));
        $this->assertFalse($database->constraintExists('utest_people', 'utest_constraint_false'));
    }

    /**
     * Test the getting of primary key of the database
     *
     * @return void
     */
    public function testGetPrimaryKeys()
    {
        $database = $this->getDatabase();

        $primaryKeys = $database->getPrimaryKeys("utest_people");

        $this->assertEquals(sizeof($primaryKeys), 1);
        $this->assertEquals($primaryKeys[0]["attname"], "id");
    }

    /**
     * Test the checking of the column of database if it is a primary key or not
     *
     * @return void
     */
    public function testIsColumnPrimaryKey()
    {
        $database = $this->getDatabase();

        $ret = $database->isColumnPrimaryKey("utest_people", "id");
        $this->assertTrue($ret);

        $ret = $database->isColumnPrimaryKey("utest_people", "id_not_primary_key");
        $this->assertFalse($ret);
    }
}
