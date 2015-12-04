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

/**
 * Render Activity into an entity form
 */
var Activity = React.createClass({

    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    render: function () {
        return (
            <div ref='activityContainer'></div>
        );
    },

    componentDidMount: function () {
        this._loadActivities();
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
        browser.load({
            type: controller.types.FRAGMENT,
            title: "Activity",
            objType: "activity",
            hideToolbar: true,
            filters: [referenceFilter]
        }, inlineCon);
    }
});

module.exports = Activity;
