/**
 * AppBar used for browse mode
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var AppBarSearch = require("./AppBarSearch.jsx");
var AppBarSelect = require("./AppBarSelect.jsx");
var actionModes = require("../../entity/actions/actionModes");
var Chamel = require('chamel');
var AppBar = Chamel.AppBar;
var IconButton = Chamel.IconButton;

/**
 * Module shell
 */
var AppBarBrowse = React.createClass({

    propTypes: {
        title : React.PropTypes.string,
        // Navigation button action - hamburger to the left of the title
        onNavBtnClick: React.PropTypes.func,
        // Navigation back button - left arrow to the eft of the title
        onNavBackBtnClick: React.PropTypes.func,
        onSearchChange: React.PropTypes.func,
        onPerformAction: React.PropTypes.func,
        onSelectAll: React.PropTypes.func,
        deviceSize: React.PropTypes.number,
        selectedEntities: React.PropTypes.array,
        actionHandler: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
    },

    /**
     * Set initial state for the browser
     */
    getInitialState: function() {
        return { searchMode: false };
    },

    render: function() {

        var elementRight = null;
        var elemmentLeft = null;
        var title = this.props.title;

        if (this.props.selectedEntities && this.props.selectedEntities.length) {

            // Create exit button for select mode
            elemmentLeft = (
                <IconButton
                    iconClassName="fa fa-arrow-left"
                    onClick={this.deSelectAll_} />
            );

            // Create app bar for selected elements
            var actions = this.props.actionHandler.getActions(actionModes.BROWSE, this.props.selectedEntities);
            elementRight = <AppBarSelect onPerformAction={this.props.onPerformAction} actions={actions} />;

            title = this.props.selectedEntities.length + "";

        } else if (this.state.searchMode) {

            // Create exit search mode button
            elemmentLeft = (
                <IconButton
                    iconClassName="fa fa-arrow-left"
                    onClick={this.toggleSearchMode} />
            );

            // Create AppBar with search form
            elementRight = <AppBarSearch onSearch={this.handleSearchChange_} eventsObj={this.props.eventsObj} />;

            // Clear the title
            title = null;

        } else {

            if (this.props.onNavBackBtnClick) {
                elemmentLeft = (
                    <IconButton
                        iconClassName="fa fa-arrow-left"
                        onClick={this.handleBackClick_} />
                );
            }

            // Show default AppBar with nothing selected and no search
            elementRight = (
                <div>
                    <IconButton
                        iconClassName="fa fa-search"
                        onClick={this.toggleSearchMode}>
                    </IconButton>
                </div>
            );

        }

        return (
            <AppBar 
                iconElementLeft={elemmentLeft}
                title={title} 
                onNavBtnClick={this.props.onNavBtnClick}>
                {elementRight}
            </AppBar>
            
        );
    },

    /**
     *  Turn search mode on or off
     *
     * @param evt
     */
    toggleSearchMode: function(evt) {

        // Clear any text
        if (this.state.searchMode) {
            this.handleSearchChange_("");
        }

        this.setState({searchMode: (this.state.searchMode) ? false : true});
    },

    /**
     * Handle getting search params
     */
    handleSearchChange_: function(textSearch) {
        if (this.props.onSearchChange) {
            this.props.onSearchChange(textSearch, null);
        }
    },

    /** 
     * Deselect all
     */
    deSelectAll_: function(evt) {
        if (this.props.onSelectAll) {
            this.props.onSelectAll(false);
        }
    },

    /**
     * The user clicked back in the toolbar/appbar
     *
     * @param {DOMEvent} evt
     * @private
     */
    handleBackClick_: function(evt) {
        if (this.props.onNavBackBtnClick) {
            this.props.onNavBackBtnClick();
        }
    }

});

module.exports = AppBarBrowse;
