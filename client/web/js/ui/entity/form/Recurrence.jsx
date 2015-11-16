/**
 * Entity Recurrence
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var controller = require("../../../controller/controller");

var Recurrence = React.createClass({

    render: function () {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);

        console.log(this.props.entity.getValueName('recurrence'));

        // TODO: We have to load the recurrence plugin here and handle updating this.props.entity

        if (this.props.editMode) {

            return (
                <a href='javascript: void(0)' onClick={this._handleShowRecurrence}>Does Not Repeat</a>
            );


        } else {

            // If there is no value then we don't need to show this field at all
            if (!fieldValue) {
                return (<div />);
            } else {
                return (
                    <div>
                        <div className="entity-form-field-label">Repeats</div>
                        <div className="entity-form-field-value">Human Description Here</div>
                    </div>
                );
            }
        }
    },

    _handleShowRecurrence: function () {

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var RecurrenceController = require("../../../controller/RecurrenceController");
        var recurrence = new RecurrenceController();

        recurrence.load({
            type: controller.types.DIALOG,
            title: "Recurrence",
            onSetRecurrence: function (data) {
                this._handleSetRecurrence(data);
            }.bind(this)
        });
    },

    /**
     * Saves the fileId and fileName of the uploaded file to the entity field 'attachments'
     *
     * @param {int} fileId          The id of the file uploaded
     * @param {string} fileName     The name of the file uploaded
     *
     * @private
     */
    _handleSetRecurrence: function (data) {

        // Add the file in the entity object
        this.props.entity.addMultiValue('recurrence', this.props.entity.def.type, 'object_type');
        this.props.entity.addMultiValue('recurrence', this.props.entity.def.id, 'object_type_id');

        for (var idx in data) {
            console.log(idx + ' = ' + data[idx]);

            this.props.entity.addMultiValue('recurrence', data[idx], idx);
        }
    },

});

module.exports = Recurrence;