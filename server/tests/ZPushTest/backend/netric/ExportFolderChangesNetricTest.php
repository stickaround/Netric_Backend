<?php
namespace ZPushTest\backend\netric;

use PHPUnit\Framework\TestCase;

// Add all z-push required files
require_once("z-push.includes.php");

// Include config
require_once(dirname(__FILE__) . '/../../../../config/zpush.config.php');

// Include backend classes
require_once('backend/netric/netric.php');
require_once('backend/netric/entityprovider.php');

class ExportFolderChagesNetricTest extends TestCase
{
    /**
     * Logger interface
     *
     * @var \Netric\Log\LogInterface
     */
    private $log = null;

    /**
     * Entity provider mock
     *
     * @var \EntityProvider
     */
    private $entityProvider = null;


    protected function setUp()
    {
        $this->log = $this->getMockBuilder('\Netric\Log\LogInterface')
            ->disableOriginalConstructor()
            ->getMock();

        // Initialize zpush - copied from zpush index file
        if (!defined ( 'REAL_BASE_PATH' )) {
            \ZPush::CheckConfig();
        }

        $this->entityProvider = $this->getMockBuilder('\EntityProvider')
            ->disableOriginalConstructor()
            ->getMock();

        // The memory importer we are using for tests will call a static Request object
        // to try and check the deviceid. We need to set it here in order for the tests to pass.
        $class = new \ReflectionClass('\Request');
        $property = $class->getProperty("devid");
        $property->setAccessible(true);
        $property->setvalue("testdeviceid");
    }

    public function testInitializeExporter()
    {
        $exporter = new \ExportFolderChangeNetric(
            $this->log,
            $this->entityProvider
        );

        // Create an in-memory importer
        $importer = new \ChangesMemoryWrapper();

        // Create fake folder to return
        $syncFolder = new \SyncFolder();
        $syncFolder->serverid = 'test';
        $syncFolder->displayname = "Test";
        $this->entityProvider->method('getAllFolders')->willReturn(array($syncFolder));

        // Initialize
        $ret = $exporter->InitializeExporter($importer);
        $this->assertTrue($ret);
    }

    public function testGetChangeCount()
    {
        $exporter = new \ExportFolderChangeNetric(
            $this->log,
            $this->entityProvider
        );

        // Create an in-memory importer
        $importer = new \ChangesMemoryWrapper();

        // Create fake folder to return
        $syncFolder = new \SyncFolder();
        $syncFolder->serverid = 'test';
        $syncFolder->displayname = "Test";
        $this->entityProvider->method('getAllFolders')->willReturn(array($syncFolder));

        // Initialize
        $exporter->InitializeExporter($importer);

        // Make sure the changes have been registered
        $this->assertEquals(1, $exporter->GetChangeCount());
    }

    public function testSynchronize()
    {
        // Create a SyncFolder for testing and return from mock entity provider
        $syncFolder = new \SyncFolder();
        $syncFolder->serverid = 'test';
        $syncFolder->displayname = "Test";
        $syncFolder->parentid = "0";
        $this->entityProvider->method('getFolder')->willReturn($syncFolder);
        $this->entityProvider->method('getAllFolders')->willReturn(array($syncFolder));

        // Create exporter
        $exporter = new \ExportFolderChangeNetric(
            $this->log,
            $this->entityProvider
        );

        // Call config which passes the saved state. We will pass a fake previously saved folder with
        // and id of 'testdel' so that we can simulate deleting folders that no longer exist in the provider
        $syncFolderDelete = new \SyncFolder();
        $syncFolderDelete->serverid = 'testdel';
        $syncFolderDelete->displayname = "DelTest";
        $syncFolder->parentid = "0";
        $exporter->Config(array(array('id' => $syncFolderDelete->serverid, 'flags' => 0)));

        // Create an in-memory importer
        $importer = new \ChangesMemoryWrapper();

        // Add the folder that we are going to delete (previously imported but not in exporter above)
        $importer->AddFolder($syncFolderDelete);

        // Initialize the netric exporter with the in-memory importer
        $exporter->InitializeExporter($importer);

        // Synchronize - first pass should get changes
        // 1: the new folder that the exporter got from getAllFolders
        // 2. The folder to delete that was previously in the importer state
        $result = $exporter->Synchronize();

        // If a change is made it should return steps and progress
        $this->assertEquals(array("steps" => 2, "progress" => 1), $result);

        // Synchronize again which should process the second change
        $result = $exporter->Synchronize();

        // If a change is made it should return steps and progress
        $this->assertEquals(array("steps" => 2, "progress" => 2), $result);

        // Make sure the importer delete was called for folder 'test'
        $this->assertTrue($importer->IsChanged($syncFolder));

        // Make sure the number of changes is right
        $this->assertEquals(2, $importer->GetChangeCount());

        // Calling Synchronize  a second time should return false since there are no changes
        $this->assertFalse($exporter->Synchronize());

        // Check that the state was updated
        $expectedState = array(
            array (
                'type' => 'change',
                'id' => 'test',
                'mod' => 'Test',
                'parent' => "0"
            )
        );
        $this->assertEquals($expectedState, $exporter->GetState());
    }
}