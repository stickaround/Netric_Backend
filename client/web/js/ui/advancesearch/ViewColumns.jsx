/**
 * View column used for advance search
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');

/**
 * Module shell
 */
var ViewColumn = React.createClass({

    propTypes: {
        objType: React.PropTypes.string.isRequired,
    },

    getInitialState: function() {
        return { 
        	
        	};
    },

    componentDidMount: function() {
    },

    render: function() {
    		
        return (
        		<div>
				</div>
        );
    },    
});

module.exports = ViewColumn;
