/**
 * Main controller view for activites for entity
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var controller = require("../controller/controller");
var Where = require("../entity/Where");
var Chamel = require('chamel');
var AppBar = Chamel.AppBar;
var DropDownMenu = Chamel.DropDownMenu;

/**
 * Handle rendering activity browser inline
 */
var EntityActivity = React.createClass({

    propTypes: {
        // Get the objReference - the object for which we are displaying/adding comments
        objReference: React.PropTypes.string,
        viewMenu: React.PropTypes.array
    },

    componentDidMount: function () {
        this._loadActivityBrowser();
    },

    getInitialState: function () {
        return {
            activityBrowser: null
        };
    },

    render: function () {
        return (
            <div>
                <DropDownMenu
                    menuItems={this.props.viewMenu}
                    selectedIndex={0}
                    onChange={this._handleFilterChange}/>

                <div ref="activityContainer" className="entity-activity">
                </div>
            </div>
        );
    },

    /**
     * Load browser inline (FRAGMENT) with objType='activity'
     *
     * @private
     */
    _loadActivityBrowser: function (conditions) {

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var BrowserController = require("../controller/EntityBrowserController");
        var browser = this.state.activityBrowser;

        // Add filter to only show activities from the referenced object
        var referenceFilter = new Where("obj_reference");
        referenceFilter.equalTo(this.props.objReference);

        // If conditions is not set, then we create a blank conditions array
        if (!conditions) {
            conditions = [];
        }

        // Set the reference filter in the conditions
        conditions.push(referenceFilter);

        if (!browser) {
            browser = new BrowserController();

            browser.load({
                type: controller.types.FRAGMENT,
                title: "Activity",
                objType: "activity",
                hideToolbar: true,
                filters: conditions
            }, ReactDOM.findDOMNode(this.refs.activityContainer));

            this.setState({activityBrowser: browser});
        } else {
            browser.updateFilters(conditions);
        }
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
        this._loadActivityBrowser(menuItem.conditions)
    },

    /**
     * Refresh the activity entity browser
     *
     * @public
     */
    refreshActivity: function () {
        if (this.state.activityBrowser) {
            this.state.activityBrowser.refresh();
        }
    }
});

module.exports = EntityActivity;
