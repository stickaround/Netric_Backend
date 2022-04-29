<?php

namespace Netric\Entity;

use Netric\Account\Account;
use Netric\Entity\Entity;
use Netric\EntityQuery\EntityQuery;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Account\AccountContainer;
use RuntimeException;
use DateTime;

/**
 * Entity service used to sanitize entity values
 */
class EntityValueSanitizer
{
    // Date format that will be used to sanitize date value
    const DATE_FORMAT = "Y-m-d h:i:s A e";

    /**
     * The object that we are going to sanitize. This can be set in setEntity() or setQuery()
     *
     * @var Entity | EntityQuery
     */
    private $object = null;

    /**
     * Entity definition loader for getting definitions
     *
     * @var EntityDefinitionLoader
     */
    private $definitionLoader = null;

    /**
     * Grouping loader used to get user groups
     *
     * @var GroupingLoader
     */
    private $groupingLoader = null;

    /**
     * This will be used to load the account using accountId
     *
     * @var AccountContainer $accountContainer
     */
    private $accountContainer = null;

    /**
     * The account tat will be used to get the current user. This can be set in setAccount()
     *
     * @var Account
     */
    private $account = null;

    /**
     * EntityValueSanitizer constructor
     *
     * @param EntityDefinitionLoader $definitionLoader Entity definition loader for getting definitions
     * @param GroupingLoader $groupingLoader Grouping loader used to get user groups
     * @param AccountContainer $accountContainer This will be used to load the account using accountId
     */
    public function __construct(EntityDefinitionLoader $definitionLoader, GroupingLoader $groupingLoader, AccountContainer $accountContainer)
    {
        $this->definitionLoader = $definitionLoader;
        $this->groupingLoader = $groupingLoader;
        $this->accountContainer = $accountContainer;
    }

    /**
     * Sanitize an EntityQuery
     *
     * @param EntityQuery $query The query to sanitize
     * @return array Where[] Array of conditions with sanitized values
     */
    public function sanitizeQuery(EntityQuery $query)
    {
        // Make sure that an EntityQuery is set
        if (!$query instanceof EntityQuery) {
            throw new RuntimeException("The object being sanitized is not an EntityQuery!");
        }

        // Load the account using the accountId set in the query
        $this->account = $this->accountContainer->loadById($query->getAccountId());

        // Make sure that an account is setup for this sanitizer
        if (!$this->account instanceof Account) {
            throw new RuntimeException("Invalid account set for this sanitizer!");
        }

        $entityDef = $this->definitionLoader->get($query->getObjType(), $this->account->getAccountId());

        // Get the query conditions
        $queryConditions = $query->getWheres();
        $sanitizedConditions = [];

        // Loop thru the query conditions and check for fields to sanitize
        foreach ($queryConditions as $condition) {
            $fieldName = $condition->fieldName;
            $value = $condition->value;

            // If full-text, just leave it alone
            if ($fieldName === '*') {
                $sanitizedConditions[] = $condition;
                continue;
            }

            // Get the Field Definition using the field name provided in the $condition
            $field = $entityDef->getField($fieldName);

            switch ($field->type) {
                case Field::TYPE_BOOL:
                    $condition->value = "false";

                    if (in_array($value, [true, 1, "yes", "t"])) {
                        $condition->value = "true";
                    }

                    $sanitizedConditions[] = $condition;
                    break;

                case Field::TYPE_DATE:
                case Field::TYPE_TIMESTAMP:
                    // Convert DateTime to a timestamp
                    if ($value && $value instanceof DateTime) {
                        $condition->value = $value->format(self::DATE_FORMATE);
                    }

                    $sanitizedConditions[] = $condition;
                    break;

                case Field::TYPE_OBJECT:
                case Field::TYPE_OBJECT_MULTI:
                case Field::TYPE_GROUPING:
                case Field::TYPE_GROUPING_MULTI:
                    // Sanitize the UserEntity::USER_CURRENT value
                    if ($field->subtype == ObjectTypes::USER && $value == UserEntity::USER_CURRENT) {
                        // Get the current user
                        $condition->value = $query->getUserId();
                    } else {
                        $this->sanitizeFieldValue($field, $value);
                        $condition->value = $value;
                    }

                    $sanitizedConditions[] = $condition;
                    break;
                default:
                    $sanitizedConditions[] = $condition;
                    break;
            }
        }

        return $sanitizedConditions;
    }

    /**
     * Function that will sanitize the entity values
     *
     * @param Entity $entity The entity to sanitize
     * @return array Sanitized entity values
     */
    public function sanitizeEntity(Entity $entity)
    {
        // Make sure that an Entity is set
        if (!$entity instanceof Entity) {
            throw new RuntimeException("The object being sanitized is not an Entity!");
        }

        // Load the account using the accountId set in the entity
        $this->account = $this->accountContainer->loadById($entity->getAccountId());

        // Make sure that an account is setup for this sanitizer
        if (!$this->account instanceof Account) {
            throw new RuntimeException("Invalid account set for this sanitizer!");
        }

        $fieldData = [];

        // Get the fields from the entity definition
        $fields = $entity->getDefinition()->getFields();

        // Loop through the fields to find the field that can be sanitized based on its field type
        foreach ($fields as $field) {
            $value = $entity->getValue($field->name);

            switch ($field->type) {
                case Field::TYPE_BOOL:
                    if ($value === "t") {
                        $fieldData[$field->name] = true;
                    } elseif ($value === "f") {
                        $fieldData[$field->name] = false;
                    } else {
                        $fieldData[$field->name] = $value;
                    }
                    break;

                case Field::TYPE_DATE:
                case Field::TYPE_TIMESTAMP:
                    if ($value && $value instanceof DateTime) {
                        $fieldData[$field->name] = strtotime($value);
                    } else {
                        $fieldData[$field->name] = $value;
                    }
                    break;

                case Field::TYPE_OBJECT_MULTI:
                case Field::TYPE_GROUPING_MULTI:
                    $valueNames = $entity->getValueNames($field->name);

                    $ret = $this->sanitizeFieldMultiValues($field, $value, $valueNames);
                    $fieldData[$field->name] = $value;
                    $fieldData["{$field->name}_fval"] = $valueNames;
                    break;

                case Field::TYPE_OBJECT:
                case Field::TYPE_GROUPING:
                    $valueName = $entity->getValueName($field->name);

                    $ret = $this->sanitizeFieldValue($field, $value, $valueName);
                    $fieldData[$field->name] = $value;
                    $fieldData["{$field->name}_fval"] = $valueName;
                    break;
                default:
                    break;
            }
        }

        return $fieldData;
    }

    /**
     * Private function that will sanitize the OBJECT_MULTI or TYPE_GROUPING_MULTI fields
     *
     * @param Field $field The field that we are currently sanitizing
     * @return void
     */
    private function sanitizeFieldMultiValues(Field $field, &$multiValue, &$multiValueNames = [])
    {
        // Get the current user
        $currentUser = $this->account->getAuthenticatedUser();

        // If the value is not an array then we do not need to proceed
        if (!is_array($multiValue)) {
            return;
        }

        foreach ($multiValue as $index => $value) {
            switch ($value) {
                case UserEntity::USER_CURRENT:
                    if ($field->subtype == ObjectTypes::USER) {
                        $multiValue[$index] = $currentUser->getEntityId();
                        $multiValueNames[$currentUser->getEntityId()] = $currentUser->getName();

                        // We need to unset the old value name since we just added a new index for the current user
                        unset($multiValueNames[$value]);
                    }
                    break;

                case UserEntity::GROUP_USERS:
                case UserEntity::GROUP_EVERYONE:
                case UserEntity::GROUP_CREATOROWNER:
                case UserEntity::GROUP_ADMINISTRATORS:
                    // Make sure default groups are set correctly
                    $userGroups = $this->groupingLoader->get(ObjectTypes::USER . '/groups', $this->account->getAccountId());

                    // Get the group based on the field value
                    $group = $userGroups->getByName($value);
                    $multiValue[$index] = $group->getGroupId();
                    $multiValueNames[$group->getGroupId()] = $group->getName();

                    // We need to unset the old value name since we just added a new index for the user group
                    unset($multiValueNames[$value]);
                    break;
            }
        }
    }

    /**
     * Private function that will sanitize the OBJECT or GROUPING fields
     *
     * @param Field $field The field that we are currently sanitizing
     * @return void
     */
    private function sanitizeFieldValue(Field $field, &$value, &$valueName = "")
    {
        switch ($value) {
            case UserEntity::USER_CURRENT:
                if ($field->subtype == ObjectTypes::USER) {
                    // Get the current user
                    $currentUser = $this->account->getAuthenticatedUser();

                    $value = $currentUser->getEntityId();
                    $valueName = $currentUser->getName();
                }
                break;

            case UserEntity::GROUP_USERS:
            case UserEntity::GROUP_EVERYONE:
            case UserEntity::GROUP_CREATOROWNER:
            case UserEntity::GROUP_ADMINISTRATORS:
                // Make sure default groups are set correctly
                $userGroups = $this->groupingLoader->get(ObjectTypes::USER . '/groups', $this->account->getAccountId());

                // Get the group based on the field value
                $group = $userGroups->getByName($value);
                $value = $group->getGroupId();
                $valueName = $group->getName();
                break;
        }
    }
}
