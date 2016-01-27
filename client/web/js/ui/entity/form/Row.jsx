/**
 * A row
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ShowIfFilter = require("../../mixins/ShowIfFilter.jsx");

/**
 * Row element
 */
var Row = React.createClass({

    mixins: [ShowIfFilter],

    render: function () {

        var displayRow = (
            <div className="entity-form-row">
                {this.props.children}
            </div>
        );
        var showif = this.props.xmlNode.getAttribute('showif');

        if (showif) {

            // If ::evaluateShowIf() returns false, it means that the showif did not match the filter specified
            if(!this.evaluateShowIf(showif)) {
                displayRow = null;
            }
        }

        return (
            displayRow
        );
    }

});

module.exports = Row;