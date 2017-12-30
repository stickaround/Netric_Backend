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
    private $oPDOStatement = null;

    /**
     * Prepared query params
     *
     * @var string[]
     */
    private $aParams = [];

    /**
     * Instantiate the Statement
     *
     * @param \PDOStatement $oStatement
     * @param array $aParams - the array of key/value pairs
     */
    public function __construct(\PDOStatement $oStatement, array $aParams = [])
    {
        $this->oPDOStatement = $oStatement;
        $this->aParams = $aParams;
    }

    /**
     * Execute the statement
     *
     * @return Result
     */
    public function execute()
    {
        // Binding the parameters will cause the values inside the parameter list to be
        // cast appropriately.
        $this->bindAllParameters();

        if ($this->oPDOStatement->execute()) {
            return new Result($this->oPDOStatement);
        } else {
            return null;
        }
    }

    /**
     * Binds the parameters to ensure that they are cast appropriately
     */
    private function bindAllParameters()
    {
        $bIncrementByOne = false;
        // If this is a simple array of parameters, like: [ 'value1', 'value2' ], then in order
        // to bind correctly based on the '?' placeholder, we need to increment the index so assignment
        // is not invalid.
        if (array_key_exists(0, $this->aParams)) {
            $bIncrementByOne = true;
        }
        // In order to facilitate appropriate type cast checks, we need to bind values 
        // to their appropriate type, otherwise PDO will enclose the values in single quotes;
        // this will break things in SQLite and possibly other RDBs.
        foreach ($this->aParams as $mField => $mValue) {
            // Parameter placeholder values start at 1, so we need to increment the parameter key by one
            if ($bIncrementByOne === true) {
                $mField += 1;
            }
            switch (gettype($mValue)) {
                case "integer":
                    $this->oPDOStatement->bindValue($mField, $mValue, \PDO::PARAM_INT);
                    break;
                case "string":
                case "double":
                    // There is no PDO::PARAM constant for DOUBLE, so it needs
                    // to cast it as a string
                    $this->oPDOStatement->bindValue($mField, $mValue, \PDO::PARAM_STR);
                    break;
                case "boolean":
                    $this->oPDOStatement->bindValue($mField, $mValue, \PDO::PARAM_BOOL);
                    break;
                case "NULL":
                    $this->oPDOStatement->bindValue($mField, $mValue, \PDO::PARAM_NULL);
                    break;
                case "resource":
                case "object":
                    throw new RuntimeException("Unable to validate $mField as queryable parameter.");
                default:
                    $this->oPDOStatement->bindValue($mField, $mValue);
            }
        }
    }
}
