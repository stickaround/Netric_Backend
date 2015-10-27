<?php
/**
 * Manage entity browser views
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\BrowserView;

use Netric\Db\DbInterface;
use Netric\Entity\ObjType\User;
use Netric\EntityDefinition;
use Netric\Config;


/**
 * Class for managing entity forms
 *
 * @package Netric\Entity
 */
class BrowserViewService
{
    /**
     * Database handle
     *
     * @var \Netric\Db\DbInterface
     */
    private $dbh = null;

    /**
     * Netric configuration
     *
     * @var \Netric\Config
     */
    private $config = null;

    /**
     * A cache of all loaded BrowserViews from the DB
     *
     * Each object type will be cached in $this->views[$objType]
     *
     * @var array
     */
    private $views = array();

    /**
     * Class constructor to set up dependencies
     *
     * @param \Netric\Db\DbInterface
     */
    public function __construct(DbInterface $dbh, Config $config)
    {
        $this->dbh = $dbh;
        $this->config = $config;
    }

    /**
     * Get browser views for a user
     *
     * Here is how views will be loaded:
     *  1. First get system (file) views
     *  2. Then add account views
     *  3. Then add team views if user is a memeber
     *  4. Then add user specific views for the user
     * @param $objType
     * @param $user
     */
    public function getViewsForUser($objType, $user)
    {

    }

    /**
     * Get team views that are saved to the database
     *
     * @param $objType The object type to get browser views for
     * @return BrowserView[]
     */
    public function getUserViews($obj, $teamId)
    {

    }

    /**
     * Get team views that are saved to the database for teams only
     *
     * @param $objType The object type to get browser views for
     * @return BrowserView[]
     */
    public function getTeamViews($obj, $teamId)
    {

    }

    /**
     * Get account views that are saved to the database for everyone
     *
     * @param $objType The object type to get browser views for
     * @return BrowserView[]
     */
    public function getAccountViews($objType)
    {

    }

    /**
     * Get system/default views from config files
     *
     * @param $objType The object type to get browser views for
     * @return BrowserView[]
     */
    public function getSystemViews($objType)
    {
        if (!$objType)
            return false;

        $views = array();

        // Check for system object
        if (file_exists($basePath . "/browser_views/" . $objType . ".php"))
        {
            $viewsData = include($basePath . "/browser_views/" . $objType . ".php");

            // Initialize all the views from the returned array
            foreach ($viewsData as $key=>$vData)
            {
                $view = new BrowserView();
                $view->fromArray($vData);
                $views[] = $view;
            }

        }

        return $views;
    }

    /**
     * Save this view to the database
     *
     * @param BrowserView $view The view to save
     */
    public function saveView(BrowserView $view)
    {

        /*
        $result = $dbh->Query("insert into app_object_views(name, description, filter_key, user_id, object_type_id, report_id)
								values('".$dbh->Escape($this->name)."', '".$dbh->Escape($this->description)."',
									   '".$dbh->Escape($this->filterKey)."', ".$dbh->EscapeNumber($this->userid).",
									   '".$obj->object_type_id."', ".$dbh->EscapeNumber($this->reportId).");
								select currval('app_object_views_id_seq') as id;");
		if ($dbh->GetNumberRows($result))
			$this->id = $dbh->GetValue($result, 0, "id");

		// Save conditions
		foreach ($this->conditions as $cond)
		{
			$field = $obj->fields->getField($cond->fieldName);

			if ($field)
			{
				$dbh->Query("insert into app_object_view_conditions(view_id, field_id, blogic, operator, value)
								values('".$this->id."', '".$field['id']."', '".$cond->blogic."',
									   '".$cond->operator."', '".$cond->value."')");
			}
		}

		// Save fields
		$sort_order = 1;
		foreach ($this->view_fields as $fld)
		{
			$field = $obj->fields->getField($fld);

			if ($field)
			{
				$dbh->Query("insert into app_object_view_fields(view_id, field_id, sort_order)
											 values('".$this->id."', '".$field['id']."', '$sort_order')");
			}

			$sort_order++;
		}

		// order by
		$sort_order = 1;
		foreach ($this->sort_order as $sort)
		{
			$field = $obj->fields->getField($sort->fieldName);

			if ($field)
			{
				$dbh->Query("insert into app_object_view_orderby(view_id, field_id, order_dir, sort_order)
							 values('".$this->id."', '".$field['id']."', '".$sort->direction."', '$sort_order')");
			}

			$sort_order++;
		}

		return $this->id;
         */

    }

    /**
     * This will do a one-time load of all the views from the database and cache
     *
     * @param string $objType The object type to load
     */
    private function loadViewsFromDb($objType)
    {
        $sql = "SELECT
                    id, name, scope, description, filter_key,
                    user_id, object_type_id, f_default, team_id, owner_id
                FROM app_object_views WHERE object_type_id="; // TODO: get obj type
        $result = $dbh->Query($sql);

        // TODO: Load the attributes of each brower view below

        // The below is old code to get the conditions, view+fields, and sort order normalized (yuk)
        /*
        // Get view_fields
			$res2 = $dbh->Query("select app_object_type_fields.name from app_object_type_fields, app_object_view_fields where
								 app_object_view_fields.field_id=app_object_type_fields.id and app_object_view_fields.view_id='".$this->id."'
								 order by app_object_view_fields.sort_order");
			$num2 = $dbh->GetNumberRows($res2);
			for ($j = 0; $j < $num2; $j++)
			{
				$row2 = $dbh->GetRow($res2, $j);
				$this->view_fields[] = $row2['name'];
			}

			// Get conditions
			$res2 = $dbh->Query("select app_object_view_conditions.id, app_object_type_fields.name, app_object_view_conditions.blogic,
								 app_object_view_conditions.operator, app_object_view_conditions.value
								 from app_object_type_fields, app_object_view_conditions where
								 app_object_view_conditions.field_id=app_object_type_fields.id
								 and app_object_view_conditions.view_id='".$this->id."'
								 order by app_object_view_conditions.id");
			$num2 = $dbh->GetNumberRows($res2);
			for ($j = 0; $j < $num2; $j++)
			{
				$row2 = $dbh->GetRow($res2, $j);
				$this->conditions[] = new CAntObjectCond($row2['blogic'], $row2['name'], $row2['operator'], $row2['value']);
			}

			// Get sort order
			$res2 = $dbh->Query("select app_object_type_fields.name, app_object_view_orderby.order_dir
								 from app_object_type_fields, app_object_view_orderby where
								 app_object_view_orderby.field_id=app_object_type_fields.id and app_object_view_orderby.view_id='".$this->id."'
								 order by app_object_view_orderby.sort_order");
			$num2 = $dbh->GetNumberRows($res2);
			for ($j = 0; $j < $num2; $j++)
			{
				$row2 = $dbh->GetRow($res2, $j);
				$this->sort_order[] = new CAntObjectSort($row2['name'], $row2['order_dir']);
			}
         */
    }
}