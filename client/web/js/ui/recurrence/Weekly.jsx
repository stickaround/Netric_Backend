/**
 * Weekly recurrence pattern.
 * This will display the interval and days of week.
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var TextField = Chamel.TextField;
var Checkbox = Chamel.Checkbox;

var Weekly = React.createClass({

    propTypes: {
        recurrencePattern: React.PropTypes.object.isRequired,
    },

    componentDidMount: function () {
        this.refs.inputInterval.setValue(this.props.recurrencePattern.interval);
    },

    render: function () {
        var displayDays = [];
        var daysOfWeek = this.props.recurrencePattern.getDaysOfWeek();

        for (var day in daysOfWeek) {
            var bitmask = this.props.recurrencePattern.weekdays[day.toUpperCase()].toString();
            var dayLabel = day.replace(/^./, day[0].toUpperCase());
            var ref = 'dayOfWeek' + day;
            var checked = false;
            if (daysOfWeek[day] && daysOfWeek[day] == bitmask) {
                checked = true;
            }

            displayDays.push(<Checkbox
                key={day}
                value={bitmask}
                ref={ref}
                label={dayLabel}
                onCheck={this._handleOnCheck.bind(this, day)}
                defaultSwitched={checked}/>)
        }

        return (
            <div>
                <div>
                    <label>Every </label>
                    <TextField
                        className='recurrence-input'
                        ref='inputInterval'
                        onBlur={this._handleInputBlur}/>
                    <label> week(s) on:</label>
                </div>
                <div>
                    {displayDays}
                </div>
            </div>
        );
    },

    /**
     * Handles the clicking of checkbox for weekOfDay
     *
     * @param {string} day          The day that was checked. (e.g. Monday, Tuesday, Wednesday ... and so on ...)
     * @param {DOMEvent} e          Reference to the DOM event being sent
     * @param {bool} isChecked      The current state of the checkbox
     *
     * @private
     */
    _handleOnCheck: function (day, e, isInputChecked) {
        var bitmask = parseInt(e.target.value);
        this.props.recurrencePattern.setDayOfWeek(bitmask, isInputChecked);
    },

    /**
     * Handles the blur event on the interval input
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @private
     */
    _handleInputBlur: function (e) {
        this.props.recurrencePattern.interval = e.target.value;
    }
});

module.exports = Weekly;
