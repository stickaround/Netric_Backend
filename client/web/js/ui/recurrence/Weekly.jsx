/**
 * Weekly component.
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
        data: React.PropTypes.object,
        dayOfWeek: React.PropTypes.array.isRequired
    },

    getDefaultProps: function () {
        return {
            data: {
                weekly: 1,
                day: []
            }
        }
    },

    componentDidMount: function () {
        this.refs.inputWeekly.setValue(this.props.data.weekly);
    },

    render: function () {
        var displayDays = []

        for (var idx in this.props.dayOfWeek) {
            var day = this.props.dayOfWeek[idx];
            var ref = 'weeklyDay' + day.key;
            var checked = false;

            if (this.props.data.day[day.key]) {
                checked = true;
            }

            displayDays.push(<Checkbox
                key={day.key}
                value={day.key}
                ref={ref}
                label={day.text}
                defaultSwitched={checked}/>)
        }

        return (
            <div>
                <div>
                    <label>Every </label>
                    <TextField
                        className='recurrence-input'
                        ref='inputWeekly'/>
                    <label> week(s) on:</label>
                </div>
                <div>
                    {displayDays}
                </div>
            </div>
        );
    },

    /**
     * Gets the recurrence pattern data set by the user
     *
     * @return {object}
     * @public
     */
    getData: function () {
        var data = {
            type: 2,
            weekly: this.refs.inputWeekly.getValue(),
            day: []
        }

        for (var idx in this.props.dayOfWeek) {
            var dayIndex = this.props.dayOfWeek[idx].key;
            var ref = 'weeklyDay' + dayIndex;
            var checked = this.refs[ref].isChecked();

            data.day[dayIndex] = checked;
        }

        return data;
    }
});

module.exports = Weekly;
