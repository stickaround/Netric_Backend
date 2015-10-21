<?php
/**
 * Manage entity forms
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity;

use Netric\Db\DbInterface;
use Netric\Entity\ObjType\User;
use Netric\EntityDefinition;
use Netric\Config;


/**
 * Class for managing entity forms
 *
 * @package Netric\Entity
 */
class Forms
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
     * Service creation factory
     *
     * @param \Netric\EntityDefinition $def
     * @return array Associative array
     */
    public function getDeviceForms(EntityDefinition $def, User $user)
    {
        $dbh = $this->dbh;

        /*
         * We are translating the new form names 'small|medium|large|xlarge'
         * to the old 'mobile|default' names for the time being
         * because these scopes are accessed all throughout the
         * old code base. Once we replace the entire UI then it should be
         * pretty easy to remove all old references to mobile/default
         * and then just do an SQL update to rename exsiting custom forms.
         */

        $default = $this->getFormUiXml($def, $user, "default");
        $small = $this->getFormUiXml($def, $user, "mobile");
        if (!$small)
            $small = $default;
        $medium = $this->getFormUiXml($def, $user, "mobile");
        if (!$medium)
            $medium = $default;

        $forms = array(
            'small' => $small,
            'medium' => $medium,
            'large' => $default,
            'xlarge' => $default,
            'infobox' => $this->getFormUiXml($def, $user, "infobox"),
        );

        return $forms;
    }

    /**
     * Get a UIXML form for an entity type but check for user/team customizations
     *
     * @param \Netric\EntityDefinition $def
     * @param \Netric\Entity\ObjType\User $user User to get forms for
     * @param string $scope The device scope / size
     * @return string
     */
    public function getFormUiXml(EntityDefinition $def, User $user, $scope)
    {
        $dbh = $this->dbh;

        // Protect against SQL Injection
        $scope = $dbh->escape($scope);

        // Check for user specific form
        $result = $dbh->query("SELECT form_layout_xml FROM app_object_type_frm_layouts
                                WHERE user_id='" . $user->getId() . "'
                                    AND scope='" . $scope . "'
                                    AND type_id='" . $def->getId() . "';");
        if ($dbh->getNumRows($result))
        {
            $val = $dbh->getValue($result, 0, "form_layout_xml");
            if ($val && $val!="*")
                return $val;
        }
        
        // Check for team specific form
        if ($user->getValue("team_id"))
        {
            $result = $dbh->query("SELECT form_layout_xml FROM app_object_type_frm_layouts
                                    WHERE team_id='" . $user->getValue("team_id") . "' 
                                        AND scope='" . $scope . "' 
                                        AND type_id='" . $def->getId() . "';");
            if ($dbh->getNumRows($result))
            {
                $val = $dbh->getValue($result, 0, "form_layout_xml");
                if ($val && $val!="*")
                    return $val;
            }
        }

        // Check for default custom that applies to all users and teams
        $result = $dbh->query("SELECT form_layout_xml FROM app_object_type_frm_layouts
                                WHERE scope=''" . $scope . "'  
                                AND team_id is null AND user_id is null
                                AND type_id='" . $def->getId() . "';");
        if ($dbh->getNumRows($result))
        {
            $val = $dbh->getValue($result, 0, "form_layout_xml");
            if ($val && $val!="*")
                return $val;
        }

        // Get system default
        return $this->getSysForm($def, $scope);
    }

    /**
     * Get system defined UIXML form for an object type
     *
     * @param \Netric\EntityDefinition $def
     * @param string $scope
     * @return string UIXML form if defined
     */
    public function getSysForm($def, $scope)
    {
        $objType = $def->getObjType();
        $xml = "";

        if (!$objType)
            throw new \Exception("Invalid object type");

        // Check for system object
        $basePath = $this->config->application_path . "/objects";
        $formPath = $basePath . "/oforms/" . $objType . "/" . $scope . ".php";
        if (file_exists($formPath))
        {
            $xml = file_get_contents($formPath);
        }

        return $xml;
    }

}
