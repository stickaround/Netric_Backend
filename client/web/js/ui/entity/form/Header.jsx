/**
 * A row
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Header element
 */
var Header = React.createClass({

    render: function() {

        let xmlNode = this.props.xmlNode;
        let text = xmlNode.childNodes[0].nodeValue;
        let className = xmlNode.getAttribute("class");

        let headerElement = null;

        return (
            <h5>
                {text}
            </h5>
        );
    }

});

module.exports = Header;