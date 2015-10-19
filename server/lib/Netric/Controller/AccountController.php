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
	 */
	public function get()
	{
		$ret = array(
			"id" => $this->account->getId(),
			"name" => $this->account->getName(),
			"orgName" => "", // TODO: $this->account->get
			"defaultModule" => "notes", // TODO: this should be home until it is configurable
		);

		// Get account modules
		// TODO: this should be dynamic
		$ret['modules'] = array(
			array(
				"name" => "notes",
				"title" => "Notes",
				"icon" => "pencil-square-o",
				"defaultRoute" => "all-notes",
				"navigation" => array(
					array(
						"title" => "New Note",
						"type" => "entity",
						"route" => "new-note",
						"objType" => "note",
						"icon" => "plus",
					),
					array(
						"title" => "All Notes",
						"type" => "browse",
						"route" => "all-notes",
						"objType" => "note",
						"icon" => "tags",
						"browseby" => "groups",
					),
				),
			),
			array(
				"name" => "work",
				"title" => "Work",
				"icon" => "check-square-o",
				"defaultRoute" => "all-tasks",
				"navigation" => array(
					array(
						"title" => "New Task",
						"type" => "entity",
						"route" => "new-task",
						"objType" => "task",
						"icon" => "plus",
					),
					array(
						"title" => "All Tasks",
						"type" => "browse",
						"route" => "all-tasks",
						"objType" => "task",
						"icon" => "list-ul",
						"browseby" => "groups",
					),
				),
			),
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
