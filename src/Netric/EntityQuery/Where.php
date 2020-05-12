<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Netric\EntityQuery;

/**
 * Description of Where
 *
 * @author Sky Stebnicki
 */
class Where
{
    /**
     * Conjuncture operator for this where after preceeding
     */
    const COMBINED_BY_AND = 'and';
    const COMBINED_BY_OR = "or";

    /**
     * Combiner logic
     *
     * @var string
     */
    public $bLogic = self::COMBINED_BY_AND;

    /**
     * The field name
     *
     * If the field name is "*" then conduct a full text query
     *
     * @var string
     */
    public $fieldName = "";

    /**
     * The operator to use with this condition
     *
     * @var string
     */
    public $operator = "";

    /**
     * The value to query against
     *
     * @var string
     */
    public $value = "";

    /**
     * Define operators to comparing values
     */
    const OPERATOR_EQUAL_TO                  = 'is_equal';
    const OP_EQ                              = 'is_equal';
    const OPERATOR_NOT_EQUAL_TO              = 'is_not_equal';
    const OP_NE                              = 'is_not_equal';
    const OPERATOR_LESS_THAN                 = 'is_less';
    const OP_LT                              = 'is_less';
    const OPERATOR_LESS_THAN_OR_EQUAL_TO     = 'is_less_or_equal';
    const OP_LTE                             = 'is_less_or_equal';
    const OPERATOR_GREATER_THAN              = 'is_greater';
    const OP_GT                              = 'is_greater';
    const OPERATOR_GREATER_THAN_OR_EQUAL_TO  = 'is_greater_or_equal';
    const OP_GTE                             = 'is_greater_or_equal';
    const OPERATOR_CONTAINS                  = 'contains';
    const OPERATOR_BEGINS                    = 'begins';
    const OPERATOR_BEGINS_WITH               = 'begins_with';
    const OPERATOR_DAY_IS_EQUAL              = 'day_is_equal';
    const OPERATOR_MONTH_IS_EQUAL            = 'month_is_equal';
    const OPERATOR_YEAR_IS_EQUAL             = 'year_is_equal';
    const OPERATOR_LAST_X_DAYS               = 'last_x_days';
    const OPERATOR_LAST_X_WEEKS              = 'last_x_weeks';
    const OPERATOR_LAST_X_MONTHS             = 'last_x_months';
    const OPERATOR_LAST_X_YEARS              = 'last_x_years';
    const OPERATOR_NEXT_X_DAYS               = 'next_x_days';
    const OPERATOR_NEXT_X_WEEKS              = 'next_x_weeks';
    const OPERATOR_NEXT_X_MONTHS             = 'next_x_months';
    const OPERATOR_NEXT_X_YEARS              = 'next_x_years';



    /**
     * Create a where condition
     *
     * @param string $fieldName
     *
     * @return Where
     */
    public function __construct($fieldName = "*")
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    /**
     * Return an array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            "blogic" => $this->bLogic,
            "field_name" => $this->fieldName,
            "operator" => $this->operator,
            "value" => $this->value,
        );
    }

    /**
     * Load where condition from an associative array
     *
     * @param array $data The associative array to load
     */
    public function fromArray($data)
    {
        if (isset($data['blogic'])) {
            $this->bLogic =  $data['blogic'];
        }

        if (isset($data['field_name'])) {
            $this->fieldName =  $data['field_name'];
        }

        if (isset($data['operator'])) {
            $this->operator =  $data['operator'];
        }

        if (isset($data['value'])) {
            $this->value =  $data['value'];
        }
    }

    /**
     * Set condition to match where field equals value
     *
     * @param string $value
     */
    public function equals($value)
    {
        $this->operator = self::OPERATOR_EQUAL_TO;
        $this->value = $value;
    }

    /**
     * Set condition to match where field does not equal value
     *
     * @param string $value
     */
    public function doesNotEqual($value)
    {
        $this->operator = self::OPERATOR_NOT_EQUAL_TO;
        $this->value = $value;
    }

    /**
     * Check if terms are included in a string - full text
     *
     * @param string $value
     */
    public function contains($value)
    {
        $this->operator = self::OPERATOR_CONTAINS;
        $this->value = $value;
    }

    /**
     * Check if the value in the column/field is greater than the condition value
     *
     * @param string $value
     */
    public function isGreaterThan($value)
    {
        $this->operator = self::OPERATOR_GREATER_THAN;
        $this->value = $value;
    }

    /**
     * Check if the value in the column/field is greater than the condition value
     *
     * @param string $value
     */
    public function isGreaterOrEqualTo($value)
    {
        $this->operator = self::OPERATOR_GREATER_THAN_OR_EQUAL_TO;
        $this->value = $value;
    }

    /**
     * Check if the value in the column/field is less than the condition value
     *
     * @param string $value
     */
    public function isLessThan($value)
    {
        $this->operator = self::OPERATOR_LESS_THAN;
        $this->value = $value;
    }

    /**
     * Check if the value in the column/field is less than the condition value
     *
     * @param string $value
     */
    public function isLessOrEqualTo($value)
    {
        $this->operator = self::OPERATOR_LESS_THAN_OR_EQUAL_TO;
        $this->value = $value;
    }

    /**
     * Check if text/string begins with a string
     *
     * @param string $value
     */
    public function beginsWith($value)
    {
        $this->operator = self::OPERATOR_BEGINS;
        $this->value = $value;
    }

    /**
     * Check if a date in a date/time field matches a given day
     *
     * @param string $value
     */
    public function dayIsEqual($value)
    {
        $this->operator = self::OPERATOR_DAY_IS_EQUAL;
        $this->value = $value;
    }

    /**
     * Check if a date in a date/time field matches a given month
     *
     * @param string $value
     */
    public function monthIsEqual($value)
    {
        $this->operator = self::OPERATOR_MONTH_IS_EQUAL;
        $this->value = $value;
    }

    /**
     * Check if a date in a date/time field matches a given year
     *
     * @param string $value
     */
    public function yearIsEqual($value)
    {
        $this->operator = self::OPERATOR_YEAR_IS_EQUAL;
        $this->value = $value;
    }

    /**
     * Check to see if a date is within the last x number of days
     *
     * @param string $value
     */
    public function lastNumDays($value)
    {
        $this->operator = self::OPERATOR_LAST_X_DAYS;
        $this->value = $value;
    }

    /**
     * Check to see if a date is within the last x number of weeks
     *
     * @param string $value
     */
    public function lastNumWeeks($value)
    {
        $this->operator = self::OPERATOR_LAST_X_WEEKS;
        $this->value = $value;
    }

    /**
     * Check to see if a date is within the last x number of months
     *
     * @param string $value
     */
    public function lastNumMonths($value)
    {
        $this->operator = self::OPERATOR_LAST_X_MONTHS;
        $this->value = $value;
    }

    /**
     * Check to see if a date is within the last x number of years
     *
     * @param string $value
     */
    public function lastNumYears($value)
    {
        $this->operator = self::OPERATOR_LAST_X_YEARS;
        $this->value = $value;
    }

    /**
     * Check to see if a date is within the next x number of days
     *
     * @param string $value
     */
    public function nextNumDays($value)
    {
        $this->operator = self::OPERATOR_NEXT_X_DAYS;
        $this->value = $value;
    }

    /**
     * Check to see if a date is within the next x number of weeks
     *
     * @param string $value
     */
    public function nextNumWeeks($value)
    {
        $this->operator = self::OPERATOR_NEXT_X_WEEKS;
        $this->value = $value;
    }

    /**
     * Check to see if a date is within the next x number of months
     *
     * @param string $value
     */
    public function nextNumMonths($value)
    {
        $this->operator = self::OPERATOR_NEXT_X_MONTHS;
        $this->value = $value;
    }

    /**
     * Check to see if a date is within the next x number of years
     *
     * @param string $value
     */
    public function nextNumYears($value)
    {
        $this->operator = self::OPERATOR_NEXT_X_YEARS;
        $this->value = $value;
    }

    /**
     * Fulltext type query
     *
     * @param string $value
     */
    public function fullText($value)
    {
        $this->operator = self::OPERATOR_CONTAINS;
        $this->value = $value;
    }

    /**
     * Function that will get the date type of the operator
     * @return string
     */
    public function getOperatorDateType()
    {
        switch ($this->operator) {
            
            case self::OPERATOR_DAY_IS_EQUAL:
            case self::OPERATOR_LAST_X_DAYS:
            case self::OPERATOR_NEXT_X_DAYS:
                return "day";
                break;
            case self::OPERATOR_LAST_X_WEEKS:
            case self::OPERATOR_NEXT_X_WEEKS:
                return "week";
                break;
            case self::OPERATOR_MONTH_IS_EQUAL:
            case self::OPERATOR_LAST_X_MONTHS:
            case self::OPERATOR_NEXT_X_MONTHS:
                return "month";
                break;
            case self::OPERATOR_YEAR_IS_EQUAL:
            case self::OPERATOR_LAST_X_YEARS:
            case self::OPERATOR_NEXT_X_YEARS:
                return "year";
                break;
            default:
                break;
        }
    }

    /**
     * Get a hash for this where condition
     *
     * @return string Unique has for this where condition
     */
    public function getHash()
    {
        $signature = md5 ( json_encode($this->toArray()) );

        // Keep it short, it should be unique enough
        if (strlen($signature) > 8) {
            $signature = substr($signature, 0, 8);
        }

        return $this->fieldName . $signature;
    }
}
