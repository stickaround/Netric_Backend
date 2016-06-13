/**
 * AppBar used for browse mode
 *
 */
'use strict';

var React = require('react');
var AppBarSearch = require("./AppBarSearch.jsx");
var AppBarSelect = require("./AppBarSelect.jsx");
var actionModes = require("../../entity/actions/actionModes");
var Controls = require('../Controls.jsx');
var AppBar = Controls.AppBar;
var IconButton = Controls.IconButton;
var DropDownIcon = Controls.DropDownIcon;


/**
 * Module shell
 */
var AppBarBrowse = React.createClass({

    propTypes: {
        title: React.PropTypes.string,
        // Navigation button action - hamburger to the left of the title
        onNavBtnClick: React.PropTypes.func,
        // Navigation back button - left arrow to the eft of the title
        onNavBackBtnClick: React.PropTypes.func,
        onSearchChange: React.PropTypes.func,
        onAdvancedSearch: React.PropTypes.func,
        onPerformAction: React.PropTypes.func,
        onSelectAll: React.PropTypes.func,
        deviceSize: React.PropTypes.number,
        selectedEntities: React.PropTypes.array,
        actionHandler: React.PropTypes.object,
        entityBrowserViews: React.PropTypes.array,
        onApplySearch: React.PropTypes.func
    },

    /**
     * Set initial state for the browser
     */
    getInitialState: function () {
        return {searchMode: false};
    },

    render: function () {

        var elementRight = null;
        var elemmentLeft = null;
        var title = this.props.title;

        if (this.props.selectedEntities && this.props.selectedEntities.length) {

            // Create exit button for select mode
            elemmentLeft = (
                <IconButton
                    key="back"
                    iconClassName="fa fa-arrow-left"
                    onClick={this._deSelectAll}/>
            );

            // Create app bar for selected elements
            var actions = this.props.actionHandler.getActions(actionModes.BROWSE, this.props.selectedEntities);
            elementRight = <AppBarSelect onPerformAction={this.props.onPerformAction} actions={actions}/>;

            title = this.props.selectedEntities.length + "";

        } else if (this.state.searchMode) {

            // Create exit search mode button
            elemmentLeft = (
                <IconButton
                    key="searchLeft"
                    iconClassName="fa fa-arrow-left"
                    onClick={this.toggleSearchMode}/>
            );

            // Create AppBar with search form
            elementRight = (
                <AppBarSearch
                    key="appBarSearch"
                    onSearch={this._handleSearchChange}/>
            );

            // Clear the title
            title = null;

        } else {
            if (this.props.onNavBackBtnClick) {
                elemmentLeft = (
                    <IconButton
                        key="back"
                        iconClassName="fa fa-arrow-left"
                        onClick={this._handleBackClick}/>
                );
            }


            let displayFilter = null;

            if (this.props.entityBrowserViews) {

                let viewMenuData = [];

                this.props.entityBrowserViews.map(function (view) {
                    viewMenuData.push({
                        payload: view.id,
                        text: view.name
                    })
                })

                displayFilter = (
                    <DropDownIcon
                        iconClassName="fa fa-filter"
                        menuItems={viewMenuData}
                        onChange={this._handleSelectView}/>
                );
            }

            // Show default AppBar with nothing selected and no search
            elementRight = (
                <div>
                    <IconButton
                        key="searchRight"
                        iconClassName="fa fa-search"
                        onClick={this.toggleSearchMode}/>
                    <IconButton
                        iconClassName="fa fa-ellipsis-v "
                        onClick={this._handleAdvancedSearch}/>
                    {displayFilter}
                </div>
            );

        }

        return (
            <AppBar
                fixed={true}
                key="appBarBrowse"
                iconElementLeft={elemmentLeft}
                title={title}
                zDepth={0}
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
    toggleSearchMode: function (evt) {

        // Clear any text
        if (this.state.searchMode) {
            this._handleSearchChange("");
        }

        this.setState({searchMode: (this.state.searchMode) ? false : true});
    },

    /**
     * Handle getting search params
     */
    _handleSearchChange: function (textSearch) {
        if (this.props.onSearchChange) {
            this.props.onSearchChange(textSearch, null);
        }
    },

    /**
     * Deselect all
     */
    _deSelectAll: function (evt) {
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
    _handleBackClick: function (evt) {
        if (this.props.onNavBackBtnClick) {
            this.props.onNavBackBtnClick();
        }
    },

    /**
     * Callback used to handle commands when user clicks the button to display the menu in the popover
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @private
     */
    _handlePopoverDisplay: function (e) {

        // This prevents ghost click.
        e.preventDefault();

        this.setState({
            openMenu: this.state.openMenu ? false : true,
            anchorEl: e.currentTarget
        });
    },

    /**
     * Callback used to close the popover
     *
     * @private
     */
    _handlePopoverRequestClose: function () {
        this.setState({openMenu: false});
    },

    /**
     * Callback used to handle commands when user selects a browser view to filter the browser list
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {Object} data The object value of the menu clicked
     * @private
     */
    _handleSelectView: function (e, key, data) {

        // Get the browserView selected by using the key
        let browserView = this.props.entityBrowserViews[key];

        if (this.props.onApplySearch) {
            this.props.onApplySearch(browserView);
        }
    },

    /**
     * Displays the advanced search
     */
    _handleAdvancedSearch: function () {
        if (this.props.onAdvancedSearch) {
            this.props.onAdvancedSearch()
        }
    }
});

module.exports = AppBarBrowse;
