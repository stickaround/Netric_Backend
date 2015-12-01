/**
 * Component that handles rendering Activity of an entity
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var EntityActivityController = require('../../../controller/EntityActivityController');
var controller = require('../../../controller/controller');
var netric = require('../../../base');
var Device = require('../../../Device');

/**
 * Render Activitys into an entity form
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

        var inlineCon = ReactDOM.findDOMNode(this.refs.activityContainer);

        var activity = new EntityActivityController();
        activity.load({
            type: controller.types.FRAGMENT,
            title: 'Activity',
            objType: 'activity',
            objReference: this.props.entity.objType + ':' + this.props.entity.id
        }, inlineCon);
    }
});

module.exports = Activity;
