/**
 * @fileoverview File Upload
 *
 * Manages the file uploading to the server.
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var UiRecurrence = require("../ui/recurrence/Recurrence.jsx");
var entityLoader = require("../entity/loader");

/**
 * Controller that loads a File Upload Component
 */
var RecurrenceController = function () {

    this._recurrenceTypes = [
        {id: '1', type: 'Daily'},
        {id: '2', type: 'Weekly'},
        {id: '3', type: 'Monthly'},
        {id: '4', type: 'Month-nth'},
        {id: '5', type: 'Yearly'},
        {id: '6', type: 'Year-nth'},
    ];

    this._dayOfWeek = [
        {key: '1', text: 'Sunday'},
        {key: '2', text: 'Monday'},
        {key: '3', text: 'Tuesday'},
        {key: '4', text: 'Wednesday'},
        {key: '5', text: 'Thursday'},
        {key: '6', text: 'Friday'},
        {key: '7', text: 'Saturday'},
    ];

    this._instance = [
        {key: '1', text: 'The First'},
        {key: '2', text: 'The Second'},
        {key: '3', text: 'The Third'},
        {key: '4', text: 'The Fourt'},
        {key: '5', text: 'The Last'},
    ];

    this._months = [
        {key: '1', text: 'January'},
        {key: '2', text: 'February'},
        {key: '3', text: 'March'},
        {key: '4', text: 'April'},
        {key: '5', text: 'May'},
        {key: '6', text: 'June'},
        {key: '7', text: 'July'},
        {key: '8', text: 'August'},
        {key: '9', text: 'September'},
        {key: '10', text: 'October'},
        {key: '11', text: 'November'},
        {key: '12', text: 'December'},
    ];
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
 * The name of the object type we are working with
 *
 * @public
 * @type {string}
 */
RecurrenceController.prototype.objType = 'recurrence';

/**
 * The entity of the recurrence object
 *
 * @private
 * @type {Array}
 */
RecurrenceController.prototype._recurrenceEntity = null;

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

    return;

    // Create an empty file entity
    entityLoader.factory(this.objType, function (ent) {

        this._recurrenceEntity = ent;

        if (callbackWhenLoaded) {
            callbackWhenLoaded();
        } else {
            this.render();
        }
    }.bind(this));
}

/**
 * Render this controller into the dom tree
 */
RecurrenceController.prototype.render = function () {

    // Set outer application container
    var domCon = this.domNode_;

    console.log(this._instance);

    // Define the data
    var data = {
        title: this.props.title || "Recurrence",
        displayType: this.getType(),
        recurrenceTypes: this._recurrenceTypes,
        dayOfWeek: this._dayOfWeek,
        instance: this._instance,
        months: this._months,
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

RecurrenceController.prototype._handleSave = function (data) {
    if (this.props.onSetRecurrence) this.props.onSetRecurrence(data);
}

module.exports = RecurrenceController;

