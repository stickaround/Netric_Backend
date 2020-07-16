<?php

/**
 * Manage entity browser views
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\BrowserView;

use Netric\Entity\ObjType\UserEntity;
use Aereus\Config\Config;
use Netric\Settings\Settings;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\EntityGroupings\GroupingLoader;
use Ramsey\Uuid\Uuid;

/**
 * Class for managing entity forms
 *
 * @package Netric\Entity\Entity
 */
class BrowserViewService
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    private $database = null;

    /**
     * Netric configuration
     *
     * @var Config
     */
    private $config = null;

    /**
     * A cache of all loaded BrowserViews from the DB
     *
     * Each object type will be cached in $this->views[$objType]
     *
     * @var array
     */
    private $views = [];

    /**
     * Entity defition loader to map type id to type name
     *
     * @var EntityDefinitionLoader
     */
    private $definitionLoader = null;

    /**
     * Account or user level settings service
     *
     * @var Settings|null
     */
    private $settings = null;

    /**
     * GroupingLoader to get the groupings data to sanitize the condition values
     *
     * @var GroupingLoader
     */
    private $groupingLoader = null;

    /**
     * Class constructor to set up dependencies
     *
     * @param RelationalDbInterface $rdb
     * @param Config $config The configuration object
     * @param EntityDefinitionLoader $defLoader To get definitions of entities by $objType
     * @param Settings $settings Account or user settings service
     * @param GroupingLoader $groupingLoader To get the groupings data to sanitize the condition values
     */
    public function __construct(RelationalDbInterface $rdb, Config $config, EntityDefinitionLoader $defLoader, Settings $settings, GroupingLoader $groupingLoader)
    {
        $this->database = $rdb;
        $this->config = $config;
        $this->definitionLoader = $defLoader;
        $this->settings = $settings;
        $this->groupingLoader = $groupingLoader;
    }

    /**
     * Get the user's default view for the given object type
     *
     * @param string $objType The object type
     * @param UserEntity $user The user we are getting the default for
     * @return string User's default view for the given object type
     */
    public function getDefaultViewForUser(string $objType, UserEntity $user)
    {
        $settingKey = "entity/browser-view/default/$objType";

        // First check to see if they set their own default
        $defaultViewId = $this->settings->getForUser($user, $settingKey);

        // TODO: Check the user's team

        // Check to see if there is an account default
        if (!$defaultViewId) {
            $defaultViewId = $this->settings->get($settingKey);
        }

        // Now load the system default
        if (!$defaultViewId) {
            $sysViews = $this->getSystemViews($objType);
            foreach ($sysViews as $view) {
                if ($view->isDefault()) {
                    $defaultViewId = $view->getId();
                }
            }

            // If none were marked as default, then just grab the first one
            if (!$defaultViewId && count($sysViews)) {
                $defaultViewId = $sysViews[0]->getId();
            }
        }

        return $defaultViewId;
    }

    /**
     * Get the user's default view for the given object type
     *
     * @param string $objType The object type
     * @param UserEntity $user The user we are getting the default for
     * @param string $defaultViewId The id of the view that we will be setting as default view
     * @return string User's default view for the given object type
     */
    public function setDefaultViewForUser(string $objType, UserEntity $user, string $defaultViewId)
    {
        $settingKey = "entity/browser-view/default/$objType";

        // Set the default view for this specific user
        $this->settings->setForUser($user, $settingKey, $defaultViewId);
    }

    /**
     * Get browser views for a user
     *
     * Here is how views will be loaded:
     *  1. First get system (file) views
     *  2. Then add account views
     *  3. Then add team views if user is a member
     *  4. Then add user specific views for the user
     * @param string $objType The objType where we will be getting the views
     * @param UserEntity $user The user that will be used to filter the views
     * @return array of BrowserView(s) for the user
     */
    public function getViewsForUser(string $objType, UserEntity $user)
    {
        // If we have not loaded views from the database then do that now
        if (!isset($this->views[$objType])) {
            $this->loadViewsFromDb($objType);
        }

        $systemViews = $this->getSystemViews($objType);

        // Add account views
        $accountViews = $this->getAccountViews($objType);

        // Add team views if a user is a member of teams
        $teamViews = [];
        if (!empty($user->getValue("team_id"))) {
            $teamViews = $this->getTeamViews($objType, $user->getValue("team_id"));
        }

        // Add user specific views
        $userViews = $this->getUserViews($objType, $user->getEntityId());

        $mergedViews = array_merge($systemViews, $accountViews, $teamViews, $userViews);
        return $mergedViews;
    }

    /**
     * Get a browser view by id
     *
     * @param string $objType The object type for this view
     * @param string $viewId The unique id of the view
     * @return BrowserView
     */
    public function getViewById(string $objType, string $viewId)
    {
        // If we have not loaded views from the database then do that now
        if (!isset($this->views[$objType])) {
            $this->loadViewsFromDb($objType);
        }

        foreach ($this->views[$objType] as $view) {
            if ($view->getId() == $viewId) {
                return $view;
            }
        }

        return null;
    }

    /**
     * Get team views that are saved to the database
     *
     * @param string $objType The object type to get browser views for
     * @param string $userGuid The unique global id of the user to get views for
     * @return BrowserView[]
     */
    public function getUserViews(string $objType, string $userGuid)
    {
        // If we have not loaded views from the database then do that now
        if (!isset($this->views[$objType])) {
            $this->loadViewsFromDb($objType);
        }

        // Return all views that are set for a specific team
        $ret = [];
        foreach ($this->views[$objType] as $view) {
            if ($view->getOwnerId() == $userGuid) {
                $ret[] = $view;
            }
        }
        return $ret;
    }

    /**
     * Get team views that are saved to the database for teams only
     *
     * @param string $objType The object type to get browser views for
     * @param string $teamId The team id which we will get the views
     * @return BrowserView[]
     */
    public function getTeamViews(string $objType, string $teamId)
    {
        // If we have not loaded views from the database then do that now
        if (!isset($this->views[$objType])) {
            $this->loadViewsFromDb($objType);
        }

        // Return all views that are set for a specific team
        $ret = [];
        foreach ($this->views[$objType] as $view) {
            if ($view->getTeamId() && $view->getTeamId() == $teamId) {
                $ret[] = $view;
            }
        }
        return $ret;
    }

    /**
     * Get account views that are saved to the database for everyone
     *
     * @param string $objType The object type to get browser views for
     * @return BrowserView[]
     */
    public function getAccountViews(string $objType)
    {
        // If we have not loaded views from the database then do that now
        if (!isset($this->views[$objType])) {
            $this->loadViewsFromDb($objType);
        }

        // Return all views that are not user or team views
        $ret = [];
        foreach ($this->views[$objType] as $view) {
            if (empty($view->getTeamId()) && empty($view->getOwnerId())) {
                $ret[] = $view;
            }
        }
        return $ret;
    }

    /**
     * Get system/default views from config files
     *
     * @param string $objType The object type to get browser views for
     * @return BrowserView[]
     */
    public function getSystemViews(string $objType)
    {
        if (!$objType) {
            return false;
        }

        $views = [];

        // Check for system object
        $basePath = $this->config->get("application_path") . "/data";
        if (file_exists($basePath . "/browser_views/$objType.php")) {
            $viewsData = include($basePath . "/browser_views/$objType.php");

            // Initialize all the views from the returned array
            foreach ($viewsData as $key => $systemViewData) {
                // System level views must only have a name for the key because it is used for the id
                if (is_numeric($key)) {
                    throw new \RuntimeException(
                        "BrowserViews must be defined with associative and unique keyname " .
                            "but " . $basePath . "/browser_views/$objType.php does not follow that rule"
                    );
                }

                $view = new BrowserView();
                $view->fromArray($systemViewData);
                $view->setId($key); // For saving the default in user settings
                $view->setSystem(true);

                // Traverse through the view's conditions and convert the grouping name to id
                $this->convertGroupingNameToID($view);
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
        $def = $this->definitionLoader->get($view->getObjType());

        if (!$def) {
            throw new \RuntimeException("Could not get entity definition for: " . $view->getObjType());
        }

        $viewId = $view->getId();
        $data = $view->toArray();
        $saveViewData = [
            "name" => $data['name'],
            "description" => $data['description'],
            "team_id" => ($data['team_id']) ? $data['team_id'] : null,
            "object_type_id" => $def->getEntityDefinitionId(),
            "f_default" => $data['default'],
            "owner_id" => ($data['owner_id']) ? $data['owner_id'] : null,
            "group_first_order_by" => $data['group_first_order_by'],
            "conditions_data" => json_encode($data['conditions']),
            "order_by_data" => json_encode($data['order_by']),
            "table_columns_data" => json_encode($data['table_columns'])
        ];

        if ($viewId && is_numeric($viewId)) {
            $this->database->update("app_object_views", $saveViewData, ['id' => $viewId]);
        } else {
            $viewId = $this->database->insert("app_object_views", $saveViewData, 'id');
            $view->setId($viewId);
        }

        $this->addViewToCache($view);
        return $viewId;
    }

    /**
     * Delete a BrowserView
     *
     * @param BrowserView $view The view to delete
     * @return bool true on success, false on failure
     * @throws \RuntimeException if it cannot run the command on the backend database
     */
    public function deleteView(BrowserView $view)
    {
        if (!$view->getId()) {
            return false;
        }

        $result = $this->database->delete(
            "app_object_views",
            ['id' => $view->getId()]
        );

        if ($result) {
            // Remove the view from the local views cache
            $this->removeViewFromLocalCache($view->getObjType(), $view->getId());

            // Clear the ID since it is not saved anymore
            $view->setId(null);

            return true;
        }

        return false;
    }

    /**
     * Clear the views cache
     */
    public function clearViewsCache()
    {
        $this->views = [];
    }

    /**
     * Add the view to cache
     * @param BrowserView $view The view that we will be adding to the cache
     */
    private function addViewToCache(BrowserView $view)
    {
        $found = false;

        if (!isset($this->views[$view->getObjType()])) {
            $this->views[$view->getObjType()] = [];
        }

        // Make sure we do not add this view again
        foreach ($this->views[$view->getObjType()] as $cachedView) {
            if ($cachedView->getId() == $view->getId()) {
                $found = true;
            }
        }

        if (!$found) {
            $this->views[$view->getObjType()][] = $view;
        }
    }

    /**
     * Remove a view from the local cached array
     *
     * @param string $objType The object type of the view to remove
     * @param string $viewId The unique id of the view to remove
     * @return bool true on success, false on failure
     */
    private function removeViewFromLocalCache(string $objType = null, string $viewId = null)
    {
        if (empty($objType) || empty($viewId)) {
            return false;
        }

        if (!isset($this->views[$objType])) {
            return false;
        }

        // Loop through each cached view for a match and remove it from the array if found
        for ($i = 0; $i < count($this->views[$objType]); $i++) {
            $cachedView = $this->views[$objType][$i];
            if ($cachedView->getId() === $viewId) {
                array_splice($this->views[$objType], $i, 1);

                // Break the for loop now that we have decreased the bounds of the array
                break;
            }
        }

        return true;
    }

    /**
     * This will do a one-time load of all the views from the database and cache
     *
     * @param string $objType The object type to load
     * @throws \RuntimeException if it cannot load the entity definition
     */
    private function loadViewsFromDb($objType)
    {
        // First clear out cache
        $this->views = [];

        $def = $this->definitionLoader->get($objType);

        if (!$def) {
            throw new \RuntimeException("Could not get entity definition for $objType");
        }

        // Initialize the cache
        if (!isset($this->views[$objType])) {
            $this->views[$objType] = [];
        }

        // Now get all views from the DB
        $sql = "SELECT id, name, scope, description, filter_key,
                    object_type_id, f_default, team_id,
                    owner_id, conditions_data, order_by_data, table_columns_data,
                    group_first_order_by
                FROM app_object_views WHERE object_type_id=:object_type_id";

        $result = $this->database->query($sql, ["object_type_id" => $def->getEntityDefinitionId()]);
        foreach ($result->fetchAll() as $row) {
            $viewData = [
                'id' => $row['id'],
                'obj_type' => $objType,
                'name' => $row['name'],
                'description' => $row['description'],
                'owner_id' => $row['owner_id'],
                'group_first_order_by' => $row['group_first_order_by'],
                'team_id' => $row['team_id'],
                'default' => ($row['f_default'] === 't') ? true : false,
                'system' => false,
                'conditions' => json_decode($row['conditions_data'], true),
                'order_by' => json_decode($row['order_by_data'], true),
                'table_columns' => json_decode($row['table_columns_data'], true)
            ];

            $view = new BrowserView();
            $view->fromArray($viewData);
            $this->views[$objType][] = $view;
        }
    }

    /**
     * Function that will convert the grouping name to id
     *
     * @param {BrowserView} $view The browser view that we will sanitize the value of its conditions
     * @throws Netric\EntityGroupings\Exception
     */
    private function convertGroupingNameToID($view)
    {
        $objType = $view->getObjType();
        $def = $this->definitionLoader->get($objType);
        $conditions = $view->getConditions();

        foreach ($conditions as $condition) {
            $fieldName = $condition->fieldName;
            $condValue = $condition->value;
            $field = $def->getField($fieldName);

            // We need to check if we have an invalid uuid value, then we need to sanitize it
            if ($field && $field->isGroupingReference() && !Uuid::isValid($condValue)) {

                // Sanitize the value by loading the grouping data and get the value's guid
                $groupings = $this->groupingLoader->get("$objType/$fieldName");
                $group = $groupings->getByName($condValue);

                // If we found the group by using the $condValue
                if ($group) {

                    //We will update the condition's value with the group id
                    $condition->value = $group->guid;
                }
            }
        }
    }
}
