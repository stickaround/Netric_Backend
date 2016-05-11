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
				"icon" => "child",
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
					),
					array(
						"title" => "New Lead",
						"type" => "entity",
						"route" => "new-lead",
						"objType" => "lead",
						"icon" => "plus",
					),
					array(
						"title" => "All Leads",
						"type" => "browse",
						"route" => "all-leads",
						"objType" => "lead",
						"icon" => "list-ul",
						"browseby" => "groups",
					),
					array(
						"title" => "New Opportunity",
						"type" => "entity",
						"route" => "new-opportunity",
						"objType" => "opportunity",
						"icon" => "plus",
					),
					array(
						"title" => "All Opportunities",
						"type" => "browse",
						"route" => "all-opportunity",
						"objType" => "opportunity",
						"icon" => "list-ul",
						"browseby" => "groups",
					),
					array(
						"title" => "New Campaign",
						"type" => "entity",
						"route" => "new-campaign",
						"objType" => "marketing_campaign",
						"icon" => "plus",
					),
					array(
						"title" => "All Campaigns",
						"type" => "browse",
						"route" => "all-campaigns",
						"objType" => "marketing_campaign",
						"icon" => "list-ul",
						"browseby" => "groups",
					),
					array(
						"title" => "New Case",
						"type" => "entity",
						"route" => "new-case",
						"objType" => "case",
						"icon" => "plus",
					),
					array(
						"title" => "All Cases",
						"type" => "browse",
						"route" => "all-cases",
						"objType" => "case",
						"icon" => "list-ul",
						"browseby" => "groups",
					)
				),
			),
			array(
				"name" => "content",
				"title" => "Content",
				"icon" => "newspaper-o",
				"defaultRoute" => "all-contents",
				"navigation" => array(
					array(
						"title" => "New Content",
						"type" => "entity",
						"route" => "new-content",
						"objType" => "content_feed",
						"icon" => "plus",
					),
					array(
						"title" => "All Contents",
						"type" => "browse",
						"route" => "all-contents",
						"objType" => "content_feed",
						"icon" => "list-ul",
						"browseby" => "groups",
					)
				),
			),
			array(
				"name" => "infocenter",
				"title" => "Infocenter",
				"icon" => "clipboard",
				"defaultRoute" => "all-documents",
				"navigation" => array(
					array(
						"title" => "New Document",
						"type" => "entity",
						"route" => "new-document",
						"objType" => "infocenter_document",
						"icon" => "plus",
					),
					array(
						"title" => "All Documents",
						"type" => "browse",
						"route" => "all-documents",
						"objType" => "infocenter_document",
						"icon" => "tags",
						"browseby" => "groups",
					),
				),
			),
			array(
				"name" => "settings",
				"title" => "Settings",
				"icon" => "wrench",
				"defaultRoute" => "workflows",
				"navigation" => array(
					array(
						"title" => "Automated Workflows",
						"type" => "browse",
                        "objType" => "workflow",
						"route" => "workflows",
						"icon" => "cogs",
					),
					array(
						"title" => "New User",
						"type" => "entity",
						"route" => "new-user",
						"objType" => "user",
						"icon" => "user-plus",
					),
					array(
						"title" => "Users",
						"type" => "browse",
						"objType" => "user",
						"route" => "users",
						"icon" => "users"
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
				"email" => $user->getValue("email")
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
