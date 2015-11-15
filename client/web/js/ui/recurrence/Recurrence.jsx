/**
 * Recurrence component.
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var Chamel = require('chamel');
var IconButton = Chamel.IconButton;
var FlatButton = Chamel.FlatButton;
var DropDownMenu = Chamel.DropDownMenu;
var TextField = Chamel.TextField;
var Checkbox = Chamel.Checkbox;

// Recurrence Types
var Daily = require('./Daily.jsx');
var Weekly = require('./Weekly.jsx');
var Monthly = require('./Monthly.jsx');
var Yearly = require('./Yearly.jsx');

var recurrenceType = [
    {value: '0', text: 'Does Not Repeat'},
    {value: '1', text: 'Daily'},
    {value: '2', text: 'Weekly'},
    {value: 'm', text: 'Monthly'},
    {value: 'y', text: 'Yearly'},
];

var Recurrence = React.createClass({

    propTypes: {
        displayType: React.PropTypes.string,
        dayOfWeek: React.PropTypes.array.isRequired,
        instance: React.PropTypes.array.isRequired,
        onGetData: React.PropTypes.func,
        _handleBackButton: React.PropTypes.func
    },

    getDefaultProps: function () {
        displayType: 'page'
    },

    getInitialState: function () {

        // Return the initial state
        return {
            recurrenceType: 0,
            recurrenceIndex: 0,
            recurrenceData: []
        };
    },

    componentDidUpdate: function () {
        if (this.props.getData) {
            this.props.onGetData('data');
        }
    },

    render: function () {
        var displayCancel = null;
        var displayPattern = this._handleDisplayRecurrenceType(this.state.recurrenceType);

        if (this.props.displayType != 'dialog') {
            displayCancel = (<FlatButton label='Cancel' onClick={this._handleBackButton}/>);
        }

        return (
            <div>
                <div className='recurrence'>
                    <fieldset>
                        <legend>Recurrence Pattern</legend>
                        <DropDownMenu
                            selectedIndex={this.state.recurrenceIndex}
                            menuItems={recurrenceType}
                            onChange={this._handleRecurrenceChange}/>

                        <div className='recurrence-pattern'>
                            {displayPattern}
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Range of Recurrence</legend>
                        <TextField
                            ref='inputStartDate'
                            hintText="Start Date"/>

                        <TextField
                            ref='inputEndDate'
                            hintText="End Date"/>

                        <Checkbox
                            ref="neverEnds"
                            value="default"
                            label="Never Ends"
                            defaultSwitched={true}/>

                    </fieldset>
                </div>
                <div>
                    <FlatButton label='Save' onClick={this._handleSaveButton}/>
                    {displayCancel}
                </div>
            </div>
        );
    },

    /**
     * Closes the dialog window or goes back to the previous page
     *
     * @private
     */
    _handleBackButton: function () {
        if (this.props.onNavBackBtnClick) this.props.onNavBackBtnClick();
    },

    /**
     * Handles the save button. Passes the recurrence data to the entity object
     *
     * @private
     */
    _handleSaveButton: function () {
        var data = this._getRecurrenceData();

        console.log(data);
    },

    /**
     * Callback used to handle the changing of recurrence
     *
     * @param {DOMEvent} e                  Reference to the DOM event being sent
     * @param {int} key                     The index of the menu clicked
     * @param {array} menuItem              The object value of the menu clicked
     *
     * @private
     */
    _handleRecurrenceChange: function (e, key, menuItem) {

        /*
         * When changing the type of recurrence, lets save the current recurrence type data to the state
         * So if the user decides to select back this recurrence type
         * We can just display its saved data instead of using the default values
         */
        var data = this.state.recurrenceData;
        data[this.state.recurrenceType] = this._getRecurrenceData();

        this.setState({
            recurrenceType: menuItem.value,
            recurrenceIndex: key,
            recurrenceData: data
        });
    },

    /**
     * Determine what type of recurrence to display
     *
     * @param {string} type                 Type of recurrence to be displayed
     * @return {RecurrenceSubComponent}     Returns the react component to display depending on the type argument
     * @private
     */
    _handleDisplayRecurrenceType: function (type) {

        var data = undefined; // Lets set the data undefined as default, so sub types components can assign the default props
        var displayPattern = null;
        var ref = 'recurrence' + type;

        console.log(this.state.recurrenceData)

        // If recurrence data is set, then lets use that data to set the default values
        if (this.state.recurrenceData[type]) {
            data = this.state.recurrenceData[type];
        }

        switch (type.toString()) {
            case '1': // Daily
                displayPattern = (
                    <Daily
                        ref={ref}
                        data={data}/>
                );
                break;
            case '2': // Weekly
                displayPattern = (
                    <Weekly
                        ref={ref}
                        dayOfWeek={this.props.dayOfWeek}
                        data={data}/>
                );
                break;
            case 'm':
                displayPattern = (
                    <Monthly
                        ref={ref}
                        dayOfWeek={this.props.dayOfWeek}
                        instance={this.props.instance}
                        data={data}/>
                );
                break;
            case 'y':
                displayPattern = (<Yearly
                    ref={ref}
                    dayOfWeek={this.props.dayOfWeek}
                    instance={this.props.instance}
                    months={this.props.months}
                    data={data}/>);
                break;
            default: // Does not repeat
                break;
        }

        return displayPattern;
    },

    _getRecurrenceData: function () {
        var data = null;
        var ref = 'recurrence' + this.state.recurrenceType;

        // Check first we have already rendered the recurrence type before we try to get its data
        if(this.refs[ref]) {
            data = this.refs[ref].getData();
        }

        return data;
    }


});

module.exports = Recurrence;
