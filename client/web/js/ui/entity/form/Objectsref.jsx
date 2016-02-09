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
        this.triggerCustomEvent("entityclick", {objType: objType, id: oid});
    },

    /**
     * Trigger a create new entity event to send back to the entity controller
     */
    _createNewEntity: function () {
        var xmlNode = this.props.xmlNode;
        var objType = xmlNode.getAttribute('obj_type');
        var entityName = this.props.entity.getValue('name');
        var refField = xmlNode.getAttribute('ref_field');
        var params = [];

        // If we have refField is set, then add it in the query parameters
        if (refField) {
            params[refField] = this.props.entity.id;
            params[refField + '_val'] = encodeURIComponent(entityName);
        }

        this.triggerCustomEvent("entitycreatenew", {objType: objType, params: params});
    },

    /**
     * Load the entity browser controller either inline or as dialog for smaller devices
     *
     * @private
     */
    _loadEntities: function () {

        // Only load object reference if this device displays inline browsers (size > medium)
        if (netric.getApplication().device.size < _minimumInlineDeviceSize) {
            return;
        }

        // We only referenced entities if working with an existing entity
        if (!this.props.entity.id) {
            return;
        }

        var xmlNode = this.props.xmlNode;
        var objType = xmlNode.getAttribute('obj_type');
        var refField = xmlNode.getAttribute('ref_field');

        // Check if we have already loaded the entity browser controller for this specific objType
        if (this.state.entityController) {

            // Just refresh the results and return
            this.state.entityController.refresh();
            return;
        }

        // Add filter to reference the current entity
        var filters = [];
        if (refField) {

            var whereValue = this.props.entity.id;

            // Since we have an obj_reference refField, then we should set the whereValue to [objType:oid]
            if(refField === 'obj_reference') {
                whereValue = this.props.entity.objType + ':' + this.props.entity.id;
            }

            // Create a filter reference
            var whereCond = new Where(refField);
            whereCond.equalTo(whereValue);

            filters.push(whereCond);
        }

        var data = {
            type: controller.types.FRAGMENT,
            hideToolbar: false,
            toolbarMode: 'toolbar',
            objType: objType,
            filters: filters,
            onEntityClick: function (objType, oid) {
                this._sendEntityClickEvent(objType, oid);
            }.bind(this),
            onCreateNewEntity: function () {
                this._createNewEntity();
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