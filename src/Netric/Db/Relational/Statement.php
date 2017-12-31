<?php
namespace Netric\Db\Relational;

/**
 * Wrap the PDO statement
 */
class Statement
{
    /**
     * PDOStatement we are wrapping
     *
     * @var \PDOStatement
     */
    private $pdoStatement = null;

    /**
     * Associative array of param variables for the query
     *
     * @var string[]
     */
    private $params = [];

    /**
     * Instantiate the Statement
     *
     * @param \PDOStatement $pdoStatement
     * @param array $params Array of name/value pairs for the query
     */
    public function __construct(\PDOStatement $pdoStatement, array $params = [])
    {
        $this->pdoStatement = $pdoStatement;
        $this->params = $params;
    }

    /**
     * Execute the statement
     *
     * @return Result
     */
    public function execute()
    {
        // Binding the parameters will cause the values inside the parameter list 
        // to be cast correctly for their type.
        $this->bindAllParameters();

        if ($this->pdoStatement->execute()) {
            return new Result($this->pdoStatement);
        } else {
            return null;
        }
    }

    /**
     * Binds the parameters to ensure that they are cast correctly for their type.
     */
    private function bindAllParameters()
    {
        /*
         * Bind the values to specific types so that the PDO driver knows how to
         * escame them. Otherwise everything will be wrapped into single quites
         * which will break most RDBs. 
         */
        foreach ($this->params as $paramName => $paramValue) {
            // POD requires the param names to be ':paramname' with the ':' prefix
            $bindParamName = ':' . $paramName;
            switch (gettype($paramValue)) {
                case "integer":
                    $this->pdoStatement->bindValue($bindParamName, $paramValue, \PDO::PARAM_INT);
                    break;
                case "string":
                case "double":
                    // There is no PDO::PARAM constant for DOUBLE, just use string
                    $this->pdoStatement->bindValue($bindParamName, $paramValue, \PDO::PARAM_STR);
                    break;
                case "boolean":
                    $this->pdoStatement->bindValue($bindParamName, $paramValue, \PDO::PARAM_BOOL);
                    break;
                case "NULL":
                    $this->pdoStatement->bindValue($bindParamName, $paramValue, \PDO::PARAM_NULL);
                    break;
                case "resource":
                case "object":
                    throw new RuntimeException("Unable to validate $paramName as queryable parameter.");
                default:
                    $this->pdoStatement->bindValue($bindParamName, $paramValue);
            }
        }
    }
}
