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
use Netric;

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
     * Entity defition loader to map type id to type name
     *
     * @var Netric\EntityDefinitionLoader
     */
    private $defLoader = null;

    /**
     * Class constructor to set up dependencies
     *
     * @param \Netric\Db\DbInterface
     * @param Config $config The configuration object
     * @param \Netric\EntityDefinitionLoader $defLoader To get definitions of entities by $objType
     */
    public function __construct(DbInterface $dbh, Config $config, Netric\EntityDefinitionLoader $defLoader)
    {
        $this->dbh = $dbh;
        $this->config = $config;
        $this->definitionLoader = $defLoader;
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
        // If we have not loaded views from the database then do that now
        if (!isset($this->views[$objType]))
            $this->loadViewsFromDb($objType);

        // TODO: Follow the logic in the comments above to return a merged list of views for this user
    }

    /**
     * Get a browser view by id
     *
     * @param string $objType The object type for this view
     * @param string $id The unique id of the view
     * @return BrowserView
     */
    public function getById($objType, $id)
    {
        // If we have not loaded views from the database then do that now
        if (!isset($this->views[$objType]))
            $this->loadViewsFromDb($objType);

        foreach ($this->views[$objType] as $view)
        {
            if ($view->getId() == $id)
                return $view;
        }

        return null;
    }

    /**
     * Get team views that are saved to the database
     *
     * @param string $objType The object type to get browser views for
     * @return BrowserView[]
     */
    public function getUserViews($objType, $userId)
    {
        // If we have not loaded views from the database then do that now
        if (!isset($this->views[$objType]))
            $this->loadViewsFromDb($objType);

        // Return all views that are set for a specific team
        $ret = array();
        foreach ($this->views[$objType] as $view)
        {
            if ($view->getUserId() && $view->getUserId() == $userId)
                $ret[] = $view;
        }
        return $ret;
    }

    /**
     * Get team views that are saved to the database for teams only
     *
     * @param $objType The object type to get browser views for
     * @return BrowserView[]
     */
    public function getTeamViews($obj, $teamId)
    {
        // If we have not loaded views from the database then do that now
        if (!isset($this->views[$objType]))
            $this->loadViewsFromDb($objType);

        // Return all views that are set for a specific team
        $ret = array();
        foreach ($this->views[$objType] as $view)
        {
            if ($view->getTeamId() && $view->getTeamId() == $teamId)
                $ret[] = $view;
        }
        return $ret;
    }

    /**
     * Get account views that are saved to the database for everyone
     *
     * @param $objType The object type to get browser views for
     * @return BrowserView[]
     */
    public function getAccountViews($objType)
    {
        // If we have not loaded views from the database then do that now
        if (!isset($this->views[$objType]))
            $this->loadViewsFromDb($objType);

        // Return all views that are not user or team views
        $ret = array();
        foreach ($this->views[$objType] as $view)
        {
            if (!$view->getTeamId() && !$view->getUserId())
                $ret[] = $view;
        }
        return $ret;
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
     * @throws \RuntimeException if it cannot load the entity definition
     * @return int Unique id of saved view
     */
    public function saveView(BrowserView $view)
    {
        $dbh = $this->dbh;

        $def = $this->definitionLoader->get($view->getObjType());

        if (!$def)
            throw new \RuntimeException("Could not get entity definition for $objType");

        $data = $view->toArray();

        if ($view->getId())
        {
            $sql = "UPDATE app_object_views SET
                      name='" . $dbh->escape($data['name']) . "',
                      description='" . $dbh->escape($data['description']) . "',
                      team_id=" . $dbh->escapeNumber($data['team_id']) . ",
                      user_id=" . $dbh->escapeNumber($data['user_id']) . ",
                      object_type_id=" . $dbh->escapeNumber($def->getId()) . ",
                      f_default='" . (($data['default']) ? 't' : 'f') . "',
                      owner_id=" . $dbh->escapeNumber($data['user_id']) . ",
                      conditions_data='" . $dbh->escape(json_encode($data['conditions'])) . "',
                      order_by_data='" . $dbh->escape(json_encode($data['order_by'])) . "',
                      table_columns_data='" . $dbh->escape(json_encode($data['table_columns'])) . "'
                    WHERE id='" . $view->getId() . "'; SELECT '" . $view->getId() . "' as id;";

        }
        else
        {
            $sql = "INSERT INTO app_object_views(
                  name,
                  description,
                  team_id,
                  user_id,
                  object_type_id,
                  f_default,
                  owner_id,
                  conditions_data,
                  order_by_data,
                  table_columns_data
                ) values (
                  '" . $dbh->escape($data['name']) . "',
                  '" . $dbh->escape($data['description']) . "',
                  " . $dbh->escapeNumber($data['team_id']) . ",
                  " . $dbh->escapeNumber($data['user_id']) . ",
                  " . $dbh->escapeNumber($def->getId()) . ",
                  '" . (($data['default']) ? 't' : 'f') . "',
                  " . $dbh->escapeNumber($data['user_id']) . ",
                  '" . $dbh->escape(json_encode($data['conditions'])) . "',
                  '" . $dbh->escape(json_encode($data['order_by'])) . "',
                  '" . $dbh->escape(json_encode($data['table_columns'])) . "'
                ); select currval('app_object_views_id_seq') as id;";
        }

        $result = $dbh->query($sql);
        if ($dbh->getNumRows($result))
        {
            $view->setId($dbh->getValue($result, 0, "id"));
            return $view->getId();
        }
        else
        {
            throw new \RuntimeException("Could not save view:" . $dbh->getLastError());
        }
    }

    /**
     * This will do a one-time load of all the views from the database and cache
     *
     * @param string $objType The object type to load
     * @throws \RuntimeException if it cannot load the entity definition
     */
    private function loadViewsFromDb($objType)
    {
        $dbh = $this->dbh;

        // First clear out cache
        $this->views = array();

        $def = $this->definitionLoader->get($objType);

        if (!$def)
            throw new \RuntimeException("Could not get entity definition for $objType");

        // Initialize the cache
        if (!isset($this->views[$objType]))
            $this->views[$objType] = array();

        // Now get all views from the DB
        $sql = "SELECT
                    id, name, scope, description, filter_key,
                    user_id, object_type_id, f_default, team_id,
                    owner_id, conditions_data, order_by_data, table_columns_data
                FROM app_object_views WHERE object_type_id='" . $def->getId() . "'";
        $result = $dbh->Query($sql);
        $num = $dbh->getNumRows($result);
        for ($i = 0; $i < $num; $i++)
        {
            $row = $dbh->getRow($result, $i);

            $viewData = array(
                'id' => $row['id'],
                'obj_type' => $objType,
                'name' => $row['name'],
                'user_id' => $row['user_id'],
                'team_id' => $row['team_id'],
                'default' => ($row['f_default'] === 't') ? true : false,
                'system' => false,
                'conditions' => json_decode($row['conditions_data'], true),
                'order_by' => json_decode($row['order_by_data'], true),
                'table_columns' => json_decode($row['table_columns_data'], true),
            );

            $view = new BrowserView();
            $view->fromArray($viewData);
            $this->views[$objType][] = $view;
        }
    }
}