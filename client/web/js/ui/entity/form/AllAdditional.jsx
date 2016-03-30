/**
 * All additional/custom fields
 *
 */
'use strict';

var React = require('react');
var FieldInput = require('../FieldInput.jsx');

/**
 * All additional will gather all custom (non-system) fields and print them
 *
 * This allows users to add custom fields without having to worry about placing
 * them into a form.
 *
 * We will use ref_field specified in the xml form to determine the custom fields to display
 */
var AllAdditional = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        /**
         * Current xml node level
         *
         * @type {XMLNode}
         */
        xmlNode: React.PropTypes.object,

        /**
         * Entity being edited
         *
         * @type {entity\Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Generic object used to pass events back up to controller
         *
         * @type {Object}
         */
        eventsObj: React.PropTypes.object,

        /**
         * Flag indicating if we are in edit mode or view mode
         *
         * @type {bool}
         */
        editMode: React.PropTypes.bool
    },

    render: function () {

        let fields = this.props.entity.def.getFields();
        let displayFields = [];

        if (fields) {
            fields.map(function (field, idx) {

                // Make sure that we have useWhen field attribute and the field is not a system field
                if (!field.system && field.useWhen) {

                    // Get the decoded value of useWhen
                    let useWhenObj = field.decodeUseWhen();

                    // If the useWhen value did not match with the entity field, then let's return and move to the next field
                    if(this.props.entity.getValue(useWhenObj.name) != useWhenObj.value) {
                        return;
                    }

                    let valueLabel = null;
                    let value = this.props.entity.getValue(field.name);

                    // If the field is an object, then let's display the label of the value.
                    if (field.type == field.types.object) {
                        valueLabel = this.props.entity.getValueName(field.name, value);
                    }

                    // If we are on editMode, then let's display the field input of each entity fields
                    if (this.props.editMode) {
                        displayFields.push(
                            <div key={idx + 'div'}>
                                <div className="entity-form-field-label">
                                    {field.title}
                                </div>
                                <div className="entity-form-field-value">
                                    <FieldInput
                                        key={idx}
                                        objType={this.props.entity.objType}
                                        fieldName={field.name}
                                        value={value}
                                        valueLabel={valueLabel}
                                        onChange={this._handleValueChange}
                                        entityDefinition={this.props.entity.def}
                                    />
                                </div>
                            </div>
                        )
                    } else {
                        if (value) {

                            let displayValue = valueLabel || value;

                            displayFields.push(
                                <div key={idx + 'label'}>
                                    <div className="entity-form-field-label">
                                        {field.title}
                                    </div>
                                    <div>
                                        {displayValue}
                                    </div>
                                </div>
                            )
                        }
                    }
                }
            }.bind(this))
        }

        return (
            <div className="entity-form-field">
                {displayFields}
            </div>
        );
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

        this.props.entity.setValue(fieldName, fieldValue, fieldValueLabel);
    }
});

module.exports = AllAdditional;