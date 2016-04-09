/**
 * Objectsref UIML element
 *

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
var entityLoader = require('../../../entity/loader');

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

        var refObjType = this.props.xmlNode.getAttribute('obj_type');
        var refField = this.props.xmlNode.getAttribute('ref_field');

        // Return the initial state
        return {
            refObjType: refObjType,
            refField: refField,
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
        var refField = this.state.refField;
        var entityName = this.props.entity.getValue('name');
        var params = [];

        // If refField is set, then add it in the query parameters
        if (refField) {

            var refValue = this.props.entity.id;

            /*
             * If the referenced field is an object and does NOT have a subtype,
             *  then the refField is an object reference field and NOT an object id field.
             * Since we do not know what is the objType of this field,
             *  we will include the entity's objType (this.props.entity.objType) in the query paramters
             * Now the query param will have the value objType:objId (sample: customer:1)
             */
            if (!this._checkRefFieldHasSubType()) {
                refValue = this.props.entity.objType + ':' + this.props.entity.id;
            }

            params[refField] = refValue;
            params[refField + '_fval'] = encodeURIComponent(entityName);
        }

        this.triggerCustomEvent("entitycreatenew", {objType: this.state.refObjType, params: params});
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

        // Check if we have already loaded the entity browser controller for this specific objType
        if (this.state.entityController) {

            // Just refresh the results and return
            this.state.entityController.refresh();
            return;
        }

        // Add filter to reference the current entity
        var filters = [];
        if (this.state.refField) {

            var whereValue = this.props.entity.id;

            /*
             * If the referenced field is an object and does NOT have a subtype,
             *  then the refField is an object reference field and NOT an object id field.
             * Since we do not know what is the objType of this field,
             *  we will include the entity's objType (this.props.entity.objType) in the where value.
             * Now the where value will be objType:objId (sample: customer:1)
             */
            if (!this._checkRefFieldHasSubType()) {
                whereValue = this.props.entity.objType + ':' + this.props.entity.id;
            }

            // Create a filter reference
            var whereCond = new Where(this.state.refField);
            whereCond.equalTo(whereValue);

            filters.push(whereCond);
        }

        var data = {
            type: controller.types.FRAGMENT,
            hideToolbar: false,
            toolbarMode: 'toolbar',
            objType: this.state.refObjType,
            filters: filters,
            onEntityClick: function (objType, oid) {
                this._sendEntityClickEvent(objType, oid);
            }.bind(this),
            onCreateNewEntity: function () {
                this._createNewEntity();
            }.bind(this)
        }

        // Add filter to reference current entity
        data[this.state.refField] = this.props.entity.id;

        // Require EntityBrowserController here so we do not risk a circular dependency
        var EntityBrowserController = require("../../../controller/EntityBrowserController");

        // Create browser and render
        var browser = new EntityBrowserController();
        browser.load(data, ReactDOM.findDOMNode(this.refs.bcon));

        // Update the state objects
        this.setState({
            entityController: browser
        });
    },

    /**
     * Evaluate the referenced field if it is an object and whether or not it has a subtype
     *
     * @returns {boolean}
     * @private
     */
    _checkRefFieldHasSubType: function() {

        // Get the entity definition of the referenced objType to access the field defintions
        var objRefEntity = entityLoader.factory(this.state.refObjType);

        // Get the field definition of the referenced field
        var refFieldDef = objRefEntity.def.getField(this.state.refField);

        if (refFieldDef.type == refFieldDef.types.object && refFieldDef.subtype) {
            return true;
        }

        return false;
    }

});

module.exports = Objectsref;