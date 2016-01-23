<?php
/**
 * Controller for handling user authentication
 */
namespace Netric\Controller;

use Netric\Mvc;
use Netric\Console\Console;

class AuthenticationController extends Mvc\AbstractController
{
	/**
	 * Override to allow anonymous users to access this controller for authentication
	 *
	 * @return \Netric\Permissions\Dacl
	 */
	public function getAccessControlList()
	{
		$dacl = new \Netric\Permissions\Dacl();

		// By default allow authenticated users to access a controller
		$dacl->allowGroup(\Netric\Entity\ObjType\UserEntity::GROUP_EVERYONE);

		return $dacl;
	}

	/**
	 * Authenticate a new user
	 */
	public function getAuthenticateAction()
	{
		$username = $this->request->getParam("username");
		$password = $this->request->getParam("password");

		if (!$username || !$password)
		{
			return $this->sendOutput(
				array(
					"result"=>"FAIL", 
					"reason"=>"Both username and password are required fields"
				)
			);
		}

		// Get the authentication service and authenticate the credentials
		$sm = $this->account->getServiceManager();
		$authService = $sm->get("/Netric/Authentication/AuthenticationService");
		$sessionStr = $authService->authenticate($username, $password);

		// Return the status
		if ($sessionStr)
		{
			// Set cookie for non-app access such as server renders
			if (!Console::isConsole())
				setcookie('Authentication', $sessionStr, $authService->getExpiresTs(), '/');

			// Return session token
			$ret = array(
				"result" => "SUCCESS",
				"session_token" => $sessionStr,
			);
		}
		else
		{
			$ret = array(
				"result" => "FAIL",
				"reason" => $authService->getFailureReason(),
			);
		}


		return $this->sendOutput($ret);
	}

	/**
	 * Authenticate a new user - POST version
	 */
	public function postAuthenticateAction($params=array())
	{
		return $this->getAuthenticateAction();
	}

	/**
	 * Clear an identity and log out
	 */
	public function getLogoutAction()
	{
		// Destroy any cookies
		$this->request->setParam("Authentication", null);
		if (!Console::isConsole())
		{
			unset($_COOKIE['Authentication']);
    		setcookie('Authentication', null, -1, '/');	
		}
		
		return $this->sendOutput(array("result"=>"SUCCESS"));
	}

	/**
	 * Check if a session is still valid
	 */
	public function getCheckinAction()
	{
		$sm = $this->account->getServiceManager();
		$authService = $sm->get("/Netric/Authentication/AuthenticationService");

		$ret = array(
			"result" => ($authService->getIdentity()) ? "OK" : "FAIL"
		);
		
		return $this->sendOutput($ret);
	}

	/**
	 * Get all accounts associated with a domain and return the name and instance URL
	 */
	public function getGetAccountsAction()
	{
		$email = $this->request->getParam("email");

		// TODO: Figure out a way to authorize the requestor so that
		// a bot cannto use this endpoint to validate email addresses.

		$ret = array();

		if ($email)
			$ret = $this->account->getApplication()->getAccountsByEmail($email);
		
		return $this->sendOutput($ret);
	}
}
