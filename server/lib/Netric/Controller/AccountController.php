<?php
/**
 * Controller for account interactoin
 */
namespace Netric\Controller;

use \Netric\Mvc;

class AccountController extends Mvc\AbstractController
{
	/**
	 * Get the definition of an account
	 * 
	 * @param array $params Array of params from get > post > cookie
	 */
	public function get($params=array())
	{
		$ret = array(
			"id" => $this->account->getId(),
			"name" => $this->account->getName(),
			"orgName" => "", // TODO: $this->account->get
			"defaultModule" => "notes", // TODO: this should be home until it is configurable
		);

		// Add the user if we have a currently authenticated user
		$user = $this->account->getUser();
		if ($user)
		{
			$ret["user"] = array(
				"id" => $user->getId(),
				"name" => $user->getValue("name"),
				"fullName" => $user->getValue("full_name"),
			);
		}

		return $this->sendOutput($ret);
	}
}
