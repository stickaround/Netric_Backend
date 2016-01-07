/**
 * Render an entity browser
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var List = require("./entitybrowser/List.jsx");
var AppBarBrowse = require("./entitybrowser/AppBarBrowse.jsx");
var Loading = require("./Loading.jsx");
var Chamel = require('chamel');
var Toolbar = Chamel.Toolbar;
var ToolbarGroup = Chamel.ToolbarGroup;
var FontIcon = Chamel.FontIcon;
var IconButton = Chamel.IconButton;

/**
 * Module shell
 */
var EntityBrowser = React.createClass({

    propTypes: {
        onEntityListClick: React.PropTypes.func,
        onEntityListSelect: React.PropTypes.func,
        onPerformAction: React.PropTypes.func,
        // Navigation button action - hamburger to the left of the title
        onNavBtnClick: React.PropTypes.func,
        // Navigation back button - left arrow to the left of the title
        onNavBackBtnClick: React.PropTypes.func,
        onCreateNewEntity: React.PropTypes.func,
        onRefreshEntityList: React.PropTypes.func,
        layout: React.PropTypes.string,
        title: React.PropTypes.string,
        actionHandler: React.PropTypes.object,
        entities: React.PropTypes.array,
        deviceSize: React.PropTypes.number,
        selectedEntities: React.PropTypes.array,
        browserView: React.PropTypes.object,
        collectionLoading: React.PropTypes.bool,
        hideAppBar: React.PropTypes.bool,

        /**
         * Type of toolbar to be displayed.
         *
         * @type {string} appbar | toolbar
         */
        toolbarMode: React.PropTypes.string
    },

    getDefaultProps: function () {
        return {
            toolbarMode: 'appbar',
            layout: '',
            title: "Browser",
            entities: [],
            selectedEntities: [],
            collectionLoading: false
        }
    },

    render: function () {

        var bodyContent = null;

        if (this.props.entities.length == 0 && this.props.collectionLoading) {
            bodyContent = <Loading />;
        } else if (this.props.entities.length == 0) {
            bodyContent = <div className="entity-browser-blank">No items found.</div>;
        } else {
            bodyContent = (<List
                onEntityListClick={this.props.onEntityListClick}
                onEntityListSelect={this.props.onEntityListSelect}
                onLoadMoreEntities={this.props.onLoadMoreEntities}
                entities={this.props.entities}
                selectedEntities={this.props.selectedEntities}
                browserView={this.props.browserView}
                layout={this.props.layout}
                collectionLoading={this.props.collectionLoading}
                filters={this.props.filters}/>);

            if (this.props.collectionLoading) {
                // TODO: display loading indicator over the list
            }
        }

        var appBar = null;
        if (!this.props.hideAppBar) {
            if (this.props.toolbarMode == 'appbar') {
                appBar = (
                    <AppBarBrowse
                        title={this.props.title}
                        actionHandler={this.props.actionHandler}
                        deviceSize={this.props.deviceSize}
                        onNavBtnClick={this.props.onNavBtnClick}
                        onNavBackBtnClick={this.props.onNavBackBtnClick}
                        onSearchChange={this.props.onSearchChange}
                        onAdvancedSearch={this.props.onAdvancedSearch}
                        onPerformAction={this.props.onPerformAction}
                        onSelectAll={this.handleSeelctAll_}
                        selectedEntities={this.props.selectedEntities}
                        objType={this.props.objType}
                        />
                );
            } else {
                appBar = (
                    <Toolbar>
                        <ToolbarGroup key={1} float="left">
                            <FontIcon
                                tooltip='New'
                                className="fa fa-plus-circle"
                                onClick={this._handleCreateNewEntity}/>
                            <FontIcon
                                tooltip='Refresh'
                                className="fa fa-refresh"
                                onClick={this._handleRefreshEntityList}/>
                        </ToolbarGroup>
                    </Toolbar>
                );
            }
        }

        return (
            <div>
                <div>
                    {appBar}
                </div>
                <div ref="moduleMain">
                    {bodyContent}
                </div>
            </div>
        );
    },

    /**
     * Select/Deselect all
     */
    handleSeelctAll_: function (selected) {
        if (this.props.onEntityListSelect) {
            this.props.onEntityListSelect(selected);
        }
    },

    /**
     * Handles the clicking of create new entity
     *
     * @private
     */
    _handleCreateNewEntity: function () {
        if (this.props.onCreateNewEntity) this.props.onCreateNewEntity();
    },

    /**
     * Handles the clicking of refresh entity list
     *
     * @private
     */
    _handleRefreshEntityList: function () {
        if (this.props.onRefreshEntityList) this.props.onRefreshEntityList();
    }
});

module.exports = EntityBrowser;