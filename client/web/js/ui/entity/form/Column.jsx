/**
 * Flex box column
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Tab element
 */
var Column = React.createClass({

    render: function() {

        var xmlNode = this.props.xmlNode;
        var type = xmlNode.getAttribute('type');
        var className = (type === "sidebar")
            ? "entity-form-column-sidebar" : "entity-form-column";

    	return (
            <div className={className}>
                {this.props.children}
            </div>
        );
    }

});

module.exports = Column;