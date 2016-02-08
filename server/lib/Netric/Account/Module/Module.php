<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module;

/**
 * Class represents a module (used to be called application) in netric
 *
 * Modules are like sub-applications within each account. They pretty much represent
 * a separate loadable applet within the netric main application. Default applications
 * are added to every account on initialization.
 */
class Module
{
    /**
     * Unique id if saved
     *
     * @var int
     */
    private $id = null;

    /**
     * Name of the module - must be unique
     *
     * @var string
     */
    private $name = "";

    /**
     * Human readable full title of the module
     *
     * @var string
     */
    private $title = "";

    /**
     * Short menu-friendly title
     *
     * @var string
     */
    private $shortTitle = "";

    /**
     * The scope indicating who sees the module
     *
     * @var string
     */
    private $scope = self::SCOPE_EVERYONE;
    const SCOPE_EVERYONE = "system";
    const SCOPE_USER = "user";
    const SCOPE_TEAM = "team";
    const SCOPE_NOBODY = "draft";

    /**
     * Flag to indicate if this is a system module or user-generated
     *
     * @var bool
     */
    private $system = true;

    /**
     * If scope is user, then a userId must be specified
     *
     * @var int
     */
    private $userId = null;

    /**
     * If scope is for a team, then teamId must be specified
     *
     * @var int
     */
    private $teamId = null;

    /**
     * The order in which the module should be displayed in the list
     *
     * In the future this will be overridden by usage stats - how often a user
     * launches a module - but for now it reigns as the primary sort field.
     *
     * @var int
     */
    private $sortOder = 0;

    /**
     * Get the id of this module
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id of this module
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the unique name of this module
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the unique name of this module
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get full human readable title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set full human readable title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Return a short, menu-friendly title
     *
     * @return string
     */
    public function getShortTitle()
    {
        return $this->shortTitle;
    }

    /**
     * Set a short, menu-friendly title
     *
     * @param string $title
     */
    public function setShortTitle($title)
    {
        $this->shortTitle = $title;
    }

    /**
     * Get the scope
     *
     * @return string self::SCOPE_*
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set the publish scope
     *
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * Check if the module is a system module or user created
     *
     * @return bool
     */
    public function isSystem()
    {
        return $this->system;
    }

    /**
     * Set whether or not this is a system module
     *
     * @param bool $isSystem
     */
    public function setSystem($isSystem = true)
    {
        $this->system = $isSystem;
    }

    /**
     * Get the user id, used if the scope is self::SCOPE_USER
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the user id, used if the scope is self::SCOPE_USER
     *
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;;
    }

    /**
     * Get the team id, used if the scope is self::SCOPE_TEAM
     *
     * @return int
     */
    public function getTeamId()
    {
        return $this->teamId;
    }

    /**
     * Set the team id, used if the scope is self::SCOPE_TEAM
     *
     * @param int $teamId
     */
    public function setTeamId($teamId)
    {
        $this->teamId = $teamId;
    }

    /**
     * Get the sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOder;
    }

    /**
     * Set the sort order
     *
     * @param int $sortOrder
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOder = $sortOrder;
    }

    /**
     * Import properties from an associative array
     *
     * @param array $data Associative array describing the module
     */
    public function fromArray(array $data)
    {
        if (isset($data['id']))
            $this->id = $data['id'];

        if (isset($data['name']))
            $this->name = $data['name'];

        if (isset($data['title']))
            $this->title = $data['title'];

        if (isset($data['short_title']))
            $this->shortTitle = $data['short_title'];

        if (isset($data['scope']))
            $this->scope = $data['scope'];

        if (isset($data['system']))
            $this->system = $data['system'];

        if (isset($data['user_id']))
            $this->userId = $data['user_id'];

        if (isset($data['team_id']))
            $this->teamId = $data['team_id'];

        if (isset($data['sort_order']))
            $this->sortOder = $data['sort_order'];
    }

    /**
     * Export properties as an array
     *
     * @return array Associative array of module properties
     */
    public function toArray()
    {
        return array(
            "id" => $this->id,
            "name" => $this->name,
            "title" => $this->title,
            "short_title" => $this->shortTitle,
            "scope" => $this->scope,
            "system" => $this->system,
            "user_id" => $this->userId,
            "team_id" => $this->teamId,
            "sort_order" => $this->sortOder,
        );
    }
}