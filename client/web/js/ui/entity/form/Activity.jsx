/**
 * Component that handles rendering Activity of an entity
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var controller = require('../../../controller/controller');
var Where = require("../../../entity/Where");
var netric = require('../../../base');
var Device = require('../../../Device');
var Chamel = require('chamel');
var DropDownMenu = Chamel.DropDownMenu;

/**
 * Render Activity into an entity form
 */
var Activity = React.createClass({

    propTypes: {
        entity: React.PropTypes.object,
    },

    getInitialState: function () {

        // Return the initial state
        return {
            viewMenuData: null
        };
    },

    componentDidMount: function () {
        this._loadActivities();
    },

    render: function () {
        var viewDropdown = null;

        if (this.state.viewMenuData) {
            viewDropdown = (
                <DropDownMenu
                    menuItems={this.state.viewMenuData}
                    selectedIndex={0}
                    onChange={this._handleFilterChange}/>
            );
        }

        return (
            <div>
                {viewDropdown}

                <div ref='activityContainer'></div>
            </div>
        );
    },

    /**
     * Load the activityController to display the activities for this entity
     *
     * @private
     */
    _loadActivities: function () {
        var BrowserController = require('../../../controller/EntityBrowserController');
        var inlineCon = ReactDOM.findDOMNode(this.refs.activityContainer);

        // Add filter to only show activities from the referenced object
        var referenceFilter = new Where("obj_reference");
        referenceFilter.equalTo(this.props.entity.objType + ":" + this.props.entity.id);

        var browser = new BrowserController();

        var callbackFunc = function () {

            // We dont need to get activity views if it is already set.
            if(!this.state.viewMenuData) {
                this._setViewMenuData(browser.getEntityDefinition().getViews())
            }
        }

        browser.load({
            type: controller.types.FRAGMENT,
            title: "Activity",
            objType: "activity",
            hideToolbar: true,
            filters: [referenceFilter]
        }, inlineCon, null, callbackFunc.bind(this));
    },

    /**
     * Set the view menu data in the state
     *
     * @param {array} views     The activity view data from entity definition
     * @private
     */
    _setViewMenuData: function (views) {
        var viewMenu = [];

        for (var idx in views) {
            var view = views[idx];

            viewMenu.push({
                text: view.name,
                conditions: view.getConditions()
            });
        }

        this.setState({viewMenuData: viewMenu});
    }
});

module.exports = Activity;
