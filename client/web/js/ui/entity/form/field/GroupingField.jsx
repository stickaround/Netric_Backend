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

        // If the fieldValues is an array or an object, then lets loop thru it to get the actual values
        if (Array.isArray(fieldValues) || typeof fieldValues === 'object') {
            for (var idx in fieldValues) {

                let id = null,
                    name = null;

                // If it is an array, we are using the 'key' as id and 'value' as the field name
                if (Array.isArray(fieldValues)) {
                    id = fieldValues[idx].key;
                    name = fieldValues[idx].value;
                } else {

                    // If fieldValue is an object, then we will use the idx as our id
                    id = parseInt(idx);
                    name = fieldValues[idx];
                }

                // Setup the GroupingChip
                chips.push(
                    <GroupingChip
                        key={idx}
                        id={id}
                        onRemove={this._handleRemove}
                        name={name}
                    />
                );
            }
        } else {
            selectedValue = fieldValues;
        }

        // TODO: create a GroupingChip component
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
        }

        return (
            <div>{chips} {selectElement}</div>
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
        this.props.entity.addMultiValue(this.props.elementNode.getAttribute('name'), id, name);
    }
});

module.exports = GroupingField;