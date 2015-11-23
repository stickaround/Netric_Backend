/**
 * Yearly recurrence pattern.
 * This will display the Yearly and YearNth type.
 * Yearly will accept monthOfYear and dayOfMonth input values.
 * YearlyNth will accept instance, monthOfYear, and dayOfWeek input values.
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

var Yearly = React.createClass({

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

        if (this.props.recurrencePattern.type == this.props.recurrenceTypes.YEARLY) {

            displayType = (
                <div className='row'>
                    <div className="col-small-1 recurrence-label">
                        <label>Every</label>
                    </div>
                    <div className="col-small-3">
                        <DropDownMenu
                            selectedIndex={this.props.recurrencePattern.monthOfYear - 1}
                            onChange={this._handleDropDownChange.bind(this, 'monthOfYear')}
                            menuItems={this.props.recurrencePattern.getMonths()}/>
                    </div>
                    <div className="col-small-1">
                        <TextField
                            className='recurrence-input'
                            ref='inputDayOfMonth'
                            onBlur={this._handleInputBlur}/>
                    </div>
                </div>
            );
        } else {

            var dayOfWeek = this.props.recurrencePattern.dayOfWeek;
            var dayOfWeekIndex = this.props.recurrencePattern.getBitMaskIndex(dayOfWeek);

            displayType = (
                <div className='row'>
                    <div className="col-small-6">
                        <DropDownMenu
                            selectedIndex={this.props.recurrencePattern.instance - 1}
                            onChange={this._handleDropDownChange.bind(this, 'instance')}
                            menuItems={this.props.recurrencePattern.getInstance()}/>
                        <DropDownMenu
                            selectedIndex={dayOfWeekIndex}
                            onChange={this._handleDropDownChange.bind(this, 'dayOfWeek')}
                            menuItems={this.props.recurrencePattern.getDayOfWeek()}/>
                    </div>
                    <div className="col-small-1 recurrence-label">
                        <label>of</label>
                    </div>
                    <div className="col-small-3">
                        <DropDownMenu
                            selectedIndex={this.props.recurrencePattern.monthOfYear - 1}
                            onChange={this._handleDropDownChange.bind(this, 'monthOfYear')}
                            menuItems={this.props.recurrencePattern.getMonths()}/>
                    </div>
                </div>
            );
        }

        return (
            <div>
                <div>
                    <RadioButtonGroup
                        className='recurrence-input'
                        name='inputYearly'
                        defaultSelected={this.props.recurrencePattern.type}
                        onChange={this._handleTypeChange}>
                        <RadioButton
                            value={this.props.recurrenceTypes.YEARLY}
                            label='Yearly'/>

                        <RadioButton
                            value={this.props.recurrenceTypes.YEARNTH}
                            label='Year-nth'/>
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
        }
    }
});

module.exports = Yearly;
