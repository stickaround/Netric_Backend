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
            viewMenuData: null,
            browser: null
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
     * Callback used to handle the changing of filter dropdown
     *
     * @param {DOMEvent} e          Reference to the DOM event being sent
     * @param {int} key             The index of the menu clicked
     * @param {array} menuItem      The object value of the menu clicked
     * @private
     */
    _handleFilterChange: function (e, key, menuItem) {
        this._loadActivities(menuItem.conditions)
    },

    /**
     * Load the activityController to display the activities for this entity
     *
     * @param {entity.Where[]} conditions      These are the conditions that will limit/filter the activities
     * @private
     */
    _loadActivities: function (conditions) {
        var inlineCon = ReactDOM.findDOMNode(this.refs.activityContainer);
        var BrowserController = require('../../../controller/EntityBrowserController');
        var browser = this.state.activityBrowser;

        // Add filter to only show activities from the referenced object
        var referenceFilter = new Where("obj_reference");
        referenceFilter.equalTo(this.props.entity.objType + ":" + this.props.entity.id);

        // If conditions is not set, then we create a blank conditions array
        if (!conditions) {
            conditions = [];
        }

        // Set the reference filter in the conditions
        conditions.push(referenceFilter);

        // Check if entity browser is not yet set
        if (!browser) {
            browser = new BrowserController();

            // Lets create a callback function to set the activity view dropdown once entity browser has been loaded
            var callbackFunc = function () {

                // We dont need to get activity views if it is already set.
                if(!this.state.viewMenuData) {
                    var activityViews = browser.getEntityDefinition().getViews();
                    this._setViewMenuData(activityViews)
                }
            }

            // Load the entity browser
            browser.load({
                type: controller.types.FRAGMENT,
                title: "Activity",
                objType: "activity",
                hideToolbar: true,
                filters: conditions
            }, inlineCon, null, callbackFunc.bind(this));

            this.setState({activityBrowser: browser});
        } else {

            // If entity browser is already set, then lets just update the filters and refresh the results
            browser.updateFilters(conditions);
        }
    },

    /**
     * Set the view menu data in the state
     *
     * @param {array} activityViews     The activity view data from entity definition
     * @private
     */
    _setViewMenuData: function (activityViews) {
        var viewMenu = [];

        for (var idx in activityViews) {
            var view = activityViews[idx];

            viewMenu.push({
                text: view.name,
                conditions: view.getConditions()
            });
        }

        this.setState({viewMenuData: viewMenu});
    }
});

module.exports = Activity;
