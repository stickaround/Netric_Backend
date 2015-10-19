/**
 * Entity Plugin
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

var Plugin = React.createClass({

    render: function() {

        var xmlNode = this.props.xmlNode;
        return (
            <div>Plugin</div>
        );
    }

});

module.exports = Plugin;