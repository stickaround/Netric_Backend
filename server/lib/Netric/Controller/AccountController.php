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
	public function getGetAction()
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
					array(
						"title" => "New Project",
						"type" => "entity",
						"route" => "new-project",
						"objType" => "project",
						"icon" => "plus",
					),
					array(
						"title" => "All Projects",
						"type" => "browse",
						"route" => "all-projects",
						"objType" => "project",
						"icon" => "list-ul",
						"browseby" => "groups",
					),
				),
			),
			array(
				"name" => "crm",
				"title" => "CRM",
				"icon" => "check-square-o",
				"defaultRoute" => "all-customers",
				"navigation" => array(
					array(
						"title" => "New Customer",
						"type" => "entity",
						"route" => "new-customer",
						"objType" => "customer",
						"icon" => "plus",
					),
					array(
						"title" => "All Customers",
						"type" => "browse",
						"route" => "all-customers",
						"objType" => "customer",
						"icon" => "list-ul",
						"browseby" => "groups",
					)
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

	/**
	 * Just in case they use POST
	 */
	public function postGetAction()
	{
		return $this->getGetAction();
	}
}
