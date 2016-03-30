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

        let refField = this.props.xmlNode.getAttribute('ref_field');
        let fields = this.props.entity.def.getFields();
        let displayFields = [];

        if (fields) {
            fields.map(function (field, idx) {

                // Make sure that we have useWhen field attribute and it matches the ref_field specified in the xml form
                if (field.useWhen
                    && field.useWhen == refField + ':' + this.props.entity.getValue(refField)) {

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