/**
 * Grouping field component
 *
 * @jsx React.DOM
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
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    /**
     * Render the component
     */
    render: function() {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        

        var field = this.props.entity.def.getField(fieldName);
        var fieldValues = this.props.entity.getValueName(fieldName);

        var chips = [];
        if (Array.isArray(fieldValues)) {
            for (var i in fieldValues) {
                chips.push(
                    <GroupingChip id={fieldValues[i].key} onRemove={this._handleRemove} name={fieldValues[i].value} />
                );
            }
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
    _handleRemove: function(id, name) {
      this.props.entity.remMultiValue(this.props.xmlNode.getAttribute('name'), id);
    },

    /**
     * Handle adding a value to a grouping field (or setting if not _multi)
     *
     * @param {string} id The unique id of the grouping to remove
     * @param {string} name Optional name value of the id
     */
    _handleGroupAdd: function(id, name) {
        this.props.entity.addMultiValue(this.props.xmlNode.getAttribute('name'), id, name);
    }
});

module.exports = GroupingField;