/**
 * Handle the create entity type action
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
var entityLoader = require('../../../../../entity/loader');
var definitionLoader = require("../../../../../entity/definitionLoader");
var FieldInput = require("../../../FieldInput.jsx");
var ObjectTypeDropDown = require("../../../ObjectTypeDropDown.jsx");
var Field = require('../../../../../entity/definition/Field.js');
var Controls = require('../../../../Controls.jsx');
var DropDownMenu = Controls.DropDownMenu;
var TextField = Controls.TextField;

/**
 * Manage action data for create entity
 */
var CreateEntity = React.createClass({

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
    render: function () {

        let objType = this.props.data.obj_type || null;
        let definitionsDropDown = null;
        let entityFieldsDisplay = [];

        // If we are in editMode, then let's display the dropdown menu of object types
        if (this.props.editMode) {
            definitionsDropDown = (
                <ObjectTypeDropDown
                    objType={objType}
                    onChange={this._handleDefintionsMenuSelect}
                />
            );
        } else {

            // If we are NOT on editMode, then let's just display the objType text
            definitionsDropDown = (
                <div>
                    {objType}
                </div>
            );
        }


        // If we have an objType selected, then lets display the entity fields
        if (objType) {
            let entity = entityLoader.factory(objType);

            // Loop thru the entity fields and display each field using <FieldInput>
            entity.def.fields.map(function (field) {

                // Do not display the fields that are read only or are not objectMulti
                if (!field.readonly && (field.type && field.type != Field.types.objectMulti)) {

                    var key = objType + field.id;
                    var value = this.props.data[field.name] || null;
                    var valueLabel = null;

                    // If the field is an object, then let's display the label of the value.
                    if (field.type == field.types.object) {
                        valueLabel = this.props.entity.getValueName(field.name, value);
                    }

                    entityFieldsDisplay.push(
                        <FieldInput
                            key={key}
                            objType={objType}
                            fieldName={field.name}
                            value={value}
                            valueLabel={valueLabel}
                            onChange={this._handleValueChange}
                            entityDefinition={entity.def}
                            editMode={this.props.editMode}
                            displayFieldTitle={true}
                        />
                    );
                }
            }.bind(this));
        }

        return (
            <div className="entity-form-field">
                <div>
                    <div className="entity-form-field-label">
                        Object Type
                    </div>
                    <div className="entity-form-field-value">
                        {definitionsDropDown}
                    </div>
                </div>
                {entityFieldsDisplay}
            </div>
        );
    },

    /**
     * When a property changes send an event so it can be handled
     *
     * @param {string} property The name of the property that was changed
     * @param {string|int|Object} value Whatever we set the property to
     * @private
     */
    _handleDataChange: function (property, value) {
        let data = this.props.data;
        data[property] = value;
        if (this.props.onChange) {
            this.props.onChange(data);
        }
    },

    /**
     * Callback used to handle the selecting of definition
     *
     * @param {stirng} objType The object type that was selected
     * @private
     */
    _handleDefintionsMenuSelect: function (objType) {
        this._handleDataChange('obj_type', objType);
    },

    /**
     * Handle the input value changing for the entity field
     *
     * @param {string} fieldName The name of the field changed
     * @param {any} fieldValue The value the field was changed to
     * @param {string} opt_fieldValueLabel Optional string describing an ID value
     * @private
     */
    _handleValueChange: function (fieldName, fieldValue, opt_fieldValueLabel) {
        let fieldValueLabel = opt_fieldValueLabel || null;
        this._handleDataChange(fieldName, fieldValue, fieldValueLabel);
    }
});

module.exports = CreateEntity;
