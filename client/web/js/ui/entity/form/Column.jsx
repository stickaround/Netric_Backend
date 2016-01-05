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

    render: function () {

        var xmlNode = this.props.xmlNode;
        var type = xmlNode.getAttribute('type');
        var className = null;

        switch (type) {
            case 'sidebar':
            case 'half':
            case 'quarter':
                className = 'entity-form-column-' + type;
                break;

            default:
                className = 'entity-form-column';
                break;
        }

        return (
            <div className={className}>
                {this.props.children}
            </div>
        );
    }

});

module.exports = Column;