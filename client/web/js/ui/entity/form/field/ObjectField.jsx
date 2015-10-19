/**
 * Object reference component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require("chamel");
var Dialog = Chamel.Dialog;
var controller = require("../../../../controller/controller");


/**
 * Handle displaying an object refrence
 */
var ObjectField = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    /**
     * Render the component
     */
    render: function () {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);

        if (this.props.editMode) {
            return (<div>Edit Mode Object</div>);
        } else {
            return (<div onClick={this._handleBrowseClick}>View Mode Object</div>);
        }

    },

    /**
     * The user has clicked browse to select an entity
     *
     * @param {DOMEvent} evt
     * @private
     */
    _handleBrowseClick: function(evt) {

        var fieldName = this.props.xmlNode.getAttribute('name');

        // Send an event to the entity controller to set the property for this field
        alib.events.triggerEvent(
            this.props.eventsObj,
            "set_object_field",
            {fieldName: fieldName}
        );

    },

    /**
     * TODO: We will set the value of the entity here
     *
     * @param {int} oid The unique id of the entity selected
     * @param {string} title The human readable title of the entity selected
     * @private
     */
    _handleSetValue: function(oid, title) {
        console.log("Setting value to", oid, title);
    }
});

module.exports = ObjectField;