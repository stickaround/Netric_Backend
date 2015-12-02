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
        var dayOfWeek = this.props.recurrencePattern.getDayOfWeek();

        for (var idx in dayOfWeek) {
            var day = dayOfWeek[idx];
            var ref = 'dayOfWeek' + idx;
            var checked = false;
            if (this.props.recurrencePattern.dayOfWeek[idx]) {
                checked = true;
            }

            displayDays.push(<Checkbox
                key={idx}
                value={day.value}
                ref={ref}
                label={day.text}
                onCheck={this._handleOnCheck.bind(this, idx)}
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
     * @param {int} indes           The index of the weekOfDay
     * @param {DOMEvent} e          Reference to the DOM event being sent
     * @param {bool} isChecked      The current state of the checkbox
     *
     * @private
     */
    _handleOnCheck: function (index, e, isInputChecked) {

        if(isInputChecked) {
            this.props.recurrencePattern.dayOfWeek[index] = e.target.value;
        } else {
            this.props.recurrencePattern.dayOfWeek[index] = null;
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
