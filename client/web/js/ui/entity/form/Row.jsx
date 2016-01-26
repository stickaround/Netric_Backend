/**
 * A row
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Tab element
 */
var Row = React.createClass({

    render: function () {

        var displayRow = (
            <div className="entity-form-row">
                {this.props.children}
            </div>
        );
        var showif = this.props.xmlNode.getAttribute('showif');

        if (showif) {
            var parts = showif.split("=");
            var refField = parts[0];
            var refValue = parts[1];

            if(this.props.entity.getValue(refField) != refValue) {
                displayRow = null;
            }
        }

        return (
            displayRow
        );
    }

});

module.exports = Row;