/**
 * Plugin for displaying search conditions that must be met before strating workflow
 *

 */
'use strict';

var React = require('react');
var EntityConditions = require('../../Conditions.jsx');
var Where = require('../../../../entity/Where');

/**
 * Add conditions to a workflow
 */
var PluginConditions = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * Entity being edited
         *
         * @type {entity\Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Flag indicating if we are in edit mode or view mode
         *
         * @type {bool}
         */
        editMode: React.PropTypes.bool
    },

    /**
     * Render entity conditions
     */
    render: function() {

        // Get conditions from the workflow field 'conditions'
        var conditions = [];
        var conditionsString = this.props.entity.getValue("conditions");
        if (conditionsString) {
            var conditionsData = JSON.parse(conditionsString);
            for (var i in conditionsData) {
                var where = new Where();
                where.fromData(conditionsData[i]);
                conditions.push(where);
            }
        }

        return (
          <EntityConditions
            objType={this.props.entity.getValue("object_type")}
            conditions={conditions}
            onChange={this._handleConditionsChange}
          />
        );
    },

    /**
     * When the user changes the conditions, handle it here
     *
     * @param {entity/Where[]} conditions Array of where conditions set
     */
    _handleConditionsChange: function(conditions) {
        var conditionsData = [];
        for (var i in conditions) {
            conditionsData.push(conditions[i].toData());
        }

        // Convert to a string and set field value
        var conditionsString = "";
        if (conditionsData.length)
            conditionsString = JSON.stringify(conditionsData);

        // Set the entity value
        this.props.entity.setValue("conditions", conditionsString);
    }

});

module.exports = PluginConditions;
