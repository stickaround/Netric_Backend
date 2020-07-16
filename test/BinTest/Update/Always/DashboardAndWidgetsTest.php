<?php

/**
 * Make sure the bin/scripts/update/always/07-dashboard-and-widgets.php script works
 */

namespace BinTest\Update\Always;

use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

class DashboardAndWidgetsTest extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Path to the script to test
     *
     * @var string
     */
    private $scriptPath = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/always/07-dashboard-and-widgets.php";
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right, but why
     * not just use unit tests for our tests? :)
     */
    public function testExists()
    {
        $this->assertTrue(file_exists($this->scriptPath), $this->scriptPath . " not found!");
    }

    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testRun()
    {
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);

        $dashboardEntity = $entityLoader->getByUniqueName(ObjectTypes::DASHBOARD, "activity");
        $this->assertNotNull($dashboardEntity->getValue("guid"));

        $widgetEntity = $entityLoader->getByUniqueName(ObjectTypes::DASHBOARD_WIDGET, "", array(
            "dashboard_id" => $dashboardEntity->getEntityId(),
            "widget_name" => "activity"
        ));

        $this->assertNotNull($widgetEntity->getValue("guid"));
    }
}
