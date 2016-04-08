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

        var elementNode = this.props.elementNode;
        var type = elementNode.getAttribute('type');
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