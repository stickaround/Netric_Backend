/**
 * @fileOverview Where condition used for querying entities
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com;
 * 			Copyright (c) 2015 Aereus Corporation. All rights reserved.
 */
alib.declare("netric.entity.Where");

alib.require("netric");
alib.require("netric.entity.Definition");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Represents a collection of entities
 *
 * @constructor
 * @param {string} objType The name of the object type we are collecting
 */
netric.entity.Where = function(fieldName) {

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
     * @type {netric.entity.Where.operator}
     */
    this.operator = netric.entity.Where.boolOperator.AND;

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
netric.entity.Where.boolOperator = {
    AND : "and",
    OR : "or"
}

/**
 * Static order by direction
 *
 * @const
 */
netric.entity.Where.operator = {
    EQUALTO : "=",
    DOESNOTEQUA : "!=",
    LIKE : "like",
    ISGREATERTHAN : ">",
    ISGREATEROREQUALTO : ">=",
    ISLESSTHAN : "<",
    ISLESSOREQUALTO : "<="
}


/**
 * Set condition to match where field equals value
 *
 * @param {string} value The value to check quality against
 */
netric.entity.Where.prototype.equalTo = function(value) {

}

/**
 * Set condition to match where field does not equal mValue
 *
 * @param {string} value The value to check quality against
 */
netric.entity.Where.prototype.doesNotEqual = function(value) {

}

/**
 * Check if terms are included in a string using the '%' wildcard
 *
 * @param {string} value The value to check quality against
 */
netric.entity.Where.prototype.like = function(value) {

}


/**
 * Check if the value in the column/field is greater than the condition value
 *
 * @param {string} value The value to check quality against
 */
netric.entity.Where.prototype.isGreaterThan = function(value) {

}


/**
 * Check if the value in the column/field is greater or euqal to the condition value
 *
 * @param {string} value The value to check quality against
 */
netric.entity.Where.prototype.isGreaterorEqualTo = function(value) {

}

/**
 * Check if the value in the column/field is less than the condition value
 *
 * @param {string} value The value to check quality against
 */
netric.entity.Where.prototype.isLessThan = function(value) {

}

/**
 * Check if the value in the column/field is less or equal to the condition value
 *
 * @param {string} value The value to check quality against
 */
netric.entity.Where.prototype.isLessOrEqaulTo = function(value) {

}