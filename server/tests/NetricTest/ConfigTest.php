<?php
/**
 * Test core netric application class
 */
namespace ApplicationTest;

use Netric;
use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    private $path = "";
    
    function setUp() 
	{
		$this->path = dirname(__FILE__).'/../data/config';
	}
    
    /**
	 * Test loading data
	 */
	public function testLoadSetting()
	{
		$config = new Netric\Config(null, $this->path);

		$this->assertEquals($config->testvar, "test");
		$this->assertEquals($config->section['val'], "sectest");
	}

	/**
	 * Test subvals
	 */
	public function testOverrides()
	{
		$config = new Netric\Config("sub", $this->path); // load ant.sub.ini

		// Check a base var that is not overidden
		$this->assertEquals($config->section['stay'], "unchanged");

		// Inherited overrides
		$this->assertEquals($config->testvar, "testsub");
		$this->assertEquals($config->section['val'], "sectestsub");
	}

	/**
	 * Test local
	 */
	public function testLocal()
	{
		$config = new Netric\Config("sublcl", $this->path); // load ant.sublcl.local.ini

		// Stay is not set in the ant.sub.ini but it is set in the ant.sub.local.ini file
		$this->assertEquals($config->section['stay'], "local");
	}
}