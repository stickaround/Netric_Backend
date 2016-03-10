/**
 * Tabs parent component
 *

 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var Tabs = Chamel.Tabs;

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