/**
 * Grouping field component
 *

 */
'use strict';

var React = require('react');
var GroupingChip = require("../../GroupingChip.jsx");
var GroupingSelect = require("../../GroupingSelect.jsx");

/**
 * Base level element for enetity forms
 */
var GroupingField = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        elementNode: React.PropTypes.object.isRequired,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    /**
     * Render the component
     */
    render: function () {

        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');
        var field = this.props.entity.def.getField(fieldName);
        var fieldValues = this.props.entity.getValueName(fieldName);

        var chips = [];
        var selectedValue = null;
        var selectedValueName = null;

        /*
         * If field.type is either objectMulti or fkeyMulti then we need to loop thru the saved value
         * and display then inside the <GroupingChip>
         */
        if(field.type == field.types.objectMulti || field.type == field.types.fkeyMulti) {



            // If the fieldValues is an array then lets loop thru it to get the actual values
            if (Array.isArray(fieldValues)) {
                for (let idx in fieldValues) {

                    // Setup the GroupingChip
                    chips.push(
                        <GroupingChip
                            key={idx}
                            id={parseInt(fieldValues[idx].key)}
                            onRemove={this._handleRemove}
                            name={fieldValues[idx].value}
                        />
                    );
                }
            } else if(typeof fieldValues === 'object') {

                for (let idx in fieldValues) {

                    /*
                     * If fieldValues is an object, then let's use the idx as the id
                     *  and use the fieldValue[idx] as the name
                     */
                    chips.push(
                        <GroupingChip
                            key={idx}
                            id={parseInt(idx)}
                            onRemove={this._handleRemove}
                            name={fieldValues[idx]}
                        />
                    );
                }
            }
        } else if(fieldValues) {

            // Here we will handle fields that has fkey or object field.type

            selectedValue = this.props.entity.getValue(fieldName);
            selectedValueName = fieldValues[selectedValue];
        }

        var selectElement = null;

        if (this.props.editMode) {

            var addLabel = "Set " + field.title;

            if (field.type == field.types.fkeyMulti) {
                addLabel = "Add " + field.title;
            }

            selectElement = (
                <GroupingSelect
                    objType={this.props.entity.def.objType}
                    fieldName={fieldName}
                    onChange={this._handleGroupAdd}
                    label={addLabel}
                    selectedValue={selectedValue}
                />
            );

            // If we are on edit mode, we do not need to display the selectedValueName
            selectedValueName = null;
        }

        return (
            <div>{chips} {selectedValueName} {selectElement}</div>
        );
    },

    /**
     * Handle removing value from the grouping field in the entity
     *
     * @param {string} id The unique id of the grouping to remove
     * @param {string} name Optional name value of the id
     */
    _handleRemove: function (id, name) {
        this.props.entity.remMultiValue(this.props.elementNode.getAttribute('name'), id);
    },

    /**
     * Handle adding a value to a grouping field (or setting if not _multi)
     *
     * @param {string} id The unique id of the grouping to remove
     * @param {string} name Optional name value of the id
     */
    _handleGroupAdd: function (id, name) {
        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');
        var field = this.props.entity.def.getField(fieldName);

        /*
         * If field.type is either objectMulti or fkeyMulti then we need to need to use entity.addMultiValue
         * Since multi fields can save multiple values
         */
        if(field.type == field.types.objectMulti || field.type == field.types.fkeyMulti) {
            this.props.entity.addMultiValue(fieldName, id, name);
        } else {
            this.props.entity.setValue(fieldName, id, name);
        }

    }
});

module.exports = GroupingField;