<?php
/**
 * This is just a simple test controller
 */
namespace Netric\Controller;

use \Netric\Mvc;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityQuery;

class TestController extends Mvc\AbstractAccountController
{
    /**
     * For public tests
     */
    public function getTestAction()
    {
        $serviceManager = $this->account->getServiceManager();
        $index = $serviceManager->get(IndexFactory::class);


        $query = new EntityQuery("task");
        $query->where('*')->fullText("reference");


        $res = $index->executeQuery($query);

        // $this->assertEquals(1, $res->getTotalNum());
        // $obj = $res->getEntity(0);

        return $this->sendOutput("test - " . $res->getTotalNum());
    }

    public function postTestAction()
    {
        $rawBody = $this->getRequest()->getBody();
        return $this->sendOutput(json_decode($rawBody, true));
    }

    /**
     * For console requests
     */
    public function consoleTestAction()
    {
        return $this->getTestAction();
    }
}
