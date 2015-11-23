/**
 * @fileoverview Recurrence Controller
 *
 * Manages the the processing of the recurrence pattern
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var UiRecurrence = require("../ui/recurrence/Recurrence.jsx");

/**
 * Controller that loads a File Upload Component
 */
var RecurrenceController = function () {
}

/**
 * Extend base controller class
 */
netric.inherits(RecurrenceController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
RecurrenceController.prototype._rootReactNode = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
RecurrenceController.prototype.onLoad = function (opt_callback) {

    var callbackWhenLoaded = opt_callback || null;

    if (callbackWhenLoaded) {
        callbackWhenLoaded();
    } else {
        this.render();
    }
}

/**
 * Render this controller into the dom tree
 */
RecurrenceController.prototype.render = function () {

    // Set outer application container
    var domCon = this.domNode_;

    // Define the data
    var data = {
        title: this.props.title || "Recurrence",
        recurrencePattern: this.props.recurrencePattern,
        recurrenceIndex: this.props.recurrencePattern.getRecurrenceIndex(),
        displayType: this.getType(),
        dayOfWeek: this.props.recurrencePattern.getDayOfWeek(),
        instance: this.props.recurrencePattern.getInstance(),
        months: this.props.recurrencePattern.getMonths(),
        onNavBackBtnClick: this.props.onNavBackBtnClick || null,
        onSave: function (data) {
            this._handleSave(data);
        }.bind(this)
    }

    // Render browser component
    this._rootReactNode = ReactDOM.render(
        React.createElement(UiRecurrence, data),
        domCon
    );
}

/**
 * Render this controller into the dom tree
 *
 * @param {object} data     The pattern data that will be saved
 * @private
 */
RecurrenceController.prototype._handleSave = function (data) {
    var humanDesc = this.props.recurrencePattern.getHumanDesc(data);

    if (this.props.onSetRecurrence) this.props.onSetRecurrence(data, humanDesc);
}

module.exports = RecurrenceController;

