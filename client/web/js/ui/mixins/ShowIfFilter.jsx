/**
 * Handle the Showing of element by evaluating the ShowIf
 *
 * @jsx React.DOM
 */
'use strict';

/**
 * Handle showing of element
 */
var ShowIfFilter = {

    /**
     * Evaluate the showIf provided
     *
     * @param {string} showIf The filter that we will be evaluating. Sample value: type=1
     */
    evaluateShowIf: function(showIf) {
        var parts = showIf.split("=");
        var refField = parts[0];
        var refValue = parts[1];

        // If refValue has a string value of null, then lets convert it to null value
        if (refValue === "null") {
            refValue = null;
        }

        // If it did not match with the entity field value, then return true
        if (this.props.entity.getValue(refField) == refValue) {
            return true;
        } else {
            return false;
        }
    }

};

module.exports = ShowIfFilter;
