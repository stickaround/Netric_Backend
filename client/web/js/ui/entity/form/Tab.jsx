/**
 * Tab UIML element
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var Tab = Chamel.Tab;

/**
 * Tab element
 */
var FormTab = React.createClass({

    render: function() {

        var xmlNode = this.props.xmlNode;
        var label = xmlNode.getAttribute('name');

        if (this.props.renderChildren) {
        	return (
        		<div>
	                {this.props.children}
	            </div>
		    );
        } else {
        	return (
	            <Tab {...this.props} label={label}>
	                {this.props.children}
	            </Tab>
	        );
        }
        
    }
});

module.exports = FormTab;