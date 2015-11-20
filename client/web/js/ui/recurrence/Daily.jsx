/**
 * Daily recurrence pattern.
 * This will display the input text for the interval value.
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var TextField = Chamel.TextField;

var Daily = React.createClass({

    propTypes: {
        data: React.PropTypes.object
    },

    getDefaultProps: function () {
        return {
            data: {interval: 1}
        }
    },

    componentDidMount: function () {
        this.refs.inputInterval.setValue(this.props.data.interval);
    },

    render: function () {
        return (
            <div>
                <label>Every </label>
                <TextField
                    className='recurrence-input'
                    ref='inputInterval'/>
                <label> days</label>
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
            type: 1,
            interval: this.refs.inputInterval.getValue()
        }

        return data;
    }
});

module.exports = Daily;
