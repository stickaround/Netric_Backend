<?php
/**
 * This is just a simple test controller
 */
namespace Netric\Controller;

use \Netric\Mvc;

class TestController extends Mvc\AbstractAccountController
{
	/**
	 * For public tests
	 */
	public function getTestAction()
	{
        return $this->sendOutput("test");
	}

	/**
	 * For console requests
	 */
	public function consoleTestAction()
	{
		return $this->getTestAction();
	}
}
