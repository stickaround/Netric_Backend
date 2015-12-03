/**
 * Monthly recurrence pattern.
 * This will display the Monthly and MonthNth type.
 * Monthly will accept dayOfMonth and interval input values.
 * MonthlyNth will accept instance, dayOfWeek, and interval input values.
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var TextField = Chamel.TextField;
var RadioButton = Chamel.RadioButton;
var DropDownMenu = Chamel.DropDownMenu;
var RadioButtonGroup = Chamel.RadioButtonGroup;

var Monthly = React.createClass({

    propTypes: {
        onTypeChange: React.PropTypes.func,
        recurrenceTypes: React.PropTypes.object,
        recurrencePattern: React.PropTypes.object.isRequired
    },

    componentDidMount: function () {
        this._setInputValues();
    },

    componentDidUpdate: function () {
        this._setInputValues();
    },

    render: function () {
        var displayType = null;

        if (this.props.recurrencePattern.type == this.props.recurrenceTypes.MONTHLY) {
            displayType = (
                <div className="row">
                    <div className="col-small-5">
                        <label>Day</label>
                        <TextField
                            className='recurrence-input'
                            ref='inputDayOfMonth'
                            onBlur={this._handleInputBlur}/>
                        <label>of every</label>
                        <TextField
                            className='recurrence-input'
                            ref='inputIntervalMonth'
                            onBlur={this._handleInputBlur}/>
                        <label>Month(s)</label>
                    </div>
                </div>
            );
        } else {

            var dayOfWeekMask = this.props.recurrencePattern.dayOfWeekMask;
            var maskIndex = this.props.recurrencePattern.getBitMaskIndex(dayOfWeekMask);

            displayType = (
                <div className="row">
                    <div className="col-small-6">
                        <DropDownMenu
                            onChange={this._handleDropDownChange.bind(this, 'instance')}
                            selectedIndex={this.props.recurrencePattern.instance - 1}
                            menuItems={this.props.recurrencePattern.getInstance()}/>
                        <DropDownMenu
                            onChange={this._handleDropDownChange.bind(this, 'dayOfWeekMask')}
                            selectedIndex={maskIndex}
                            menuItems={this.props.recurrencePattern.getDayOfWeekMenuData()}/>
                    </div>
                    <div>
                        <label>of every</label>
                        <TextField
                            className='recurrence-input'
                            ref='inputIntervalNth'
                            onBlur={this._handleInputBlur}/>
                        <label>Month(s)</label>
                    </div>
                </div>
            );
        }

        return (
            <div>
                <div>
                    <RadioButtonGroup
                        className='row'
                        name='inputMonthly'
                        ref='radioType'
                        defaultSelected={this.props.recurrencePattern.type}
                        onChange={this._handleTypeChange}>
                        <RadioButton
                            className='col-small-2'
                            value={this.props.recurrenceTypes.MONTHLY}
                            label='Monthly'/>

                        <RadioButton
                            className='col-small-3'
                            value={this.props.recurrenceTypes.MONTHNTH}
                            label='Month-nth'/>
                    </RadioButtonGroup>
                </div>
                {displayType}
            </div>
        );
    },

    /**
     * Callback used to handle the changing of month pattern type
     *
     * @param {DOMEvent} e              Reference to the DOM event being sent
     * @param {string} newSelection     The new selected value
     * @private
     */
    _handleTypeChange: function (e, newSelection) {
        if (this.props.onTypeChange) {
            var objSelected = {value: newSelection};
            this.props.onTypeChange(e, null, objSelected);
        }
    },

    /**
     * Handles the blur event on the input texts
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @private
     */
    _handleInputBlur: function (type, e) {
        if (this.refs.inputDayOfMonth) {
            this.props.recurrencePattern.dayOfMonth = this.refs.inputDayOfMonth.getValue();
            this.props.recurrencePattern.interval = this.refs.inputIntervalMonth.getValue();
        } else if (this.refs.inputIntervalNth) {
            this.props.recurrencePattern.interval = this.refs.inputIntervalNth.getValue();
        }
    },

    /**
     * Callback used to handle the changing of dropdown menus
     *
     * @param {string} type         Type of dropdown menu that was changed
     * @param {DOMEvent} e          Reference to the DOM event being sent
     * @param {int} key             The index of the menu clicked
     * @param {array} menuItem      The object value of the menu clicked
     * @private
     */
    _handleDropDownChange: function (type, e, key, menuItem) {
        this.props.recurrencePattern[type] = menuItem.value;
    },

    /**
     * Set the values of the input boxes
     *
     * @private
     */
    _setInputValues: function () {
        if (this.refs.inputDayOfMonth) {
            this.refs.inputDayOfMonth.setValue(this.props.recurrencePattern.dayOfMonth);
            this.refs.inputIntervalMonth.setValue(this.props.recurrencePattern.interval);
        } else if (this.refs.inputIntervalNth) {
            this.refs.inputIntervalNth.setValue(this.props.recurrencePattern.interval);
        }
    }
});

module.exports = Monthly;
