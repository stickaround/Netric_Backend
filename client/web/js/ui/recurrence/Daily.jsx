/**
 * Daily component.
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
            data: {daily: 1}
        }
    },

    componentDidMount: function () {
        this.refs.inputDaily.setValue(this.props.data.daily);
    },

    render: function () {
        return (
            <div>
                <label>Every </label>
                <TextField
                    className='recurrence-input'
                    ref='inputDaily'/>
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
            daily: this.refs.inputDaily.getValue()
        }

        return data;
    }
});

module.exports = Daily;
