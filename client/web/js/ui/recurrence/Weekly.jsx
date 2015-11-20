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
        data: React.PropTypes.object,
        dayOfWeek: React.PropTypes.array.isRequired
    },

    getDefaultProps: function () {
        return {
            data: {
                interval: 1,
                dayOfWeekly: []
            }
        }
    },

    componentDidMount: function () {
        this.refs.inputInterval.setValue(this.props.data.interval);
    },

    render: function () {
        var displayDays = []

        for (var idx in this.props.dayOfWeek) {
            var day = this.props.dayOfWeek[idx];
            var ref = 'dayOfWeek' + day.key;
            var checked = false;

            if (this.props.data.dayOfWeekly[day.key] && this.props.data.dayOfWeekly[day.key] == 't') {
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
                        ref='inputInterval'/>
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
            interval: this.refs.inputInterval.getValue(),
            dayOfWeekly: []
        }

        for (var idx in this.props.dayOfWeek) {
            var dayIndex = this.props.dayOfWeek[idx].key;
            var ref = 'dayOfWeek' + dayIndex;
            var checked = this.refs[ref].isChecked() ? 't' : 'f';

            data.dayOfWeekly[dayIndex] = checked;
        }

        return data;
    }
});

module.exports = Weekly;
