/**
 * Monthly component.
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

var occurenceMenu = [
    {key: '1', text: 'The First'},
    {key: '2', text: 'The Second'},
    {key: '3', text: 'The Third'},
    {key: '4', text: 'The Fourt'},
    {key: '5', text: 'The Last'},
];

var typeMonthly = '3';
var typeMonthNth = '4';


var Monthly = React.createClass({

    propTypes: {
        data: React.PropTypes.object,
        dayOfWeek: React.PropTypes.array.isRequired,
        instance: React.PropTypes.array.isRequired
    },

    getDefaultProps: function () {
        var data = {
            type: typeMonthly,
            day: 1,
            interval: 1,
            nthOccurrence: 1,
            nthDayOfWeek: 1,
            nthInterval: 1
        };

        return {
            data: data
        }
    },

    getInitialState: function () {

        // Return the initial state
        return {
            data: this.props.data
        };
    },

    componentDidMount: function () {
        this._setInputValues();
    },

    componentDidUpdate: function () {
        this._setInputValues();
    },

    render: function () {
        var displayType = null;

        if (this.state.data.type == typeMonthly) {
            displayType = (
                <div className="row">
                    <div className="col-small-5">
                        <label>Day</label>
                        <TextField
                            className='recurrence-input'
                            ref='inputDay'/>
                        <label>of every</label>
                        <TextField
                            className='recurrence-input'
                            ref='inputDayInterval'/>
                        <label>Month(s)</label>
                    </div>
                </div>
            );
        } else {
            displayType = (
                <div className="row">
                    <div className="col-small-6">
                        <DropDownMenu
                            onChange={this._handleDropDownChange.bind(this, 'nthOccurrence')}
                            selectedIndex={this.props.data.nthOccurrence - 1}
                            menuItems={this.props.instance}/>
                        <DropDownMenu
                            onChange={this._handleDropDownChange.bind(this, 'nthDayOfWeek')}
                            selectedIndex={this.props.data.nthDayOfWeek - 1}
                            menuItems={this.props.dayOfWeek}/>
                    </div>
                    <div>
                        <label>of every</label>
                        <TextField
                            className='recurrence-input'
                            ref='inputNthInterval'/>
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
                        defaultSelected={this.state.data.type}
                        onChange={this._handleTypeChange}>
                        <RadioButton
                            className='col-small-2'
                            value={typeMonthly}
                            label='Monthly'/>

                        <RadioButton
                            className='col-small-3'
                            value={typeMonthNth}
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
        var data = this.state.data;
        data.type = newSelection;

        this._setStateData(data);
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
        var data = this.state.data;
        data[type] = menuItem.key;

        this._setStateData(data);
    },

    /**
     * Saves the data into the state
     *
     * @param {object} data     Collection of data that will be stored in the state
     * @private
     */
    _setStateData: function (data) {

        /*
         * Lets save the input values in the state to be retrieved later.
         * If the user changes the type of monthly recurrence, the state values are retrieved
         */
        if (this.refs.inputDay) {
            data.day = this.refs.inputDay.getValue();
            data.interval = this.refs.inputDayInterval.getValue();
        } else if (this.refs.inputMonthInterval) {
            data.nthInterval = this.refs.inputNthInterval.getValue();
        }

        this.setState({data: data});
    },

    /**
     * Set the values of the input boxes
     *
     * @private
     */
    _setInputValues: function () {
        if (this.refs.inputDay) {
            this.refs.inputDay.setValue(this.state.data.day);
            this.refs.inputDayInterval.setValue(this.state.data.interval);
        } else if (this.refs.inputMonthInterval) {
            this.refs.inputNthInterval.setValue(this.state.data.nthInterval);
        }
    },

    /**
     * Gets the recurrence pattern data set by the user
     *
     * @return {object}
     * @public
     */
    getData: function () {
        var data = {type: this.state.data.type};

        if (data.type == typeMonthly) {
            data.day = this.refs.inputDay.getValue();
            data.interval = this.refs.inputDayInterval.getValue();
        } else {
            data.nthDayOfWeek = this.state.data.nthDayOfWeek;
            data.nthOccurrence = this.state.data.nthOccurrence;
            data.nthInterval = this.refs.inputNthInterval.getValue();
        }

        return data;
    }
});

module.exports = Monthly;
