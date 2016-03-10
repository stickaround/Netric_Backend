/**
 * AppBar used for search mode
 *

 */
'use strict';

var React = require('react');
var KeyCodes = require("../utils/KeyCode.jsx");
var Chamel = require('chamel');
var IconButton = Chamel.IconButton;
var TextField = Chamel.TextField;

/**
 * Module shell
 */
var AppBarBrowse = React.createClass({

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
                        onKeyDown={this.handleKeyUp_}/>
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

    /**
     * Handles the key press of search text input
     *
     * @param {DOMEvent} evt      Reference to the DOM event being sent
     * @private
     */
    handleKeyUp_: function(evt) {

        if (!this.props.onSearch) {
            return;
        }

        if (evt.keyCode == KeyCodes.ENTER) {
            this.props.onSearch(this.refs.searchInput.getValue());
        }
    },

    /**
     * Executes the search functionality
     */
    handleDoSearch_: function() {
        this.props.onSearch(this.refs.searchInput.getValue());
    },
    
    /**
     * Displays the advanced search
     */
    handleAdvancedSearch_: function() {
        if(this.props.onAdvancedSearch) this.props.onAdvancedSearch();
    }
});

module.exports = AppBarBrowse;
