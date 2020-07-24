<?php

/**
 * Common tests for all schema DataMappers
 */

namespace NetricTest\Application\Schema;

use PHPUnit\Framework\TestCase;
use Netric\Application\Application;
use Netric\Application\Schema\SchemaDataMapperInterface;
use Netric\Application\Schema\SchemaProperty;
use NetricTest\Bootstrap;

abstract class AbstractSchemaDataMapperTests extends TestCase
{
    /**
     * Application object to test
     *
     * @var Application
     */
    private $application = null;

    /**
     * Account that the unit test is currently running under
     *
     * @var Account
     */
    private $account = null;

    /**
     * Test account name
     *
     * @var const
     */
    const TEST_ACCOUNT_NAME = 'ut_schema_testr';

    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->application = $this->account->getApplication();

        $this->deleteTestAccount();
    }

    protected function tearDown(): void
    {
        $this->deleteTestAccount();
    }

    private function deleteTestAccount()
    {
        // Cleanup if there's any test accounts
        $accountToDelete = $this->application->getAccount(null, self::TEST_ACCOUNT_NAME);
        if ($accountToDelete) {
            $this->application->deleteAccount($accountToDelete->getName());
        }
    }

    /**
     * Get the PostgreSQL DataMapper
     *
     * @param array $schemaDefinition
     * @param string $accountId THe account we will be managing the schema for
     * @return SchemaDataMapperInterface
     */
    abstract protected function getDataMapper(array $schemaDefinition, $accountId);

    /**
     * Test a created bucket by inserting data
     *
     * @param string $bucketName The name of the table/document/collection to save data to
     * @param array $data The data to insert and verify
     * @reutrn bool true if data could be inserted and read from the data store
     */
    abstract protected function insertIntoBucket($bucketName, array $data);

    /**
     * Make sure that a field with a primary key is set
     *
     * @param string $bucketName The name of the table/document/collection to test
     * @param strung|array $propertyOrProperties A property name or array of property names
     * @return bool true if the key exists
     */
    abstract protected function primaryKeyExists($bucketName, $propertyOrProperties);

    /**
     * Assure that there is an index on a given property for a bucket
     *
     * @param string $bucketName The name of the table/document/collection to test
     * @param string|array $propertyOrProperties A property name or array of property names
     * @return bool true if the index exists
     */
    abstract protected function indexExists($bucketName, $propertyOrProperties);

    /**
     * Test creating a brand new schema
     */
    public function testUpdate()
    {
        // Create a new account to update
        $account = $this->application->createAccount(
            self::TEST_ACCOUNT_NAME,
            'test',
            "test@test.com",
            "password"
        );

        // Create a test definition with all the goodies for testing
        $testDefinition = [
            "unit_test_schema" => [
                "PROPERTIES" => [
                    'id'            => ['type' => SchemaProperty::TYPE_BIGSERIAL],
                    'name'          => ['type' => SchemaProperty::TYPE_CHAR_128],
                    'value'         => ['type' => SchemaProperty::TYPE_INT],
                    'some_unique'   => ['type' => SchemaProperty::TYPE_CHAR_128]
                ],
                'PRIMARY_KEY'       => 'id',
                "INDEXES" => [
                    ['properties' => ['name']]
                ]
            ],
        ];

        $dataMapper = $this->getDataMapper($testDefinition, $account->getAccountId());
        $this->assertTrue($dataMapper->update($account->getAccountId()));

        // Now test reading and writing data
        $data = [
            "name" => 'my test value',
            "value" => 100,
        ];
        $this->assertTrue($this->insertIntoBucket("unit_test_schema", $data));

        // Make sure the primary key was setup
        $this->assertTrue($this->primaryKeyExists("unit_test_schema", "id"));

        // Make sure the index was created on the 'name' property
        $this->assertTrue($this->indexExists("unit_test_schema", "name"));
    }

    /**
     * Make sure we can get a schema defintiion hash
     *
     * @return void
     */
    public function testGetLastAppliedSchemaHash()
    {
        // Create a test definition with all the goodies for testing
        $testDefinition = [
            "unit_test_schema" => [
                "PROPERTIES" => [
                    'id'            => ['type' => SchemaProperty::TYPE_BIGSERIAL],
                    'name'          => ['type' => SchemaProperty::TYPE_CHAR_128],
                    'value'         => ['type' => SchemaProperty::TYPE_INT],
                    'some_unique'   => ['type' => SchemaProperty::TYPE_CHAR_128]
                ],
                'PRIMARY_KEY'       => 'id',
                "INDEXES" => [
                    ['properties' => ['name']]
                ]
            ],
        ];

        $account = $this->application->createAccount(
            self::TEST_ACCOUNT_NAME,
            'test',
            "test@test.com",
            "password"
        );

        $dataMapper = $this->getDataMapper($testDefinition, $account->getAccountId());
        $dataMapper->update($account->getAccountId());
        // A schema should have been set on the last application (if it exists)
        $this->assertNotEmpty($dataMapper->getLastAppliedSchemaHash());
    }

    /**
     * Verify that we can set a schema hash
     *
     * @return void
     */
    public function testSetLastAppliedSchemaHash()
    {
        // Create a test definition with all the goodies for testing
        $testDefinition = [
            "unit_test_schema" => [
                "PROPERTIES" => [
                    'id'            => ['type' => SchemaProperty::TYPE_BIGSERIAL],
                    'name'          => ['type' => SchemaProperty::TYPE_CHAR_128],
                    'value'         => ['type' => SchemaProperty::TYPE_INT],
                    'some_unique'   => ['type' => SchemaProperty::TYPE_CHAR_128]
                ],
                'PRIMARY_KEY'       => 'id',
                "INDEXES" => [
                    ['properties' => ['name']]
                ]
            ],
        ];

        $account = $this->application->createAccount(
            self::TEST_ACCOUNT_NAME,
            'test',
            "test@test.com",
            "password"
        );
        $dataMapper = $this->getDataMapper($testDefinition, $account->getAccountId());
        $dataMapper->update($account->getAccountId());
        $dataMapper->setLastAppliedSchemaHash('test');
        $this->assertEquals('test', $dataMapper->getLastAppliedSchemaHash());
    }

    /**
     * Test if we can check if column exists in the bucket definition
     *
     * @return void
     */
    public function testCheckIfColumnExist()
    {
        // Create a test definition with all the goodies for testing
        $testDefinition = [
            "unit_test_schema" => [
                "PROPERTIES" => [
                    'id'            => ['type' => SchemaProperty::TYPE_BIGSERIAL],
                    'name'          => ['type' => SchemaProperty::TYPE_CHAR_128],
                    'value'         => ['type' => SchemaProperty::TYPE_INT],
                    'field_data'    => ['type' => SchemaProperty::TYPE_JSON],
                ],
            ],
        ];

        $account = $this->application->createAccount(
            self::TEST_ACCOUNT_NAME,
            'test',
            "test@test.com",
            "password"
        );
        $dataMapper = $this->getDataMapper($testDefinition, $account->getAccountId());

        $this->assertEquals($dataMapper->checkIfColumnExist("unit_test_schema", "field_data"), true);
        $this->assertEquals($dataMapper->checkIfColumnExist("unit_test_schema", "ts_entered"), false);
        $this->assertEquals($dataMapper->checkIfColumnExist("non_existing_schema", "ts_entered"), false);
    }
}
