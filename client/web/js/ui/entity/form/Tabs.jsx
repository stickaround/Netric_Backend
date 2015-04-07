/**
 * Tabs parent component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Tabs = require("../../tabs/Tabs.jsx");

/**
 * Top level element for tabs
 */
var FormTabs = React.createClass({

    render: function() {
        return (
        	<Tabs>
        		{this.props.children}
        	</Tabs>
        );
    }
});

module.exports = FormTabs;