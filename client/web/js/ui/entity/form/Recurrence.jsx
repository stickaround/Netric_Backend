/**
 * Entity Recurrence
 *

 */
'use strict';

var React = require('react');
var controller = require("../../../controller/controller");

var Recurrence = React.createClass({

    getInitialState: function () {
        var humanDesc = 'Does not repeat';
        var recurrencePattern = this.props.entity.getRecurrence();

        if (recurrencePattern) {
            humanDesc = recurrencePattern.getHumanDesc();
        }

        // Return the initial state
        return {
            humanDesc: humanDesc
        };
    },

    render: function () {

        if (this.props.editMode) {

            return (
                <a href='javascript: void(0)' onClick={this._handleShowRecurrence}>{this.state.humanDesc}</a>
            );

        } else {

            // If there is no value then we don't need to show this field at all
            if (!this.state.humanDesc) {
                return (<div />);
            } else {
                return (
                    <div className="entity-form-field">
                        <div className="entity-form-field-label">Repeats</div>
                        <div className="entity-form-field-value">{this.state.humanDesc}</div>
                    </div>
                );
            }
        }
    },

    /**
     * Handles the showing of recurrence
     *
     * @private
     */
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
            recurrencePattern: this.props.entity.getRecurrence(true),
            onSetRecurrence: function (data, humanDesc) {
                this._handleSetRecurrence(data, humanDesc);
            }.bind(this)
        });
    },

    /**
     * Sets the recurrence data
     *
     * @param {object} recurrencePattern    Recurrence pattern data
     * @param {string} humanDesc            The human description of the pattern data
     *
     * @private
     */
    _handleSetRecurrence: function (recurrencePattern, humanDesc) {
        this.props.entity.setRecurrence(recurrencePattern);
        this.setState({
            humanDesc: this.props.entity.getRecurrence().getHumanDesc()
        })
    }
});

module.exports = Recurrence;