/**
 * @fileOverview Where condition used for querying entities
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com;
 * 			Copyright (c) 2015 Aereus Corporation. All rights reserved.
 */
'use strict';

/**
 * Represents a filtering condition for a collection of entities
 *
 * @constructor
 * @param {string} fieldName The name of a field we are filtering
 */
var Where = function(fieldName) {

    /**
     * Field name to check
     *
     * If the field name is "*" then conduct a full text query
     *
     * @public
     * @type {string}
     */
    this.fieldName = fieldName;

    /**
     * Operator
     *
     * @public
     * @type {Where.operator}
     */
    this.operator = Where.operators.EQUALTO;

    /**
     * Boolean operator for combining with previous
     *
     * @public
     * @type {Where.boolOperator}
     */
    this.bLogic = Where.boolOperators.AND;

    /**
     * The value to check against
     *
     * @public
     * @type {mixed}
     */
    this.value = null;
}

/**
 * Static order by direction
 *
 * @const
 */
Where.boolOperators = {
    AND : "and",
    OR : "or"
}

/**
 * Static order by direction
 *
 * @const
 */
Where.operators = {
    EQUALTO : "is_equal",
    DOESNOTEQUAL : "is_not_equal",
    LIKE : "begins_with",
    ISGREATERTHAN : "is_greater",
    ISGREATEROREQUALTO : "is_greater_or_equal",
    ISLESSTHAN : "is_less",
    ISLESSOREQUALTO : "is_less_or_equal"
}

/**
 * Set condition to match where field equals value
 *
 * @param {string} value The value to check quality against
 */
Where.prototype.equalTo = function(value) {
    this.operator = Where.operators.EQUALTO;
    this.value = value;
}

/**
 * Set condition to match where field does not equal mValue
 *
 * @param {string} value The value to check quality against
 */
Where.prototype.doesNotEqual = function(value) {

}

/**
 * Check if terms are included in a string using the '%' wildcard
 *
 * @param {string} value The value to check quality against
 */
Where.prototype.like = function(value) {

}


/**
 * Check if the value in the column/field is greater than the condition value
 *
 * @param {string} value The value to check quality against
 */
Where.prototype.isGreaterThan = function(value) {

}


/**
 * Check if the value in the column/field is greater or euqal to the condition value
 *
 * @param {string} value The value to check quality against
 */
Where.prototype.isGreaterorEqualTo = function(value) {

}

/**
 * Check if the value in the column/field is less than the condition value
 *
 * @param {string} value The value to check quality against
 */
Where.prototype.isLessThan = function(value) {

}

/**
 * Check if the value in the column/field is less or equal to the condition value
 *
 * @param {string} value The value to check quality against
 */
Where.prototype.isLessOrEqaulTo = function(value) {

}

module.exports = Where;
