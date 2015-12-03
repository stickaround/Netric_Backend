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
        var dayOfWeekData = this.props.recurrencePattern.getDayOfWeek();
        var dayOfWeek = this.props.recurrencePattern.dayOfWeek;

        console.log(dayOfWeek);

        for (var day in dayOfWeekData) {
            var bitmask = dayOfWeekData[day];
            var ref = 'dayOfWeek' + day;
            var checked = false;
            if (dayOfWeek && dayOfWeek[day] == bitmask) {
                checked = true;
            }

            displayDays.push(<Checkbox
                key={day}
                value={bitmask}
                ref={ref}
                label={day}
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

        if(isInputChecked) {
            this.props.recurrencePattern.dayOfWeek[day] = e.target.value;
        } else {
            this.props.recurrencePattern.dayOfWeek[day] = 0;
        }
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
