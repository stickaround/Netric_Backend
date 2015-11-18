/**
 * Entity Recurrence
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var controller = require("../../../controller/controller");

var Recurrence = React.createClass({

    getInitialState: function () {
        var humanDesc = 'Does not repeat';
        var pattern = this.props.entity.getRecurrence() || null;

        if(pattern) {
            humanDesc = this._getHumanDesc(pattern);
        }

        // Return the initial state
        return {
            humanDesc: humanDesc,
            pattern: pattern
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
                    <div>
                        <div className="entity-form-field-label">Repeats</div>
                        <div className="entity-form-field-value">{this.state.humanDesc}</div>
                    </div>
                );
            }
        }
    },

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
            data: this.state.pattern,
            onSetRecurrence: function (data, humanDesc) {
                this._handleSetRecurrence(data, humanDesc);
            }.bind(this)
        });
    },

    /**
     * Saves the fileId and fileName of the uploaded file to the entity field 'attachments'
     *
     * @param {int} fileId          The id of the file uploaded
     * @param {string} fileName     The name of the file uploaded
     *
     * @private
     */
    _handleSetRecurrence: function (data, humanDesc) {
        this.props.entity.setRecurrence(data);
        this.setState({humanDesc: humanDesc});
    },

    _getHumanDesc: function (data) {

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var RecurrenceController = require("../../../controller/RecurrenceController");
        var recurrence = new RecurrenceController();

        return recurrence.getHumanDesc(data);
    }

});

module.exports = Recurrence;