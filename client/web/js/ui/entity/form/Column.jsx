/**
 * Flex box column
 *

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
        var className = "entity-form-column";

        if (type) {
            className += "-" + type;
        }

        return (
            <div className={className}>
                {this.props.children}
            </div>
        );
    }

});

module.exports = Column;