<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\EntityDefinition\Field;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Error\ErrorAwareInterface;
use Netric\Error\Error;
use RuntimeException;

/**
 * Base class for all action exectors
 *
 * The primary purpose of this class is to handle merge fields -
 * where the user enters <%field_name%> or similar vars -
 * and a common error interface. The actual execution of the action
 * will be handled in all derived classes.
 */
abstract class AbstractActionExecutor implements ErrorAwareInterface
{
    /**
     * Array of errors for ErrorAwareInterface
     *
     * @var Error[]
     */
    private array $errors = [];

    /**
     * Loader to get entities for param merging
     *
     * @var EntityLoader
     */
    private EntityLoader $entityLoader;

    /**
     * The action entity with all data and settings for this action
     *
     * @var WorkflowActionEntity
     */
    private WorkflowActionEntity $actionEntity;

    /**
     * Used to compose any links
     */
    private string $applicaitonUrl;

    /**
     * This must be called by all derived classes
     *
     * @param EntityLoader $entityLoader
     * @param WorkflowActionEntity $actionEntity
     * @param string $appliactionUrl
     */
    public function __construct(
        EntityLoader $entityLoader,
        WorkflowActionEntity $actionEntity,
        string $applicationUrl
    ) {
        $this->entityLoader = $entityLoader;
        $this->actionEntity = $actionEntity;
        $this->applicaitonUrl = $applicationUrl;
    }

    /**
     * Get a param by name
     *
     * @param string $name The unique name of the pasram to get
     * @param EntityInterface $mergeWithEntity Optional entity to merge variables with
     * @return string
     */
    protected function getParam($name, EntityInterface $mergeWithEntity)
    {
        $params = $this->actionEntity->getData();
        $paramValue = (isset($params[$name])) ? $params[$name] : null;

        // Check if we should merge variables before returning
        if ($mergeWithEntity && $paramValue) {
            $paramValue = $this->replaceParamVariables($mergeWithEntity, $paramValue);
        }

        return $paramValue;
    }

    /**
     * Replace any variables in a value either from a macro like entity_link or the entity value
     *
     * @param EntityInterface $mergeWithEntity The entity we are acting on to get values from
     * @param string $value The precompiled string that can contain <%varname%> merge variables
     * @return string
     * @throws RuntimeException If we end up in an infinite loop for any reason
     */
    protected function replaceParamVariables(EntityInterface $mergeWithEntity, $value)
    {
        // Only check strings
        if (!is_string($value)) {
            return $value;
        }

        // Keep track of iterations to protect against infinite loops
        $iterations = 0;

        // Buffer for matches
        $matches = [];

        while (preg_match("/<%(.*?)%>/", $value, $matches)) {
            $variableName = $matches[1];

            switch ($variableName) {
                case 'entity_link':
                    /*
                     * Create a link to the entity in question
                     */
                    $value = str_replace(
                        "<%$variableName%>",
                        $this->applicaitonUrl . "/browse/" . $mergeWithEntity->getEntityId(),
                        $value
                    );

                    break;

                case 'id':
                    // It is common for 'id' to be used rather than entity_id
                    $fieldValue = $mergeWithEntity->getEntityId();
                    $value = str_replace("<%$variableName%>", $fieldValue, $value);
                    break;

                default:
                    /*
                     * Entity field value
                     */
                    $fieldValue = $this->getParamVariableFieldValue($mergeWithEntity, $variableName);
                    $value = str_replace("<%$variableName%>", $fieldValue, $value);

                    break;
            }

            // Prevent infinite loop
            $iterations++;
            if ($iterations > 5000) {
                throw new RuntimeException("Too many iterations");
            }
        }

        return $value;
    }

    /**
     * Get the actual value of an entity field
     *
     * The field could be cross-entity with dot '.' notation like
     * user.manager.name
     *
     * @param EntityInterface $entity The entity to get the value from
     * @param string $fieldName The name of the field or field chain (see function notes)
     * @return array|string Could either be an array if *_multi field or string
     */
    private function getParamVariableFieldValue(EntityInterface $entity, $fieldName)
    {
        /*
         * Check if this is an associated field name.
         * Variables can call associated entity fields with dot notation like
         * user.manager.name which would load th name of the user's manager.
         */
        if (strpos($fieldName, '.') === false) {
            // Just get the value from the fieldName if we are not referencing another entity
            if ($entity->getValue($fieldName) !== null) {
                return $entity->getValue($fieldName);
            }

            return '';
        }

        /*
         * The variable name will be something like user.name
         * where 'user' is the name of the field in $mergeWithEntity
         * containing the id of the user, and 'name' being the field
         * name of the referenced user.
         */
        $fieldParts = explode(".", $fieldName);

        // Get first element '$fieldName' and shorted $fieldParts
        $fieldName = array_shift($fieldParts);

        // Concat the remainder of the field names minus the first element for traversing
        $fieldNameRemainder = implode(".", $fieldParts);

        // Get the value of the entity from $entity
        $referencedEntityId = $entity->getValue($fieldName);

        // Get the field of the referenced value
        $field = $entity->getDefinition()->getField($fieldName);

        if ($referencedEntityId && $field->type == Field::TYPE_OBJECT) {
            // Load the referenced entity
            $referencedEntity = $this->entityLoader->getEntityById($referencedEntityId, $entity->getAccountId());

            // Recursively call until we are at the last element of the fieldName
            return $this->getParamVariableFieldValue($referencedEntity, $fieldNameRemainder);
        }

        // Return empty by default
        return "";
    }

    /**
     * Get entity loader dependency
     *
     * @return EntityLoader
     */
    protected function getEntityloader(): EntityLoader
    {
        return $this->entityLoader;
    }

    /**
     * Get the entity id for this action (all actions are entities)
     *
     * @return string
     */
    protected function getActionEntityId(): string
    {
        return $this->actionEntity->getEntityId();
    }

    /**
     * Get the account id that this action belongs to
     *
     * @return string
     */
    protected function getActionAccountId(): string
    {
        return $this->actionEntity->getAccountId();
    }

    /**
     * Add an error to the error aware interface
     *
     * @param Error $error
     * @return void
     */
    public function addError(Error $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Get last error if any
     *
     * @return Error|null
     */
    public function getLastError(): ?Error
    {
        return (count($this->errors)) ? $this->errors[count($this->errors) - 1] : null;
    }

    /**
     * Get all errors encountered, if any
     *
     * @return Error[]
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
