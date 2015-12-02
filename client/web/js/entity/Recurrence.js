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
     * dayOfWeek value of the recurrence pattern
     *
     * If the pattern type is weekly, this will be an array. Else it will only have integer value.
     * Weekly Array will contain the bitmask value of the selected day.
     * Example of weekly array:
     * If Monday is selected: this.dayOfWeek[1] = 2;
     * If Friday is selected: this.dayOfWeek[5] = 32;
     * If Saturday is selected: this.dayOfWeek[6] = 64;
     *
     * @public
     * @type {int|array}
     */
    this.dayOfWeek = null;

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
 * Day of week array
 *
 *
 * @private
 */
Recurrence._dayOfWeek = [
    {value: '1', text: 'Sunday'},
    {value: '2', text: 'Monday'},
    {value: '4', text: 'Tuesday'},
    {value: '8', text: 'Wednesday'},
    {value: '16', text: 'Thursday'},
    {value: '32', text: 'Friday'},
    {value: '64', text: 'Saturday'},
];

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
 * Get the recurrence pattern type menu
 *
 * @public
 * @return {array}
 */
Recurrence.prototype.getTypeMenuData = function () {
    var recurrenceTypeMenu = [
        {value: Recurrence._types.DONOTREPEAT, text: 'Does Not Repeat'},
        {value: Recurrence._types.DAILY, text: 'Daily'},
        {value: Recurrence._types.WEEKLY, text: 'Weekly'},
        {value: Recurrence._types.MONTHLY, text: 'Monthly'},
        {value: Recurrence._types.YEARLY, text: 'Yearly'},
    ];

    return recurrenceTypeMenu;
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

        // If recurrence type is weekly, then lets convert the day_of_week_mask into array
        if(data.recur_type == Recurrence._types.WEEKLY) {
            var convertedBitMask = this.convertBitMask(data.day_of_week_mask);
            this.dayOfWeek = [];

            for (var idx in convertedBitMask) {
                if (convertedBitMask[idx] > 0) {
                    this.dayOfWeek[idx] = Recurrence._dayOfWeek[idx].value;
                }
            }
        } else {
            this.dayOfWeek = data.day_of_week_mask;
        }
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
        day_of_month: this.dayOfMonth,
        month_of_year: this.monthOfYear,
        date_start: this.dateStart,
        date_end: this.dateEnd
    }

    // If the recurence type is weekly, then lets calculate the bitmask
    if(this.type == Recurrence._types.WEEKLY) {
        data.day_of_week_mask = this.weeklyBitMask(this.dayOfWeek);
    } else {
        data.day_of_week_mask = this.dayOfWeek;
    }
    
    switch(this.objType) {
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
                humanDesc = ' Every ' + this.interval + ' days';
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
            for (var idx in this.dayOfWeek) {
                if (this.dayOfWeek[idx] && this.dayOfWeek[idx] > 0) {
                    humanDesc += Recurrence._dayOfWeek[idx].text + ', ';
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
            var dayOfWeekIndex = this.getBitMaskIndex(this.dayOfWeek);
            humanDesc += ' ' + Recurrence._dayOfWeek[dayOfWeekIndex].text;

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
            var dayOfWeekIndex = this.getBitMaskIndex(this.dayOfWeek);
            humanDesc += ' ' + Recurrence._dayOfWeek[dayOfWeekIndex].text;

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
 * Get the days of week
 *
 * @public
 * @return {array}
 */
Recurrence.prototype.getDayOfWeek = function () {
    return Recurrence._dayOfWeek;
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
    this.dayOfWeek = null;
    this.interval = 1; // Interval will always have a default value of 1
    this.instance = null;
    this.dayOfMonth = null;
    this.monthOfYear = null;
    this.dateStart = null;
    this.dateEnd = null;
}

/**
 * Set default value value
 *
 * @public
 */
Recurrence.prototype.setDefaultValues = function () {
    switch (this.type.toString()) {
        case Recurrence._types.WEEKLY:
            this.dayOfWeek = [];
            break;

        case Recurrence._types.MONTHLY:
            this.dayOfMonth = 1;
            break;

        case Recurrence._types.MONTHNTH:
            this.instance = 1;
            this.dayOfWeek = 1;
            break;

        case Recurrence._types.YEARLY:
            this.monthOfYear = 1;
            this.dayOfMonth = 1;
            break;

        case Recurrence._types.YEARNTH:
            this.instance = 1;
            this.dayOfWeek = 1;
            this.monthOfYear = 1;
            break;

        default:
            break;
    }
}

/**
 * Converts the bit mask
 *
 * @param {int} bitmask     Bitmask used to turn on and off days of the week
 * @return {array}
 * @public
 */
Recurrence.prototype.convertBitMask = function (bitmask) {
    var bit = Number(bitmask).toString(2);
    var result = bit.toString(10).split("").map(Number).reverse();

    return result;
}

/**
 * Gets the array index of the bit mask
 *
 * @param {int} bitmask     Bitmask used to turn on and off days of the week
 * @return {int}            Returns the index of the array
 * @public
 */
Recurrence.prototype.getBitMaskIndex = function (bitmask) {
    var dayOfWeek = this.convertBitMask(bitmask);
    var index = dayOfWeek.indexOf(1);

    if (index < 0) {
        index = 0;
    }

    return index;
}

/**
 * Calculate the bitmask for weekly
 *
 * @param {int} bitmask     Bitmask used to turn on and off days of the week
 * @return {int}            Returns the index of the array
 * @public
 */
Recurrence.prototype.weeklyBitMask = function (dayOfWeek) {
    var bitMask = null;

    for(var idx in dayOfWeek) {
        var day = dayOfWeek[idx];

        bitMask = bitMask | day;
    }

    return bitMask;
}

module.exports = Recurrence;