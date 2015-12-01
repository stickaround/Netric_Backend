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

/**
 * Handle rendering activity browser inline
 */
var EntityActivity = React.createClass({

    propTypes: {
        // Get the objReference - the object for which we are displaying/adding comments
        objReference: React.PropTypes.string,
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
            <div ref="activityContainer" className="entity-activity">
            </div>
        );
    },

    /**
     * Load browser inline (FRAGMENT) with objType='activity'
     *
     * @private
     */
    _loadActivityBrowser: function () {

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var BrowserController = require("../controller/EntityBrowserController");
        var browser = new BrowserController();

        // Add filter to only show activities from the referenced object
        var filterWhere = new Where("obj_reference");
        filterWhere.equalTo(this.props.objReference);

        browser.load({
            type: controller.types.FRAGMENT,
            title: "Activity",
            objType: "activity",
            hideToolbar: true,
            filters: [filterWhere]
        }, ReactDOM.findDOMNode(this.refs.activityContainer));

        this.setState({activityBrowser: browser});
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
