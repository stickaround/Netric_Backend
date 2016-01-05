/**
 * Objectsref UIML element
 *
 * @jsx React.DOM
 */
'use strict';

// Load dependencies
var React = require('react');
var ReactDOM = require('react-dom');
var CustomEventTrigger = require("../../mixins/CustomEventTrigger.jsx");
var controller = require("../../../controller/controller");
var Device = require("../../../Device");
var Where = require("../../../entity/Where");

/**
 * Constant indicating the smallest device that we can print a browser in
 *
 * All other devices will open browsers in a dialog when clicked
 *
 * @type {number}
 * @private
 */
var _minimumInlineDeviceSize = Device.sizes.large;

/**
 * Objectsref/entityList element
 */
var Objectsref = React.createClass({

    mixins: [CustomEventTrigger],

    getInitialState: function () {
        return {
            entityController: null,
            entityId: this.props.entity.id
        }
    },

    /**
     * Render the browser after the component mounts
     */
    componentDidMount: function() {
        this._loadEntities();
    },

    /**
     * Invoked immediately after the component's updates are flushed to the DOM.
     *
     * This method is not called for the initial render and we use it to check if
     * the id has changed - meaning a new entity was saved.
     */
    componentDidUpdate: function () {
        this._loadEntities();
    },

    /**
     * Render the component
     */
    render: function() {

    	return (
            <div ref="bcon"></div>
        );
        
    },

    /**
     * Trigger a custom event to send back to the entity controller 
     */
    sendEntityClickEvent_: function(objType, oid) {
        this.triggerCustomEvent("entityclick", {objType:objType, id:oid});
    },

    /**
     * Load the entity browser controller either inline or as dialog for smaller devices
     *
     * @private
     */
    _loadEntities: function () {

        // Only load comments if this device displays inline comments (size > medium)
        if (netric.getApplication().device.size < _minimumInlineDeviceSize) {
            return;
        }

        // We only display comments if workign with an actual entity
        if (!this.props.entity.id) {
            return;
        }

        // Check if we have already loaded the controller
        if (this.state.entityController) {
            // Just refresh the results and return
            this.state.entityController.refresh();
            return;
        }

        // Require EntityBrowserController here so we do not risk a circular dependency
        var EntityBrowserController = require("../../../controller/EntityBrowserController");

        var xmlNode = this.props.xmlNode;
        var name = xmlNode.getAttribute('name');
        var objType = xmlNode.getAttribute('obj_type');
        var refField = xmlNode.getAttribute('ref_field');

        var whereCond = new Where(refField);
        whereCond.equalTo(this.props.entity.id);

        var data = {
            type: controller.types.FRAGMENT,
            hideToolbar: true,
            objType: objType,
            filters: [ whereCond ],
            eventsObj: this.props.eventsObj,
            onEntityClick: function(objType, oid) {
                this.sendEntityClickEvent_(objType, oid);
            }.bind(this)
        }

        // Add filter for to reference current entity
        data[refField] = this.props.entity.getValue(refField);

        // Create browser and render
        var browser = new EntityBrowserController();
        browser.load(data, ReactDOM.findDOMNode(this.refs.bcon));

        this.setState({entityController: browser});
    }

});

module.exports = Objectsref;