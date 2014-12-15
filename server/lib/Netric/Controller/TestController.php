<?php
/**
 * This is just a simple test controller
 */
namespace Netric\Controller;

use \Netric\Mvc;

class TestController extends Mvc\AbstractController
{
	public function test($params=array())
	{
        return $this->sendOutput("test");
	}
}
