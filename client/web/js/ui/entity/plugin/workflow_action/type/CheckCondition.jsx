/**
 * Handle a check condition type action
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
var EntityConditions = require('../../../Conditions.jsx');
var Where = require('../../../../../entity/Where');


/**
 * Manage action data for check condition
 */
var CheckCondition = React.createClass({

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

        let conditions = [];
        let conditionDisplay = [];


        if (this.props.data.conditions) {

            // Mapp thru data.conditions to get the saved conditions
            for (var idx in this.props.data.conditions) {
                let condition = this.props.data.conditions[idx];
                let where = new Where();

                where.fromData(condition);

                // If this is editMode then we just push the conditions into an array
                if (this.props.editMode) {
                    conditions.push(where);
                } else {

                    // If this is the first condition then we do not need to display the bLogic
                    let skipBlogic = (idx == 0) ? true : false;

                    // Build the conditions description
                    conditionDisplay.push(
                        <div key={Math.random()}>
                            <div className="entity-form-field-label">
                                {'Condition ' + (parseInt(idx) + 1).toString()}
                            </div>
                            <div className="entity-form-field-value">
                                {where.getHumanDesc(skipBlogic)}
                            </div>
                        </div>
                    );
                }
            }
        }

        if (this.props.editMode) {
            return (
                <EntityConditions
                    objType={this.props.objType}
                    conditions={conditions}
                    onChange={this._handleConditionsChange}
                />
            );
        } else {
            return (
                <div className="entity-form-field">
                    {conditionDisplay}
                </div>
            );
        }
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
     * When the user changes the conditions, handle it here
     *
     * @param {entity/Where[]} conditions Array of where conditions set
     * @private
     */
    _handleConditionsChange: function (conditions) {
        let conditionsData = [];
        for (var i in conditions) {
            conditionsData.push(conditions[i].toData());
        }

        // Update the data with the changes
        this._handleDataChange('conditions', conditionsData);
    }
});

module.exports = CheckCondition;
