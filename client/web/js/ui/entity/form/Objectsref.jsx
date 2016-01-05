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

    mixins: [CustomEventTrigger],

    getInitialState: function () {

        // Return the initial state
        return {
            entityControllers: [],
            entityId: this.props.entity.id,
            entityName: this.props.entity.getValue('name'),

            /**
             * The reference field for this entity
             *
             * @type {string}
             */
            refField: null,

            /**
             * The reference value for reference field
             *
             * @type {string}
             */
            refFieldValue: null,
        };
    },

    /**
     * Render the browser after the component mounts
     */
    componentDidMount: function () {
        if (this.props.entity.id) {
            this._displayObjectBrowser(this.props);
        }
    },

    /**
     * Render the entity browser after receiving new props
     */
    componentWillReceiveProps: function (nextProps) {
        if (nextProps.entity.id) {
            this._displayObjectBrowser(nextProps);
        }
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

            // If we have refField is set, then add it in the query parameters
            if (this.state.refField) {
                oid += '?' + this.state.refField + '=' + this.state.refFieldValue;
                oid += '&' + this.state.refField + '_val=' + encodeURIComponent(this.state.entityName);

            }
        }

        this.triggerCustomEvent("entityclick", {objType: objType, id: oid});
    },

    /**
     * Display the object browser in the div with ref='bcon'
     *
     * @param {object} sourceProps      The props object that we will use to evaluate xmlNode
     * @private
     */
    _displayObjectBrowser: function (sourceProps) {

        // Only load object reference if this device displays inline comments (size > medium)
        if (netric.getApplication().device.size < _minimumInlineDeviceSize) {
            return;
        }

        // We only display comments if working with an actual entity
        if (!this.props.entity.id) {
            return;
        }

        // Get the objType that will be used for object browser
        var objType = sourceProps.xmlNode.getAttribute('obj_type');
        var refField = sourceProps.xmlNode.getAttribute('ref_field');
        var entityName = sourceProps.entity.getValue('name');
        var entityControllers = this.state.entityControllers;

        // Check if we have already loaded the entity browser controller for this specific objType
        if (entityControllers[objType]) {

            // Just refresh the results and return
            entityControllers[objType].refresh();
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
            hideAppBar: true,
            hideToolbar: false,
            objType: objType,
            eventsObj: this.props.eventsObj,
            filters: filters,
            onEntityClick: function (objType, oid) {
                this._sendEntityClickEvent(objType, oid);
            }.bind(this)
        }

        // Add filter to reference current entity
        data[refField] = this.props.entity.getValue(refField) || this.props.entity.id;

        // Require EntityBrowserController here so we do not risk a circular dependency
        var EntityBrowserController = require("../../../controller/EntityBrowserController");

        /**
         * Create the entity browser for this specific objType.
         * Each objType will have different entity browser controller.
         * Lets store it in the entityControllers array so it will be re-used when refreshing the list.
         */
        entityControllers[objType] = new EntityBrowserController();

        // Render the entity browser
        entityControllers[objType].load(data, ReactDOM.findDOMNode(this.refs.bcon));

        // Update the state objects
        this.setState({
            entityControllers: entityControllers,
            refField: refField,
            refFieldValue: sourceProps.entity.id,
            entityName: entityName
        });
    }

});

module.exports = Objectsref;