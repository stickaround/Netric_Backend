/**
 * Flex box column
 *
 */
'use strict';

var React = require('react');
var EntityFormShowFilter = require("../../mixins/EntityFormShowFilter.jsx");

/**
 * Tab element
 */
var Column = React.createClass({

    mixins: [EntityFormShowFilter],

    render: function () {

        var elementNode = this.props.elementNode;
        var type = elementNode.getAttribute('type');
        var className = "entity-form-column";

        if (type) {
            className += "-" + type;
        }

        var displayCol = (
            <div className={className}>
                {this.props.children}
            </div>
        );

        var showif = this.props.elementNode.getAttribute('showif');

        if (showif) {

            // If ::evaluateShowIf() returns false, it means that the showif did not match the filter specified
            if (!this.evaluateShowIf(showif)) {
                displayCol = null;
            }
        }

        return (
            <div className={className}>
                {displayCol}
            </div>
        );
    }

});

module.exports = Column;