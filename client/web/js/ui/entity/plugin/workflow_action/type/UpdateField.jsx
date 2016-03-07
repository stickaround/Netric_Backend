/**
 * Handle an update field type action
 *
 * All actions have a 'data' field, which is just a JSON encoded string
 * used by the backend when executing the action.
 *
 * When the ActionDetails plugin is rendered it will decode or parse the string
 * and pass it down to the type component.
 *

 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var netric = require("../../../../../base");
var Field = require("../../../../../entity/definition/Field");
var FieldsDropDown = require("../../../FieldsDropDown.jsx");
var FieldInput = require("../../../FieldInput.jsx");

/**
 * Manage action data for sending an email
 */
var UpdateField = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * Callback to call when a user changes any properties of the action
         */
        onChange: React.PropTypes.func,

        /**
         * Flag indicating if we are in edit mode or view mode
         */
        editMode: React.PropTypes.bool,

        /**
         * The object type this action is running against
         */
        objType: React.PropTypes.string.isRequired,

        /**
         * Data from the action - decoded JSON object
         */
        data: React.PropTypes.object
    },

    /**
     * Render action type form
     *
     * @returns {JSX}
     */
    render: function() {

        // If we are not in edit mode then just display a human-readable message
        if (!this.props.editMode) {
            let viewModeDesc = "No fields set to update. Edit this action to change";

            // If a field has been set already then change the description
            if (this.props.data.update_field) {
                viewModeDesc = "Update " + this.props.data.update_field + " to ";
                viewModeDesc += (this.props.data.update_value) ? this.props.data.update_value : 'empty';
            }

            return (
                <div className="entity-form-field">{viewModeDesc}</div>
            );
        }

        let updateField = this.props.data.update_field || null;
        let updateValue = this.props.data.update_value || null;
        let connectionToString = null;

        // Set the field input
        let inputComponent = null;
        if (updateField) {
            inputComponent = (
                <FieldInput
                    objType={this.props.objType}
                    fieldName={updateField}
                    value={updateValue}
                    onChange={this._handleValueChange}
                />
            );

            connectionToString = " to ";
        }

        return (
            <div className="entity-form-field">
                <div className="entity-form-field-label">
                    Change
                </div>
                <div>
                    <div className="entity-form-field-inline-block">
                        <FieldsDropDown
                            objType={this.props.objType}
                            onChange={this._handleFieldChange}
                            showReadOnlyFields={false}
                            hideFieldTypes={[Field.types.objectMulti]}
                            selectedField={updateField}
                        />
                    </div>
                    <div className="entity-form-field-inline-block">
                        {connectionToString}
                    </div>
                    <div className="entity-form-field-inline-block">
                        {inputComponent}
                    </div>
                </div>
            </div>
        );

    },

    /**
     * Handle event where the user selects a field name
     *
     * @param fieldName
     * @private
     */
    _handleFieldChange: function(fieldName) {
        if (fieldName != this.props.update_field) {
            // Clear value since we cannot mix types
            this._handleDataChange("update_value", "");
            // Update which field we are working on
            this._handleDataChange("update_field", fieldName);
        }
    },


    /**
     * Handle the input value changing for the cuurent field
     *
     * @param {string} fieldName The name of the field changed
     * @param {any} fieldValue The value the field was changed to
     * @param {string} opt_fieldValueLabel Optional string describing an ID value
     * @private
     */
    _handleValueChange: function(fieldName, fieldValue, opt_fieldValueLabel) {
        let fieldValueLabel = opt_fieldValueLabel || null;
        this._handleDataChange("update_value", fieldValue);
    },

    /**
     * When a property changes send an event so it can be handled
     *
     * @param {string} property The name of the property that was changed
     * @param {string|int|Object} value Whatever we set the property to
     * @private
     */
    _handleDataChange: function(property, value) {
        var data = this.props.data;
        data[property] = value;
        if (this.props.onChange) {
            this.props.onChange(data);
        }
    }

});

module.exports = UpdateField;
