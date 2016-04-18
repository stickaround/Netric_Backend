/**
 * A row
 *

 */
'use strict';

var React = require('react');

/**
 * Header element
 */
var Header = React.createClass({

    render: function() {

        let elementNode = this.props.elementNode;
        let text = elementNode.getText();
        let className = elementNode.getAttribute("class");

        let headerElement = null;

        return (
            <h5>
                {text}
            </h5>
        );
    }

});

module.exports = Header;