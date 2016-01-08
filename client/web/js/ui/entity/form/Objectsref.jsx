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
var CustomEventListen = require("../../mixins/CustomEventListen.jsx");
var controller = require("../../../controller/controller");
var netric = require("../../../base");
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

    mixins: [CustomEventTrigger, CustomEventListen],

    getInitialState: function () {

        // Return the initial state
        return {
            entityController: null
        };
    },

    /**
     * Render the browser after the component mounts
     */
    componentDidMount: function () {
        if (this.props.entity.id) {
            this._loadEntities();
        }

        var func = function () {
            this._loadEntities();
        }.bind(this);

        this.listenCustomEvent("entityClose", func);
    },

    /**
     * Render the component
     */
    render: function () {

        var note = null;
        if (!this.props.entity.id) {
            note = "Please save changes to view more details.";
        }

        return (
            <div ref="bcon">{note}</div>
        );
    },

    /**
     * Trigger a custom event to send back to the entity controller
     */
    _sendEntityClickEvent: function (objType, oid) {

        if (oid == 'new') {

            var xmlNode = this.props.xmlNode;
            var entityName = this.props.entity.getValue('name');
            var refField = xmlNode.getAttribute('ref_field');

            // If we have refField is set, then add it in the query parameters
            if (refField) {
                oid += '?' + refField + '=' + this.props.entity.id;
                oid += '&' + refField + '_val=' + encodeURIComponent(entityName);
            }
        }

        this.triggerCustomEvent("entityclick", {objType: objType, id: oid});
    },

    /**
     * Load the entity browser controller either inline or as dialog for smaller devices
     *
     * @private
     */
    _loadEntities: function () {

        // Only load object reference if this device displays inline comments (size > medium)
        if (netric.getApplication().device.size < _minimumInlineDeviceSize) {
            return;
        }

        // We only display comments if working with an actual entity
        if (!this.props.entity.id) {
            return;
        }

        var xmlNode = this.props.xmlNode;
        var objType = xmlNode.getAttribute('obj_type');
        var refField = xmlNode.getAttribute('ref_field');

        // Check if we have already loaded the entity browser controller for this specific objType
        if (this.state.entityController) {

            // Just refresh the results and return
            entityController.refresh();
            return;
        }

        // Add filter to reference the current entity
        var filters = [];
        if (refField) {

            // Create a filter reference
            var whereCond = new Where(refField);
            whereCond.equalTo(this.props.entity.id);

            filters.push(whereCond);
        }

        var data = {
            type: controller.types.FRAGMENT,
            hideToolbar: false,
            toolbarMode: 'toolbar',
            objType: objType,
            eventsObj: this.props.eventsObj,
            filters: filters,
            onEntityClick: function (objType, oid) {
                this._sendEntityClickEvent(objType, oid);
            }.bind(this)
        }

        // Add filter to reference current entity
        data[refField] = this.props.entity.id;

        // Require EntityBrowserController here so we do not risk a circular dependency
        var EntityBrowserController = require("../../../controller/EntityBrowserController");

        // Create browser and render
        var browser = new EntityBrowserController();
        browser.load(data, ReactDOM.findDOMNode(this.refs.bcon));

        // Update the state objects
        this.setState({
            entityController: browser
        });
    }

});

module.exports = Objectsref;