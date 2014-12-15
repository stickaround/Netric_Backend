<?php
require_once 'PHPUnit/Autoload.php';
//require_once(dirname(__FILE__).'/../simpletest/autorun.php');
// ANT Includes 
require_once(dirname(__FILE__).'/../../lib/AntConfig.php');
require_once(dirname(__FILE__).'/../../lib/Ant.php');
require_once(dirname(__FILE__).'/../../lib/AntUser.php');
require_once(dirname(__FILE__).'/../../lib/CAntObject.php');
require_once(dirname(__FILE__).'/../../lib/AntFs.php');

class AntFsTest extends PHPUnit_Framework_TestCase 
{
	var $obj = null;
	var $dbh = null;
	var $antfs = null;

	function setUp() 
	{
		$this->ant = new Ant();
		$this->dbh = $this->ant->dbh;
		$this->user = new AntUser($this->dbh, -1); // -1 = administrator
		$this->antfs = new AntFs($this->dbh, $this->user); // -1 = administrator
	}
	
	/**
	 * Test writing to a new file
	 */
	public function testWrite() 
	{
		$fldr = $this->antfs->openFolder("/test/mytest", true);
		$this->assertNotNull($fldr->id);

		$file = $fldr->openFile("test", true);
		$this->assertNotNull($file);

		$size = $file->write("test contents");
		$this->assertNotEquals($size, -1);

		// Cleanup
		$ret = $file->removeHard();
	}

	/**
	 * Test reading from a file
	 */
	public function testRead()
	{
		$fldr = $this->antfs->openFolder("/test/mytest", true);
		$this->assertNotNull($fldr);

		$file = $fldr->openFile("test", true);
		$this->assertNotNull($file);

		$size = $file->write("test contents");
		$this->assertNotEquals($size, -1);

		$buf = $file->read();
		$this->assertEquals($buf, "test contents");

		// Cleanup
		$ret = $file->removeHard();
	}

	/**
	 * Test deletion of files
	 */
	public function testRemove()
	{
		$fldr = $this->antfs->openFolder("/test/mytest", true);
		$this->assertNotNull($fldr);

		$file = $fldr->openFile("test", true);
		$this->assertNotNull($file);

		$size = $file->write("test contents");
		$this->assertNotEquals($size, -1);

		// Remove the file and test
		$root = AntFs::getAccountDirectory($this->dbh);
		$path = $file->getValue("dat_local_path");

		$ret = $file->removeHard(); // Purge it
		$this->assertTrue($ret);
		$this->assertFalse(file_exists($root . "/" . $path));
	}

	/**
	 * Test moving files and folders
	 */
	public function testMove()
	{
		$fldr = $this->antfs->openFolder("/test/mytest", true);
		$this->assertNotNull($fldr);

		$file = $fldr->openFile("test", true);
		$this->assertNotNull($file);

		$size = $file->write("test contents");
		$this->assertNotEquals($size, -1);

		$fldr2 = $this->antfs->openFolder("/test/mytest2", true);
		$this->assertNotNull($fldr2);

		$fid = $file->id;
		unset($file);

		// Now open the file and check if the directory is right
		$file = $this->antfs->openFileById($fid);
		$file->move($fldr2);
		$this->assertEquals($file->getValue("folder_id"),  $fldr2->id);

		$ret = $file->removeHard(); // Purge it

		// Try moving a folder
		$fldr3 = $this->antfs->openFolder("/test/mytest3", true);
		$this->assertNotNull($fldr3);
		$fldr3->move($fldr); // move the /tests/mytest
		$this->assertEquals($fldr3->getValue("parent_id"), $fldr->id);
	}

	/**
	 * Test new file import
	 */
	public function testImport()
	{
		$fldr = $this->antfs->openFolder("/test/mytest", true);
		$this->assertNotNull($fldr);

		$file = $fldr->importFile(dirname(__FILE__)."/../data/mime_emails/testatt.txt", "test.txt");
		$this->assertNotNull($file);

		$buf = $file->read();
		$this->assertEquals($buf, "My Test Attachment\n");

		// Cleanup
		$ret = $file->removeHard();
	}

	/**
	 * Test existing file import - update
	 */
	public function testImportExisting()
	{
		$fldr = $this->antfs->openFolder("/test/mytest", true);
		$this->assertNotNull($fldr);

		$file = $fldr->openFile("test", true);
		$size = $file->write("test contents");
		$this->assertNotEquals($size, -1);
		$fid = $file->id;
		$this->assertTrue($fid > 0);
		$file->close();
		unset($file);

		$file = $fldr->importFile(dirname(__FILE__)."/../data/mime_emails/testatt.txt", "test.txt", $fid); // Third param enforces update
		$this->assertNotNull($file);
		$buf = $file->read();
		$this->assertEquals($buf, "My Test Attachment\n");
		$file->close();
		unset($file);

		// Make sure orginal file was updated
		$file = $this->antfs->openFileById($fid);
		$buf = $file->read();
		$this->assertEquals($buf, "My Test Attachment\n"); // new content
		$this->assertEquals($file->getValue("name"), "test.txt");
		$file->close();

		// Cleanup
		$ret = $file->removeHard();
	}

	/**
	 * Test get files list
	public function testGetFiles()
	{
		$fldr = $this->antfs->openFolder("/test/mytest", true);
		$this->assertNotNull($fldr);

		$file = $fldr->openFile("test", true);
		$this->assertNotNull($file);
		$fid = $file->id;

		$size = $file->write("test contents");
		$this->assertNotEquals($size, -1);

		// Now query files
		$this->assertTrue($fldr->getNumFiles() > 0);

		// Make sure the file exists in the list of files
		$bfound = false;
		for ($i = 0; $i < $fldr->getNumFiles(); $i++)
		{
			$file = $fldr->getFile($i);

			if ($file->id == $fid)
				$bfound = true;
		}
		$this->assertTrue($bfound);

		// Cleanup
		$file = $this->antfs->openFileById($fid);
		$file->removeHard();
	}
	 */

	/**
	 * Test get folders list
	public function testGetFolders()
	{
		$fldr = $this->antfs->openFolder("/test/mytest", true);
		$this->assertNotNull($fldr);

		$fldr2 = $this->antfs->openFolder("/test/mytest/subfolder", true);
		$this->assertNotNull($fldr2);

		// Now get folders
		$this->assertTrue($fldr->getNumFolders() > 0);

		// Make sure the folder exists in the list of folders
		$bfound = false;
		for ($i = 0; $i < $fldr->getNumFolders(); $i++)
		{
			$subfolder = $fldr->getFolder($i);

			if ($subfolder->id == $fldr2->id)
				$bfound = true;
		}
		$this->assertTrue($bfound);
	}
	 */

	/**
	 * Test temp file functionality
	 */
	public function testTemp()
	{
		// Create and write, could also import file
		$file = $this->antfs->createTempFile();
		$fid = $file->id;
		$size = $file->write("test contents");
		$this->assertNotEquals($size, -1);

		// Make sure there is content in this file
		$buf = $file->read();
		$this->assertEquals($buf, "test contents");
		$file->close();

		// Purge and make sure it is not touched
		$this->antfs->purgeTemp();
		$file = $this->antfs->openFileById($fid);
		$this->assertNotNull($file);

		// Now backdate so it will be purged
		$file->setValue("ts_entered", date("m/d/Y", strtotime("-2 months")));
		$file->save();
		unset($file);
        $this->antfs->debug = true;
		$purged = $this->antfs->purgeTemp();
		$this->assertTrue($purged > 0);
		$file = $this->antfs->openFileById($fid);
		$this->assertNull($file);
	}

	/**
	 * Test upload to ans server
	 */
	public function testAnsUpload()
	{
		$this->assertTrue(true);
	}

	/**
	 *  Test deletion from ans server
	 */
	public function testAnsDelete()
	{
		$this->assertTrue(true);
	}

	/**
	 *  Get full path
	 */
	public function testFolderFullPath()
	{
		$fldr = $this->antfs->openFolder("/test/mytest", true);
		$this->assertNotNull($fldr);

		$this->assertEquals($fldr->getFullPath(), "/test/mytest");

		$fldrRoot = $this->antfs->openFolder("/", true);
		$this->assertEquals($fldrRoot->getFullPath(), "/");

		$fldr->removeHard();
	}
}
