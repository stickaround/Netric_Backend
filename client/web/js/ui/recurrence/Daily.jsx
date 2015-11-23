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
        recurrencePattern: React.PropTypes.object.isRequired
    },

    componentDidMount: function () {
        this.refs.inputInterval.setValue(this.props.recurrencePattern.interval);
    },

    render: function () {
        return (
            <div>
                <label>Every </label>
                <TextField
                    className='recurrence-input'
                    ref='inputInterval'
                    onBlur={this._handleInputBlur}/>
                <label> days</label>
            </div>
        );
    },

    /**
     * Handles the blur event on the interval input
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @private
     */
    _handleInputBlur: function (e) {
        this.props.recurrencePattern.interval = e.target.value;
    },
});

module.exports = Daily;
