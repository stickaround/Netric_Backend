<?php
/**
 * Controller for account interactoin
 */
namespace Netric\Controller;

use \Netric\Mvc;

class ModuleController extends Mvc\AbstractController
{
	/**
	 * Get the definition of an account
	 * 
	 * @param array $params Array of params from get > post > cookie
	 */
	public function get($params=array())
	{
		// TODO: this should be dynamic
		$ret = array(
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
					"title" => "All Notes",
					"objType" => "note",
					"icon" => "tags",
					"browseby" => "groups",
				),
			),
		);

		return $this->sendOutput($ret);
	}
}
