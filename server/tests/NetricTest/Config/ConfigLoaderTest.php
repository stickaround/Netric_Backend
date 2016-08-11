<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace NetricTest\Config;

use Netric\Config\ConfigLoader;

class ConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testFromFolder()
    {
        $appEnv = "development";
        $config = ConfigLoader::fromFolder(__DIR__ . "/Fixture", $appEnv);

        $aGlobal = include(__DIR__ . "/Fixture/global.php");
        $aDevel = include(__DIR__ . "/Fixture/development.php");
        $aLocal = include(__DIR__ . "/Fixture/local.php");

        // Make sure global properties are set
        $this->assertEquals($aGlobal['global_property'], $config->global_property);

        // Make sure that properties only defined in the environment file are set
        $this->assertEquals($aDevel['development_property'], $config->development_property);

        // Make sure that the local values override the global
        $this->assertEquals($aLocal['please_override'], $config->please_override);
        $this->assertNotEmpty($aGlobal['please_override'], $config->please_override);
    }

    public function testFromFolderMissingAppEnv()
    {
        // Not passing an appEnv (second param) should result in development.php not loading
        $config = ConfigLoader::fromFolder(__DIR__ . "/Fixture");

        $this->assertNotNull($config);

        // Make sure a property only set in devel.php is not set
        $this->assertNull($config->development_property);
    }
    
    public function testMissingFile()
    {
        
    }
}
