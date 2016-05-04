/**
 * Render an entity browser
 *

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
        onRemoveEntity: React.PropTypes.func,
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
        hideToolbar: React.PropTypes.bool,

        /**
         * Type of toolbar to be displayed.
         *
         * @type {string} appbar | toolbar
         */
        toolbarMode: React.PropTypes.oneOf(['appbar', 'toolbar']),

        /**
         * The total number of entities
         *
         * @var {integer}
         */
        entitiesTotalNum: React.PropTypes.number,

        /**
         * If true do not show any text when no entities are found
         *
         * @type {bool}
         */
        hideNoItemsMessage: React.PropTypes.bool,

        entityBrowserViews: React.PropTypes.array,
        onApplySearch: React.PropTypes.func
    },

    getDefaultProps: function () {
        return {
            toolbarMode: 'appbar',
            layout: '',
            title: "Browser",
            entities: [],
            selectedEntities: [],
            collectionLoading: false,
            hideNoItemsMessage: false,
            onRemoveEntity: null
        }
    },

    render: function () {

        var bodyContent = null;

        if (this.props.entities.length == 0 && this.props.collectionLoading) {
            bodyContent = <Loading />;
        } else if (this.props.entities.length == 0 && !this.props.hideNoItemsMessage) {
            bodyContent = <div className="entity-browser-blank">No items found.</div>;
        } else {
            bodyContent = (
                <List
                    onEntityListClick={this.props.onEntityListClick}
                    onEntityListSelect={this.props.onEntityListSelect}
                    onLoadMoreEntities={this.props.onLoadMoreEntities}
                    onRemoveEntity={this.props.onRemoveEntity}
                    onCreateNewEntity={this._handleCreateNewEntity}
                    entities={this.props.entities}
                    selectedEntities={this.props.selectedEntities}
                    browserView={this.props.browserView}
                    layout={this.props.layout}
                    collectionLoading={this.props.collectionLoading}
                    filters={this.props.filters}
                    entitiesTotalNum={this.props.entitiesTotalNum}
                />
            );

            if (this.props.collectionLoading) {
                // TODO: display loading indicator over the list
            }
        }

        var toolbar = null;
        if (!this.props.hideToolbar) {
            if (this.props.toolbarMode == 'appbar') {
                toolbar = (
                    <AppBarBrowse
                        key="appbarEntityBrowser"
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
                        entityBrowserViews={this.props.entityBrowserViews}
                        onApplySearch={this.props.onApplySearch}
                    />
                );
            } else {
                toolbar = (
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
                    {toolbar}
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
     * @param {Object} opt_data Optional params to send to the new entity form
     */
    _handleCreateNewEntity: function (opt_data) {
        let data = opt_data || {};
        if (this.props.onCreateNewEntity) {
            this.props.onCreateNewEntity(data);
        }
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
