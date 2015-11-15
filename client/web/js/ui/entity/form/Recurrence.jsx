/**
 * Entity Recurrence
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var controller = require("../../../controller/controller");

var Recurrence = React.createClass({

    render: function() {

        var xmlNode = this.props.xmlNode;
        return (
            <div>
                <a href='javascript: void(0)' onClick={this._handleShowRecurrence}>Does Not Repeat</a>
            </div>
        );
    },

    _handleShowRecurrence: function() {

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var RecurrenceController = require("../../../controller/RecurrenceController");
        var recurrence = new RecurrenceController();

        recurrence.load({
            type: controller.types.DIALOG,
            title: "Recurrence",
        });
    }

});

module.exports = Recurrence;