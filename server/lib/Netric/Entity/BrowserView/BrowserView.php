<?php
/**
 * A saved (or defined by file) view for an entity browser
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\BrowserView;

use Netric\EntityQuery;


/**
 * Represent a single browser view
 *
 * @package Netric\Entity\BrowserView
 */
class BrowserView
{
    /**
     * User id if this view is owned by an individual user
     *
     * @var int
     */
    private $userId = null;

    /**
     * Set if this view is owned by a team
     *
     * @var int
     */
    private $teamId = null;

    /**
     * Unique id of this view if saved
     *
     * @var string
     */
    private $id = null;

    /**
     * Name describing this view
     *
     * @var string
     */
    private $name = null;

    /**
     * Full description of the view
     *
     * @var string
     */
    private $description = null;

    /**
     * Which fields to display in a table view
     *
     * @var array
     */
    private $tableColumns = array();

    /**
     * TODO: document or remove if we no longer need it
     *
     * @var string
     */
    private $filterKey = null;

    /**
     * True if this is the default view for the given user
     *
     * @var bool
     */
    private $default = false;

    /**
     * This is a system view which cannot be modified or deleted
     *
     * @var bool
     */
    private $system = false;

    /**
     * The fields to display in a row or list detail
     *
     * @var EnityQuery
     */
    private $query = null;

    /**
     * Convert the data for this view to an array
     *
     * @return array
     */
    public function toArray($userid=null)
    {
        $ret = array(
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            //"filter_key" => $this->filterKey,
            "system" => $this->system,
            "default" => $this->default,
            "table_columns" => array(),
        );

        // Add view fields
        foreach ($this->tableColumns as $field)
        {
            $ret['table_columns'][] = $field;
        }

        $ret['query'] = $this->query->toArray();

        return $ret;
    }

    /**
     * Load this view from an associative array
     *
     * @param array $data
     */
    public function fromArray(array $data)
    {
        if (isset($data['id']))
            $this->id = $data['id'];

        if (isset($data['name']))
            $this->name = $data['name'];

        if (isset($data['description']))
            $this->description = $data['description'];

        if (isset($data['system']) && is_bool($data['system']))
            $this->system = $data['system'];

        if (isset($data['default']) && is_bool($data['default']))
            $this->default = $data['default'];

        if (isset($data['table_columns']) && is_array($data['table_columns']))
        {
            foreach ($data['table_columns'] as $colField)
            $this->tableColumns[] = $colField;
        }

        if (isset($data['query']))
        {
            $this->query = new \Netric\EntityQuery($data['obj_type']);
            $this->query->fromArray($data['query']);
        }
    }
}
