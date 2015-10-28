/**
 * AppBar used for search mode
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var KeyCodes = require("../utils/KeyCode.jsx");
var controller = require("../../controller/controller");
var IconButton = Chamel.IconButton;
var TextField = Chamel.TextField;

/**
 * Module shell
 */
var AppBarSearch = React.createClass({
	
	propTypes: {
		onSearch: React.PropTypes.func,
		onAdvancedSearch: React.PropTypes.func,
		title : React.PropTypes.string,
	},
	
	getDefaultProps: function() {
		return {
			title: "",
			onSearch: null
		}
	},
	
	componentDidMount: function() {
		this.refs.searchInput.focus();
	},
	
	render: function() {
		
		return (
				<div>
				<div className="app-bar-input-search-box">
				<TextField
				hintText="Search" 
					ref='searchInput' 
						onKeyDown={this.handleKeyUp_} />
				
				<IconButton
				iconClassName="fa fa-search-plus"
					onClick={this.handleAdvancedSearch_}>
				</IconButton>
				</div>
				
				<IconButton
				iconClassName="fa fa-search"
				onClick={this.handleDoSearch_}>
				</IconButton>
				</div>
		);
	},
	
	handleKeyUp_: function(evt) {
		
		if (!this.props.onSearch) {
			return;
		}
		
		if (evt.keyCode == KeyCodes.ENTER) {
			this.props.onSearch(this.refs.searchInput.getValue());
		}
	},
	
	handleDoSearch_: function() {
		this.props.onSearch(this.refs.searchInput.getValue());
	},
	
	handleAdvancedSearch_: function() {
	    if(this.props.onAdvancedSearch) this.props.onAdvancedSearch();
	}
});

module.exports = AppBarSearch;
var Chamel = require('chamel');
