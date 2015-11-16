/**
 * Yearly component.
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

var typeYearly = '5';
var typeYearNth = '6';

var Yearly = React.createClass({

    propTypes: {
        data: React.PropTypes.object,
        instance: React.PropTypes.array.isRequired,
        dayOfWeek: React.PropTypes.array.isRequired,
        months: React.PropTypes.array.isRequired,
        dayOfWeek: React.PropTypes.array.isRequired
    },

    getDefaultProps: function () {
        var data = {
            type: typeYearly,
            monthOfYear: 1,
            dayOfMonth: 1,
            instance: 1,
            monthOfYearNth: 1,
            dayOfWeek: 1
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

        if (this.state.data.type == typeYearly) {
            displayType = (
                <div className='row'>
                    <div className="col-small-1 recurrence-label">
                        <label>Every</label>
                    </div>
                    <div className="col-small-3">
                        <DropDownMenu
                            selectedIndex={this.props.data.month - 1}
                            onChange={this._handleDropDownChange.bind(this, 'monthOfYear')}
                            menuItems={this.props.months}/>
                    </div>
                    <div className="col-small-1">
                        <TextField
                            className='recurrence-input'
                            ref='inputDayOfMonth'/>
                    </div>
                </div>
            );
        } else {
            displayType = (
                <div className='row'>
                    <div className="col-small-6">
                        <DropDownMenu
                            selectedIndex={this.props.data.instance - 1}
                            onChange={this._handleDropDownChange.bind(this, 'instance')}
                            menuItems={this.props.instance}/>
                        <DropDownMenu
                            selectedIndex={this.props.data.dayOfWeek - 1}
                            onChange={this._handleDropDownChange.bind(this, 'dayOfWeek')}
                            menuItems={this.props.dayOfWeek}/>
                    </div>
                    <div className="col-small-1 recurrence-label">
                        <label>of</label>
                    </div>
                    <div className="col-small-3">
                        <DropDownMenu
                            selectedIndex={this.props.data.monthOfYearNth - 1}
                            onChange={this._handleDropDownChange.bind(this, 'monthOfYearNth')}
                            menuItems={this.props.months}/>
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
                        defaultSelected={this.state.data.type}
                        onChange={this._handleTypeChange}>
                        <RadioButton
                            value={typeYearly}
                            label='Yearly'/>

                        <RadioButton
                            value={typeYearNth}
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
         * If the user changes the type of yearly recurrence, the state values are retrieved
         */
        if (this.refs.inputDayOfMonth) {
            data.inputDayOfMonth = this.refs.inputDayOfMonth.getValue();
        }

        this.setState({data: data});
    },

    /**
     * Set the values of the input boxes
     *
     * @private
     */
    _setInputValues: function () {
        if (this.refs.inputDayOfMonth) {
            this.refs.inputDayOfMonth.setValue(this.state.data.dayOfMonth);
        }
    },

    /**
     * Gets the recurrence pattern data set by the user
     *
     * @return {object}
     * @public
     */
    getData: function (isState) {
        var data = {};

        if (isState) {
            data.type = this.state.data.type;

            if (data.type == typeYearly) {
                data.dayOfMonth = this.refs.inputDayOfMonth.getValue();
                data.monthOfYear = this.state.data.monthOfYear;
            } else {
                data.instance = this.state.data.instance;
                data.monthOfYear = this.state.data.monthOfYearNth;
                data['day' + this.state.data.dayOfWeek] = 't';
            }
        } else {
            data = this.state.data;
        }

        return data;
    }
});

module.exports = Yearly;
