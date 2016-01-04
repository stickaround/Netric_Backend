<?php
/**
 * Test entity/object class
 */
namespace NetricTest\Entity;

use Netric;
use Netric\Entity\Entity;
use PHPUnit_Framework_TestCase;

class EntityTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Tennant account
     * 
     * @var \Netric\Account
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
	protected function setUp() 
	{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(\Netric\User::USER_ADMINISTRATOR);
	}

	/**
	 * Test default timestamp
	 */
	public function testFieldDefaultTimestamp()
	{
		$cust = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$cust->setValue("name", "testFieldDefaultTimestamp");
		$cust->setFieldsDefault('create'); // time_created has a 'now' on 'create' default
		$this->assertTrue(is_numeric($cust->getValue("time_entered")));
	}

	/**
	 * Test default deleted to adjust for some bug with default values resetting f_deleted
	 */
	public function testSetFieldsDefaultBool()
	{
		$cust = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$cust->setValue("name", "testFieldDefaultTimestamp");
		$cust->setValue("f_deleted", true);
		$cust->setFieldsDefault('null');
		$this->assertTrue($cust->getValue("f_deleted"));
	}

	/**
	 * Test toArray funciton
	 */
	public function testToArray()
	{
		$cust = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$cust->setValue("name", "Entity_DataMapperTests");
		// bool
		$cust->setValue("f_nocall", true);
		// object
		$cust->setValue("owner_id", $this->user->getId(), $this->user->getValue("name"));
		// object_multi
		// fkey
		// fkey_multi
		// timestamp
		$cust->setValue("last_contacted", time());

		$data = $cust->toArray();
		$this->assertEquals($cust->getValue("name"), $data["name"]);
		$this->assertEquals($cust->getValue("last_contacted"), strtotime($data["last_contacted"]));
		$this->assertEquals($cust->getValue("owner_id"), $data["owner_id"]);
		$this->assertEquals($cust->getValueName("owner_id"), $data["owner_id_fval"][$data["owner_id"]]);
		$this->assertEquals($cust->getValue("f_nocall"), $data["f_nocall"]);
	}

	/**
	 * Test loading from an array
	 */
	public function testFromArray()
	{
		$data = array(
			"name" => "testFromArray",
			"last_contacted" => time(),
			"f_nocall" => true,
			"owner_id" => $this->user->getId(),
			"owner_id_fval" => array(
				$this->user->getId() => $this->user->getValue("name")
			),
		);

		// Load data into entity
		$cust = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$cust->fromArray($data);

		// Test values
		$this->assertEquals($cust->getValue("name"), $data["name"]);
		$this->assertEquals($cust->getValue("last_contacted"), $data["last_contacted"]);
		$this->assertEquals($cust->getValue("owner_id"), $data["owner_id"]);
		$this->assertEquals($cust->getValueName("owner_id"), $data["owner_id_fval"][$data["owner_id"]]);
		$this->assertEquals($cust->getValue("f_nocall"), $data["f_nocall"]);
	}

	/**
	 * Make sure we can empty a multi-value field when loading from an array
	 */
	public function testFromArrayEmptyMval()
	{
		$cust = $this->account->getServiceManager()->get("EntityLoader")->create("customer");

		// Preset attachments
		$cust->addMultiValue("attachments", 1, "fakefile.txt");

		// This should unset the attachments property set above
		$data = array(
			"attachments" => array(),
			"attachments_fval" => array(),
		);

		// Load data into entity
		$cust->fromArray($data);

		// Test values
		$this->assertEquals(0, count($cust->getValue("attachments")));
	}

	/**
	 * Test processing temp files
	 */
	public function testProcessTempFiles()
	{
		$sm = $this->account->getServiceManager();

		$fileSystem = $sm->get("Netric/FileSystem/FileSystem");
		$entityLoader = $sm->get("EntityLoader");
		$dataMapper = $sm->get("Entity_DataMapper");

		// Temp file
		$file = $fileSystem->createFile("%tmp%", "testfile.txt", true);
		$tempFolderId = $file->getValue("folder_id");

		// Create a customer
		$cust = $entityLoader->create("customer");
		$cust->setValue("name", "Aereus Corp");
		$cust->addMultiValue("attachments", $file->getId(), $file->getValue("name"));
		$dataMapper->save($cust);

		// Test to see if file was moved
		$testFile = $fileSystem->openFileById($file->getId());
		$this->assertNotEquals($tempFolderId, $testFile->getValue("folder_id"));

		// Cleanup
		$fileSystem->deleteFile($file, true);
	}

	/**
	 * Test shallow cloning an entity
	 */
	public function testClone()
	{
		$cust = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$cust->setValue("name", "Entity_DataMapperTests");

		// bool
		$cust->setValue("f_nocall", true);

		// object
		$cust->setValue("owner_id", $this->user->getId(), $this->user->getValue("name"));

		// TODO: object_multi
		// TODO: fkey
		// TODO: fkey_multi

		// timestamp
		$cust->setValue("last_contacted", time());
		// Set a fake id just to make sure it does not get copied
		$cust->setId(1);

		// Clone it
		$cloned = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$cust->cloneTo($cloned);

		$this->assertEmpty($cloned->getId());
		$this->assertEquals($cust->getValue("name"), $cloned->getValue("name"));
		$this->assertEquals($cust->getValue("f_nocall"), $cloned->getValue("f_nocall"));
		$this->assertEquals($cust->getValue("owner_id"), $cloned->getValue("owner_id"));
		$this->assertEquals($cust->getValueName("owner_id"), $cloned->getValueName("owner_id"));
		$this->assertEquals($cust->getValue("last_contacted"), $cloned->getValue("last_contacted"));
	}

	/**
	 * Test the comments counter for an entity
	 */
	public function testSetHasComments()
	{
		$cust = $this->account->getServiceManager()->get("EntityLoader")->create("customer");

		// Should have incremented 'num_comments' to 1
		$cust->setHasComments();
		$this->assertEquals(1, $cust->getValue("num_comments"));

		// The first param will decrement the counter
		$cust->setHasComments(false);
		$this->assertEquals(0, $cust->getValue("num_comments"));
	}

	/**
	 * Test getting tagged object references in text
	 */
	public function testGetTaggedObjRef()
	{
		$test1 = "Hey [user:123:Sky] this is my test";
		$taggedReferences = Entity::getTaggedObjRef($test1);
		$this->assertEquals(1, count($taggedReferences));
		$this->assertEquals(array("obj_type"=>"user", "id"=>123, "name"=>"Sky"), $taggedReferences[0]);

		$test2 = "This would test multiple [user:123:Sky] and [user:456:John]";
		$taggedReferences = Entity::getTaggedObjRef($test2);
		$this->assertEquals(2, count($taggedReferences));
		$this->assertEquals(array("obj_type"=>"user", "id"=>123, "name"=>"Sky"), $taggedReferences[0]);
		$this->assertEquals(array("obj_type"=>"user", "id"=>456, "name"=>"John"), $taggedReferences[1]);

        // Test unicode = John in Chinese
        $test1 = "Hey [user:123:约翰·] this is my test";
        $taggedReferences = Entity::getTaggedObjRef($test1);
        $this->assertEquals(1, count($taggedReferences));
        $this->assertEquals(array("obj_type"=>"user", "id"=>123, "name"=>"约翰·"), $taggedReferences[0]);
	}

    /**
     * Test update followers
     *
     * This is a private function but because it is so fundamental in its use, we test
     * it separately from any public interface via a Reflection object. While this is
     * generally not a good idea to always test functions this way, it makes sense
     * in places like this that are small largely autonomous functions
     * used to control critical functionality.
     */
    public function testUpdateFollowers()
    {
        $entity = $this->account->getServiceManager()->get("EntityLoader")->create("task");
        $entity->setValue("user_id", 123, "John");
        $entity->setValue("notes", "Hey [user:456:Dave], check this out please.");

        // Use reflection to access the private function
        $refEntity = new \ReflectionObject($entity);
        $updateFollowers = $refEntity->getMethod("updateFollowers");
        $updateFollowers->setAccessible(true);

        // Call update followers which should pull followers from user_id and notes
        $updateFollowers->invoke($entity);

        // Now make sure followers were set to the two references above
        $followers = $entity->getValue("followers");
        $this->assertEquals(array(123, 456), $followers);
    }

    /**
     * Test synchronize followers function
     */
    public function testSyncFollowers()
    {
        // Add some fake users to a test task
        $task1 = $this->account->getServiceManager()->get("EntityLoader")->create("task");
        $task1->addMultiValue("followers", 123, "John");
        $task1->addMultiValue("followers", 456, "Dave");

        // Crete a second task and synchronize
        $task2 = $this->account->getServiceManager()->get("EntityLoader")->create("task");
        $task2->syncFollowers($task1);

        $this->assertEquals(2, count($task1->getValue("followers")));
        $this->assertEquals($task1->getValue("followers"), $task2->getValue("followers"));
    }
}
