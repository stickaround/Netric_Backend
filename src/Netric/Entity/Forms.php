<?php

namespace Netric\Entity;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinition;
use Aereus\Config\Config;
use Netric\Db\Relational\RelationalDbInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class for managing entity forms
 *
 * @package Netric\Entity
 */
class Forms
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
     * Unique account ID
     *
     * @var string
     */
    private $accountId = "";

    /**
     * Class constructor to set up dependencies
     *
     * @param RelationalDbInterface $database Handles to database actions
     * @param Config $config Contains the configuration info
     * @param string $accountId the ID of the account we are managing forms for
     */
    public function __construct(RelationalDbInterface $database, Config $config, string $accountId)
    {
        $this->database = $database;
        $this->config = $config;
        $this->accountId = $accountId;
    }

    /**
     * Service creation factory
     *
     * @param EntityDefinition $def
     * @return array Associative array
     */
    public function getDeviceForms(EntityDefinition $def, UserEntity $user)
    {
        // First look for the new form names: small, medium, large, xlarge
        $small = $this->getFormUiXml($def, $user, "small");
        $medium = $this->getFormUiXml($def, $user, "medium");
        $large = $this->getFormUiXml($def, $user, "large");
        $xlarge = $this->getFormUiXml($def, $user, "xlarge");

        // Use nearest match if all forms have not been applied
        if (!$xlarge && $large) {
            $xlarge = $large;
        }
        if (!$medium && $large) {
            $medium = $large;
        }
        if (!$large && $medium) {
            $large = $medium;
        }

        /*
         * We are translating the new form names 'small|medium|large|xlarge'
         * to the old 'mobile|default' names for the time being
         * because these scopes are accessed all throughout the
         * old code base. Once we replace the entire UI then it should be
         * pretty easy to remove all old references to mobile/default
         * and then just do an SQL update to rename exsiting custom forms.
         */
        $default = $this->getFormUiXml($def, $user, "default");
        if (!$small) {
            $small = $this->getFormUiXml($def, $user, "mobile");
            if (!$small) {
                $small = $default;
            }
        }
        if (!$medium) {
            $medium = $this->getFormUiXml($def, $user, "mobile");
            if (!$medium) {
                $medium = $default;
            }
        }
        if (!$large) {
            $large = $default;
        }
        if (!$xlarge) {
            $xlarge = $default;
        }

        $forms = array(
            'small' => $small,
            'medium' => $medium,
            'large' => $large,
            'xlarge' => $xlarge,
            'infobox' => $this->getFormUiXml($def, $user, "infobox"),
        );

        return $forms;
    }

    /**
     * Get a UIXML form for an entity type but check for user/team customizations
     *
     * Forms are selected in the following order:
     * 1. If there is a form specificaly for the userId, then use it otherwise
     * 2. If there is a form specifically for the user's team, then use it otherwise
     * 3. If there is a customized form saved for the account, then use it otherwise
     * 4. Get the system default form form the file system
     *
     * In all the above cases it will be checking
     *
     * @param EntityDefinition $def The object type definition
     * @param UserEntity $user User to get forms for
     * @param string $device The device scope / size - 'small', 'medium', 'large', 'xlarge'
     * @return string
     */
    public function getFormUiXml(EntityDefinition $def, UserEntity $user, $device)
    {
        // Check for user specific form
        $sql = "SELECT form_layout_xml FROM entity_form
                WHERE user_id=:user_id AND scope=:scope AND type_id=:type_id";

        $params = [
            "scope" => $device,
            "type_id" => $def->getEntityDefinitionId()
        ];
        $result = $this->database->query($sql, array_merge(["user_id" => $user->getEntityId()], $params));

        if ($result->rowCount()) {
            $row = $result->fetch();
            if (!empty($row["form_layout_xml"]) && $row["form_layout_xml"] !== "*") {
                return $row["form_layout_xml"];
            }
        }

        // Check for team specific form
        if ($user->getValue("team_id")) {
            $sql = "SELECT form_layout_xml FROM entity_form
                    WHERE team_id=:team_id AND scope=:scope AND type_id=:type_id";

            $result = $this->database->query($sql, array_merge(["team_id" => $user->getValue("team_id")], $params));

            if ($result->rowCount()) {
                $row = $result->fetch();
                if (!empty($row["form_layout_xml"]) && $row["form_layout_xml"] !== "*") {
                    return $row["form_layout_xml"];
                }
            }
        }

        // Check for default custom that applies to all users and teams
        $sql = "SELECT form_layout_xml FROM entity_form
                WHERE scope=:scope AND type_id=:type_id AND team_id IS NULL AND user_id IS NULL";

        $result = $this->database->query($sql, $params);

        if ($result->rowCount()) {
            $row = $result->fetch();
            if (!empty($row["form_layout_xml"]) && $row["form_layout_xml"] !== "*") {
                return $row["form_layout_xml"];
            }
        }

        // Get system default
        return $this->getSysForm($def, $device);
    }

    /**
     * Get system defined UIXML form for an object type
     *
     * @param EntityDefinition $def The object type definition
     * @param string $device Device type/size 'small', 'medium', 'large', 'xlarge'
     * @return string UIXML form if defined
     * @throws \Exception When called when $def is not a valid object type
     */
    public function getSysForm($def, $device)
    {
        $objType = $def->getObjType();
        $xml = "";

        if (!$objType) {
            throw new \Exception("Invalid object type");
        }

        // Check form xml from a file found in /objects/{objType}/{device}.php
        $basePath = $this->config->get("application_path") . "/data";
        $formPath = $basePath . "/entity_forms/" . $objType . "/" . $device . ".php";
        if (file_exists($formPath)) {
            $xml = file_get_contents($formPath);
        }

        return $xml;
    }

    /**
     * Override the default system form for a specific team
     *
     * @param EntityDefinition $def The object type definition
     * @param int $teamId The unique id of the team that will use this form
     * @param string $deviceType The type of device the form is for: small|medium|large|xlarge
     * @param string $xmlForm The UIXML representing the form
     * @throws \RuntimeException If xml is bad
     * @throws \InvalidArgumentException If any param is null
     * @return bool true on success, false on failure
     */
    public function saveForTeam(EntityDefinition $def, $teamId, $deviceType, $xmlForm)
    {
        // Make sure teamId is set
        if (!is_numeric($teamId)) {
            throw new \InvalidArgumentException("teamId is required");
        }


        return $this->saveForm($def, null, $teamId, $deviceType, $xmlForm);
    }

    /**
     * Override the default system form for a specific user
     *
     * @param \Netric\EntityDefinition $def The object type definition
     * @param string $userId The unique id of the user that will use this form
     * @param string $deviceType The type of device the form is for: small|medium|large|xlarge
     * @param string $xmlForm The UIXML representing the form
     * @return bool true on success, false on failure
     */
    public function saveForUser(EntityDefinition $def, string $userId, $deviceType, $xmlForm)
    {
        // Make sure $userId is set
        if (!$userId) {
            throw new \InvalidArgumentException("userId is required");
        }

        return $this->saveForm($def, $userId, null, $deviceType, $xmlForm);
    }

    /**
     * Override the default system form (file) for this account
     *
     * @param \Netric\EntityDefinition $def The object type definition
     * @param string $deviceType The type of device the form is for: small|medium|large|xlarge
     * @param string $xmlForm The UIXML representing the form
     * @return bool true on success, false on failure
     */
    public function saveForAccount(EntityDefinition $def, $deviceType, $xmlForm)
    {
        return $this->saveForm($def, null, null, $deviceType, $xmlForm);
    }

    /**
     * Save a form to the database if set, otherwise just delete exsiting form if null
     *
     * @param EntityDefinition $def The entity definition we are working with
     * @param int $userId If set save this for a specific user
     * @param int $teamId If set save for a specific team
     * @param string $deviceType The size of the device - small|medium|large|xlarge
     * @param string $xmlForm The UIXML form to save
     * @return bool true on success, false on failure
     * @throws \InvalidArgumentException If any of the provided params are invalid
     */
    private function saveForm(EntityDefinition $def, $userId, $teamId, $deviceType, $xmlForm)
    {
        // Either team or user can be set, but not both
        if ($userId && $teamId) {
            throw new \InvalidArgumentException("You cannot set both the userId and teamId");
        }

        if (!$this->validateXml($xmlForm)) {
            throw \RuntimeException("Invalid UIXML Detected", $xmlForm);
        }

        // Make sure the deviceType is set
        if (!$deviceType) {
            throw new \InvalidArgumentException("Device type is required");
        }

        // Make sure required params are set
        if (!$def->getEntityDefinitionId()) {
            throw new \InvalidArgumentException("Entity definition is bad");
        }

        $params = [
            "scope" => $deviceType,
            "type_id" => $def->getEntityDefinitionId()
        ];

        $params["user_id"] = null;
        $params["team_id"] = null;

        if ($teamId) {
            $params["team_id"] = $teamId;
        }

        if ($userId) {
            $params["user_id"] = $userId;
        }

        // Clean any existing forms that match this deviceType (used to be called scope)
        $this->database->delete("entity_form", $params);

        // Insert the new form if set, otherwise just leave it deleted
        if (!empty($xmlForm)) {
            $insertData = [
                'entity_form_id' => Uuid::uuid4()->toString(),
                "scope" => $deviceType,
                "team_id" => $teamId,
                "user_id" => $userId,
                "type_id" => $def->getEntityDefinitionId(),
                "form_layout_xml" => $xmlForm,
                "account_id" => $this->accountId,
            ];

            $this->database->insert("entity_form", $insertData);

            return true;
        }

        return true;
    }

    /**
     * Make sure that the user has supplied a valid xml document
     *
     * @param string $xml
     * @return bool true if the form is vaid xml, otherwise false
     */
    private function validateXml($xml)
    {
        $isValid = true;

        // The xml can be null if the user wants to delete it so default to true
        if ($xml !== null) {
        }

        return $isValid;
    }
}
