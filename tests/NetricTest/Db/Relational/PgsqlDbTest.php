<?php
namespace icf\core\test\rdb;

use icf\core\exception\DatabaseException;
use icf\core\rdb\RDb;
use icf\core\config\Config;
use icf\core\service\ServiceLocator;
use icf\core\test\rdb\testasset\RDbWithPdoFailure;

/**
 * This file should be extended to test any database adapters/interfaces
 * @group integration
 */
class RDbTest extends AbtractRelatinoalDbTest
{
    private $sTempPath = null;

    /**
     * The path of the database file for testing
     *
     * @var string
     */
    private $sRDbFilePath = null;

    /**
     * Handle to test database
     *
     * @var RDb
     */
    private $oRDb = null;

    public function setUp()
    {
        $this->sTempPath = sys_get_temp_dir() . '/rdb_test';

        if (!file_exists($this->sTempPath)) {
            mkdir($this->sTempPath, 0777, true);
        }

        $this->sRDbFilePath = $this->sTempPath . "/rdb.sqlite";
        $this->oRDb = new RDb(RDb::DRIVER_SQLITE, $this->sRDbFilePath);

        $this->setupTables($this->oRDb);
    }

    public function tearDown()
    {
        $this->dropTables($this->oRDb);
      
        // Get rid of the test database
        if (file_exists($this->sRDbFilePath)) {
            unlink($this->sRDbFilePath);
        }

        // Delete temp dir
        @rmdir($this->sTempPath);
    }

    /**
     * Used by abstract tests
     */
    protected function getRDbHandle()
    {
        return $this->oRDb;
    }

    public function test_getConnection()
    {
        $oRDb = new RDb(RDb::DRIVER_SQLITE, $this->sRDbFilePath);
        $oReflectedRDb = new \ReflectionObject($oRDb);
        $oReflectedConnection = $oReflectedRDb->getProperty("oConnection");
        $oReflectedConnection->setAccessible(true);

        $oReflectedGetConnection = $oReflectedRDb->getMethod("getConnection");
        $oReflectedGetConnection->setAccessible(true);

        $this->assertEmpty($oReflectedConnection->getValue($oRDb));
        $oReflectedGetConnection->invoke($oRDb);
        $this->assertEquals(get_class($oReflectedConnection->getValue($oRDb)), \PDO::class);
    }

    public function test_getConnection_succeedOnOneFailure()
    {
        /** @var RDbWithPdoFailure oRDb */
        $oRDb = new RDbWithPdoFailure(RDb::DRIVER_SQLITE, $this->sRDbFilePath);
        $oRDb->iConnectionFailures = 1;

        $this->assertTrue($oRDb->startTransaction());
        $this->assertTrue($oRDb->rollback());
    }

    public function test_getConnection_failToConnect()
    {
        /** @var RDbWithPdoFailure oRDb */
        $oRDb = new RDbWithPdoFailure(RDb::DRIVER_SQLITE, $this->sRDbFilePath);
        $oRDb->iConnectionFailures = $oRDb::MAX_ATTEMPTS;

        $this->expectException(DatabaseException::class);
        $oRDb->startTransaction();
        $oRDb->rollback();
        $this->fail('An exception should have been thrown.');
    }

    public function test_getTimingKey()
    {
        $oRDb = new RDb(RDb::DRIVER_SQLITE, $this->sRDbFilePath, 'somedb');
        $method = new \ReflectionMethod(get_class($oRDb), 'getTimingKey');
        $method->setAccessible(true);

        $oServiceLocator = ServiceLocator::getInstance();
        $oServiceLocator->clearAliasesAndCachedServices();
        $oServiceLocator->registerAlias('config', new Config(['appname' => 'icfcoretest']));

        $sTimingKey = $method->invoke($oRDb, ['some', 'tables']);

        $this->assertEquals('icfcoretest.sql.somedb.some_tables', $sTimingKey);
    }

    public function test_getTimingKey_missingConfig()
    {
        $oRDb = new RDb(RDb::DRIVER_SQLITE, $this->sRDbFilePath, 'somedb');
        $method = new \ReflectionMethod(get_class($oRDb), 'getTimingKey');
        $method->setAccessible(true);

        $oServiceLocator = ServiceLocator::getInstance();
        $oServiceLocator->clearAliasesAndCachedServices();

        $sTimingKey = $method->invoke($oRDb, ['some', 'tables']);

        $this->assertNull($sTimingKey);
    }

    public function test_getTimingKey_emptyTables()
    {
        $oRDb = new RDb(RDb::DRIVER_SQLITE, $this->sRDbFilePath, 'somedb');
        $method = new \ReflectionMethod(get_class($oRDb), 'getTimingKey');
        $method->setAccessible(true);

        $oServiceLocator = ServiceLocator::getInstance();
        $oServiceLocator->clearAliasesAndCachedServices();
        $oServiceLocator->registerAlias('config', new Config(['appname' => 'icfcoretest']));

        $sTimingKey = $method->invoke($oRDb, []);

        $this->assertEquals("icfcoretest.sql.somedb.unknown", $sTimingKey);
    }
}