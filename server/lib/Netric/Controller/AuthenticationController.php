<?php
/**
 * Controller for handling user authentication
 */
namespace Netric\Controller;

use \Netric\Mvc;

class AuthenticationController extends Mvc\AbstractController
{
	/**
	 * Authenticate a new user
	 * 
	 * @param array $params Array of params from get > post > cookie
	 */
	public function authenticate($params=array())
	{
		$sm = $this->account->getServiceManager();
		$authService = $sm->get("/Netric/Authentication/AuthenticationService");

		// TODO: Authenticate a user

		return $this->sendOutput($ret);
	}

	/**
	 * Clear an identity and log out
	 * 
	 * @param array $params Array of params from get > post > cookie
	 */
	public function logout($params=array())
	{
		

		return $this->sendOutput($ret);
	}
}
