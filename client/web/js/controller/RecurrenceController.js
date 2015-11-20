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
var entityLoader = require("../entity/loader");
var DateTime = require('../ui/utils/DateTime.jsx');

/**
 * Controller that loads a File Upload Component
 */
var RecurrenceController = function () {

    this._recurrenceTypes = [
        {id: '1', text: 'Daily'},
        {id: '2', text: 'Weekly'},
        {id: '3', text: 'Monthly'},
        {id: '4', text: 'Month-nth'},
        {id: '5', text: 'Yearly'},
        {id: '6', text: 'Year-nth'},
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
        {key: '4', text: 'The Fourth'},
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

    // Define the data
    var data = {
        title: this.props.title || "Recurrence",
        displayType: this.getType(),
        dateToday: DateTime.getDateToday(),
        dayOfWeek: this._dayOfWeek,
        instance: this._instance,
        months: this._months,
        onNavBackBtnClick: this.props.onNavBackBtnClick || null,
        onSave: function (data) {
            this._handleSave(data);
        }.bind(this)
    }

    // If we have saved data, then lets pass it in the props
    if (this.props.data) {

        var patternData = this.props.data;

        // Lets evaluate the recurrence type and determine the selected index
        if (this.props.data.type == 4) { // Month Nth
            patternData.typeIndex = 3;
        } else if (this.props.data.type >= 5) { // Year Nth
            patternData.typeIndex = 4;
        } else {
            patternData.typeIndex = this.props.data.type;
        }

        data.data = patternData;
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
    var humanDesc = this.getHumanDesc(data);

    if (this.props.onSetRecurrence) this.props.onSetRecurrence(data, humanDesc);
}

/**
 * Get the human description to be displayed
 *
 * @param {object} data     The pattern data that will be used to generate the human description
 * @private
 */
RecurrenceController.prototype.getHumanDesc = function (data) {
    var humanDesc = null;
    var dayOfMonth = null;

    // Convert the day of the month
    if (data.dayOfMonth) {
        var n = parseInt(data.dayOfMonth) % 100;
        var suff = ["th", "st", "nd", "rd", "th"];
        var ord = n < 21 ? (n < 4 ? suff[n] : suff[0]) : (n % 10 > 4 ? suff[0] : suff[n % 10]);
        dayOfMonth = 'Every ' + data.dayOfMonth + ord + ' day of ';
    }


    switch (parseInt(data.type)) {
        case 1: // Daily

            // interval
            if (data.interval > 1) {
                humanDesc = ' Every ' + data.interval + ' days';
            }
            else {
                humanDesc = 'Every day ';
            }
            break;

        case 2: // Weekly

            // interval
            if (data.interval > 1) {
                humanDesc = 'Every ' + data.interval + ' weeks on ';
            }
            else {
                humanDesc = 'Every ';
            }

            // day of week
            for (var idx in this._dayOfWeek) {
                if (data.dayOfWeekly[this._dayOfWeek[idx].key] == 't') {
                    humanDesc += this._dayOfWeek[idx].text + ', ';
                }
            }

            humanDesc = humanDesc.replace(/, $/, "");
            break;

        case 3: // Monthly

            humanDesc = dayOfMonth;

            if (parseInt(data.interval) > 1) {
                humanDesc += data.interval + ' months';
            }
            else {
                humanDesc += data.interval + ' month';
            }
            break;

        case 4: // Monthnth

            humanDesc = this._instance[parseInt(data.instance) - 1].text;

            // Day of week
            humanDesc += ' ' + this._dayOfWeek[(data.dayOfWeek) - 1].text;

            if (parseInt(data.interval) > 1) {
                humanDesc += ' of every ' + data.interval + ' months';
            }
            else {
                humanDesc += ' of every month';
            }
            break;

        case 5: // Yearly

            humanDesc = dayOfMonth + ' ' + this._months[parseInt(data.monthOfYear) - 1].text;
            break;

        case 6: // Yearnth

            humanDesc = this._instance[parseInt(data.instance) - 1].text;

            // Day of week
            humanDesc += ' ' + this._dayOfWeek[(data.dayOfWeek) - 1].text;

            // Month of year
            humanDesc += ' of ' + this._months[parseInt(data.monthOfYear) - 1].text;

            break;
        default:
            humanDesc = "Does not repeat";
            return this.humanDesc;
    }

    // date
    var dateStart = new Date(data.dateStart);
    humanDesc += ' effective ' + DateTime.format(dateStart, "MM/dd/yyyy");

    // end date
    if (data.dateEnd) {
        var dateEnd = new Date(data.dateEnd);
        humanDesc += ' until ' + DateTime.format(dateEnd, "MM/dd/yyyy");
    }

    // time
    if (data.fAllDay == 'f') {
        humanDesc += ' at ' + data.timeStart + ' to ' + data.timeEnd;
    }

    return humanDesc;
}

module.exports = RecurrenceController;

