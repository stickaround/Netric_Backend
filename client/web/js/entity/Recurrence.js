/**
 * @fileOverview Entity for Recurrence
 *
 * @author =    Marl Tumulak; marl.tumulak@aereus.com;
 *            Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
'use strict';

var DateTime = require('../ui/utils/DateTime.jsx');

/**
 * Entity represents the recurrence
 *
 * @constructory
 * @param {string} objType  The object type of this recurrence
 */
var Recurrence = function (objType) {
    /**
     * The name of the object type we are working with
     *
     * @public
     * @type {string}
     */
    this.objType = objType || null;

    /**
     * Id of the recurrence pattern
     *
     * @public
     * @type {int}
     */
    this.id = null;

    /**
     * Type of the recurrence pattern
     *
     * This will contain one of the values of the Recurrence._types
     * Please refer to Recurrence._types object
     *
     * @public
     * @type {int}
     */
    this.type = Recurrence._types.DONOTREPEAT;

    /**
     * Bitmask used to turn on and off days of the week
     *
     * @public
     * @type {int}
     */
    this.dayOfWeekMask = null;

    /**
     * Interval value of the recurrence pattern
     *
     * Example:
     * 1 = every day/week/month/year
     * 2 = every other day/week/month/year
     *
     * @public
     * @type {int}
     */
    this.interval = 1;

    /**
     * Instance value of the recurrence pattern
     *
     * This will contain either First, Second, Third, Fourth, or Last integer values.
     * Please refer to Recurrence._instance object
     *
     * @public
     * @type {int}
     */
    this.instance = null;

    /**
     * dayOfMonth value of the recurrence pattern
     *
     * @public
     * @type {int}
     */
    this.dayOfMonth = null;

    /**
     * monthOfYear value of the recurrence pattern
     *
     * This will contain the index of correspondign month selected by the user
     * Please refer to Recurrence._months object
     *
     * @public
     * @type {int}
     */
    this.monthOfYear = null;

    /**
     * Start date of the recurrence pattern
     *
     * @public
     * @type {Date}
     */
    this.dateStart = null;

    /**
     * End date of the recurrence pattern
     *
     * @public
     * @type {Date}
     */
    this.dateEnd = null;
}

/**
 * Recurrence Types
 *
 * @constant
 * @private
 */
Recurrence._types = {
    DONOTREPEAT: '0',
    DAILY: '1',
    WEEKLY: '2',
    MONTHLY: '3',
    MONTHNTH: '4',
    YEARLY: '5',
    YEARNTH: '6',
}

/**
 * Constants used for dayOfWeekMask bitmask
 *
 * @public
 */
Recurrence.prototype.weekdays = {
    SUNDAY: 1,
    MONDAY: 2,
    TUESDAY: 4,
    WEDNESDAY: 8,
    THURSDAY: 16,
    FRIDAY: 32,
    SATURDAY: 64
};

/**
 * Instance array
 *
 * @private
 */
Recurrence._instance = [
    {value: '1', text: 'The First'},
    {value: '2', text: 'The Second'},
    {value: '3', text: 'The Third'},
    {value: '4', text: 'The Fourth'},
    {value: '5', text: 'The Last'},
];

/**
 * Months array
 *
 * @private
 */
Recurrence._months = [
    {value: '1', text: 'January'},
    {value: '2', text: 'February'},
    {value: '3', text: 'March'},
    {value: '4', text: 'April'},
    {value: '5', text: 'May'},
    {value: '6', text: 'June'},
    {value: '7', text: 'July'},
    {value: '8', text: 'August'},
    {value: '9', text: 'September'},
    {value: '10', text: 'October'},
    {value: '11', text: 'November'},
    {value: '12', text: 'December'},
];

/**
 * Creates the recurrence pattern type menu data that is mostly used for dropdown data in display view.
 *
 * @public
 * @return {array}
 */
Recurrence.prototype.getTypeMenuData = function () {
    var recurrenceTypeMenuData = [
        {value: Recurrence._types.DONOTREPEAT, text: 'Does Not Repeat'},
        {value: Recurrence._types.DAILY, text: 'Daily'},
        {value: Recurrence._types.WEEKLY, text: 'Weekly'},
        {value: Recurrence._types.MONTHLY, text: 'Monthly'},
        {value: Recurrence._types.YEARLY, text: 'Yearly'},
    ];

    return recurrenceTypeMenuData;
}

/**
 * Creates the dayOfWeek menu data that is mostly used for dropdown data in display view.
 *
 * @public
 * @return {array}
 */
Recurrence.prototype.getDayOfWeekMenuData = function () {
    var dayOfWeekMenuData = [];

    for (var day in this.weekdays) {
        var text = day[0] + day.slice(1).toLowerCase();

        dayOfWeekMenuData.push({
            value: this.weekdays[day],
            text:  text
        })
    }

    return dayOfWeekMenuData;
}

/**
 * Set the recurrence pattern
 *
 * @param {object} data     Recurrence pattern data
 * @public
 */
Recurrence.prototype.fromData = function (data) {

    this.id = data.id;
    this.type = data.recur_type;

    if (data.day_of_week_mask) {
        this.dayOfWeekMask = data.day_of_week_mask;
    }

    if (data.interval) {
        this.interval = data.interval;
    }

    if (data.instance) {
        this.instance = data.instance;
    }

    if (data.day_of_month) {
        this.dayOfMonth = data.day_of_month;
    }

    if (data.month_of_year) {
        this.monthOfYear = data.month_of_year;
    }

    if (data.date_start) {
        this.dateStart = data.date_start;
    }

    if (data.date_end) {
        this.dateEnd = data.date_end;
    }
}

/**
 * Get the recurrence pattern
 *
 * @public
 */
Recurrence.prototype.toData = function () {

    var data = {
        id: this.id,
        obj_type: this.objType,
        recur_type: this.type,
        interval: this.interval,
        instance: this.instance,
        day_of_week_mask: this.dayOfWeekMask,
        day_of_month: this.dayOfMonth,
        month_of_year: this.monthOfYear,
        date_start: this.dateStart,
        date_end: this.dateEnd
    }

    switch (this.objType) {
        case 'calendar_event':
            data.ts_start = this.dateStart;
            break;
        default:
            data.field_start_date = this.dateStart;
    }

    return data;
}

/**
 * Get the offset of the current recurrence type
 * This will determine what index to use in the Recurrence Type Menu Dropdown
 *
 * @return {int}
 * @public
 */
Recurrence.prototype.getRecurrenceTypeOffset = function () {

    var recurrIndex = 0;

    /**
     * Lets evaluate the recurrence type and determine the selected index
     * We will set the index of MonthNth and YearNth same to Monthly and Yearly respectively
     * Since we only have 1 dropdown entry for month and 1 dropdown entry for year
     * We will not change the index for daily and weekly since their index is already correct
     */
    if (this.type == Recurrence._types.MONTHNTH) {
        recurrIndex = Recurrence._types.MONTHLY;
    } else if (this.type >= Recurrence._types.YEARLY) {
        recurrIndex = Recurrence._types.YEARLY - 1;
    } else {
        recurrIndex = this.type;
    }

    return parseInt(recurrIndex);
}

/**
 * Get the human description to be displayed
 *
 * @private
 */
Recurrence.prototype.getHumanDesc = function () {
    var humanDesc = null;
    var dayOfMonth = null;

    // Convert the day of the month
    if (this.dayOfMonth) {
        var n = parseInt(this.dayOfMonth) % 100;
        var suff = ["th", "st", "nd", "rd", "th"];
        var ord = n < 21 ? (n < 4 ? suff[n] : suff[0]) : (n % 10 > 4 ? suff[0] : suff[n % 10]);
        dayOfMonth = 'Every ' + this.dayOfMonth + ord + ' day of ';
    }


    switch (this.type.toString()) {
        case Recurrence._types.DAILY:

            // interval
            if (this.interval > 1) {
                humanDesc = 'Every ' + this.interval + ' days';
            } else {
                humanDesc = 'Every day ';
            }

            break;

        case Recurrence._types.WEEKLY:

            // interval
            if (this.interval > 1) {
                humanDesc = 'Every ' + this.interval + ' weeks on ';
            } else {
                humanDesc = 'Every ';
            }

            // day of week
            var daysOfWeek = this.getDaysOfWeek();
            for (var day in daysOfWeek) {
                if (daysOfWeek[day] && daysOfWeek[day] > 0) {
                    humanDesc += day.replace(/^./, day[0].toUpperCase()) + ', ';
                }
            }

            humanDesc = humanDesc.replace(/, $/, "");
            break;

        case Recurrence._types.MONTHLY:

            humanDesc = dayOfMonth;

            if (parseInt(this.interval) > 1) {
                humanDesc += this.interval + ' months';
            } else {
                humanDesc += this.interval + ' month';
            }

            break;

        case Recurrence._types.MONTHNTH:

            humanDesc = Recurrence._instance[parseInt(this.instance) - 1].text;

            // Day of week
            var selectedDay = this.getSelectedDay();
            humanDesc += ' ' + selectedDay.label;

            if (parseInt(this.interval) > 1) {
                humanDesc += ' of every ' + this.interval + ' months';
            } else {
                humanDesc += ' of every month';
            }

            break;

        case Recurrence._types.YEARLY:

            humanDesc = dayOfMonth + ' ' + Recurrence._months[parseInt(this.monthOfYear) - 1].text;
            break;

        case Recurrence._types.YEARNTH:

            humanDesc = Recurrence._instance[parseInt(this.instance) - 1].text;

            // Day of week
            var selectedDay = this.getSelectedDay();
            humanDesc += ' ' + selectedDay.label;

            // Month of year
            humanDesc += ' of ' + Recurrence._months[parseInt(this.monthOfYear) - 1].text;

            break;

        default:

            humanDesc = "Does not repeat";
            return humanDesc;
            break;
    }

    // date
    var dateStart = new Date(this.dateStart);
    humanDesc += ' effective ' + DateTime.format(dateStart, "MM/dd/yyyy");

    // end date
    if (this.dateEnd) {
        var dateEnd = new Date(this.dateEnd);
        humanDesc += ' until ' + DateTime.format(dateEnd, "MM/dd/yyyy");
    }

    // time
    if (this.fAllDay == 'f') {
        humanDesc += ' at ' + this.timeStart + ' to ' + this.timeEnd;
    }

    return humanDesc;
}

/**
 * Get the recurrence types
 *
 * @public
 * @return {object}
 */
Recurrence.prototype.getRecurrenceTypes = function () {
    return Recurrence._types;
}

/**
 * Get the instances
 *
 * @public
 * @return {array}
 */
Recurrence.prototype.getInstance = function () {
    return Recurrence._instance;
}

/**
 * Get the months
 *
 * @public
 * @return {array}
 */
Recurrence.prototype.getMonths = function () {
    return Recurrence._months;
}

/**
 * Get the recurrence start date
 *
 * @public
 */
Recurrence.prototype.getDateStart = function () {
    if (!this.dateStart) {
        this.dateStart = DateTime.getDateToday();
    }

    return this.dateStart;
}

/**
 * Get the recurrence end date
 *
 * @public
 */
Recurrence.prototype.getDateEnd = function () {
    if (!this.dateEnd) {
        this.dateEnd = DateTime.getDateToday();
    }

    return this.dateEnd;
}

/**
 * Resets the recurrence variables
 *
 * @public
 */
Recurrence.prototype.reset = function () {
    this.id = null;
    this.dayOfWeekMask = null;
    this.instance = null;
    this.dayOfMonth = null;
    this.monthOfYear = null;
    this.dateStart = null;
    this.dateEnd = null;
    this.interval = 1; // Interval will always have a default value of 1
}

/**
 * Set default value value
 *
 * @public
 */
Recurrence.prototype.setDefaultValues = function () {
    this.getDateStart();

    switch (this.type.toString()) {

        case Recurrence._types.MONTHLY:
            this.dayOfMonth = 1;
            break;

        case Recurrence._types.MONTHNTH:
            this.instance = Recurrence._instance[0].value;
            this.dayOfWeekMask = this.weekdays.SUNDAY;
            break;

        case Recurrence._types.YEARLY:
            this.monthOfYear = Recurrence._months[0].value;
            this.dayOfMonth = 1;
            break;

        case Recurrence._types.YEARNTH:
            this.instance = Recurrence._instance[0].value;
            this.dayOfWeekMask = this.weekdays.SUNDAY;
            this.monthOfYear = Recurrence._months[0].value;
            break;

        default:
            break;
    }
}

/**
 * Set day of week on or off
 *
 * @public
 * @param {int} $day Day from Recurrence.WEEKDAYS
 * @param {bool} selected If true the day bit is on, if false then unset the bit
 */
Recurrence.prototype.setDayOfWeek = function (day, selected) {
    if (selected) {
        this.dayOfWeekMask = this.dayOfWeekMask | day;
    } else {
        this.dayOfWeekMask = this.dayOfWeekMask & ~day;


    }
};

/**
 * Get days of week selected as an object from the dayOfWeek bitmask
 *
 * @private
 * @returns {
 *  {
 *   sunday: 1|0,
 *   monday: 1|0,
 *   tuesday: 1|0,
 *   wednesday: 1|0,
 *   thursday: 1|0,
 *   friday: 1|0,
 *   saturday: 1|0
 *  }
 * }
 */
Recurrence.prototype.getDaysOfWeek = function () {
    return {
        sunday: this.dayOfWeekMask & this.weekdays.SUNDAY,
        monday: this.dayOfWeekMask & this.weekdays.MONDAY,
        tuesday: this.dayOfWeekMask & this.weekdays.TUESDAY,
        wednesday: this.dayOfWeekMask & this.weekdays.WEDNESDAY,
        thursday: this.dayOfWeekMask & this.weekdays.THURSDAY,
        friday: this.dayOfWeekMask & this.weekdays.FRIDAY,
        saturday: this.dayOfWeekMask & this.weekdays.SATURDAY
    };
};

/**
 * Get the selected day details. This will return an object with the dayOfWeek selected index and its label.
 *
 * @public
 * return {object}  Selected day details.
 */
Recurrence.prototype.getSelectedDay = function() {
    var selectedDay = {};
    var idx = 0;
    var daysOfWeek = this.getDaysOfWeek();

    for (var day in daysOfWeek) {
        if (daysOfWeek[day] && daysOfWeek[day] > 0) {
            selectedDay.index = idx;
            selectedDay.label = day.replace(/^./, day[0].toUpperCase());
            break;
        }
        idx++;
    }

    return selectedDay;
}

module.exports = Recurrence;