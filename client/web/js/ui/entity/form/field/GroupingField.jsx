/**
 * Text field compnent
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Base level element for enetity forms
 */
var GroupingField = React.createClass({

    render: function() {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        return (
            <div>
                Grouping: {fieldName}
            </div>
        );
    }
});

module.exports = GroupingField;