/**
 * Recurrence component.
 *
 * This will display the different recurrence pattern types.
 * The recurrence pattern will depend on what type is currently selected.
 *
 * DailyComponent will display the input for interval per day.
 * WeeklyComponent will display the input for interval per day and checkbox selection for days of week.
 *
 * MonthlyComponent will have 2 types (Monthly and MonthNth).
 * Monthly will display the inputs for interval per month and the day of the month.
 * MonthNth will display the dropdown for instance and day of week, and input for interval per month.
 *
 * YearlyComponent will have 2 types (Yearly and YearNth).
 * Yearly will display the dropdowns for month of year and input for day of the month.
 * YearNth will display the dropdowns for instance, day of the week, and  month of year.
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
var DatePicker = Chamel.DatePicker;

// Recurrence Pattern Components
var DailyComponent = require('./Daily.jsx');
var WeeklyComponent = require('./Weekly.jsx');
var MonthlyComponent = require('./Monthly.jsx');
var YearlyComponent = require('./Yearly.jsx');

var Recurrence = React.createClass({

    propTypes: {
        recurrencePattern: React.PropTypes.object.isRequired,
        dateToday: React.PropTypes.string,
        displayType: React.PropTypes.string,
        onSave: React.PropTypes.func,
        onNavBackBtnClick: React.PropTypes.func,
    },

    getDefaultProps: function () {
        return {
            displayType: 'page',
            recurrenceIndex: 0
        }
    },

    getInitialState: function () {

        var neverEnds = true;
        var recurPatterns = [];
        var recurrencePattern = this.props.recurrencePattern;
        var recurrenceType = this.props.recurrencePattern.type;

        recurPatterns[recurrenceType] = recurrencePattern;

        if (recurrencePattern.dateEnds) {
            neverEnds = false;
        }

        // Return the initial state
        return {
            recurrenceType: recurrenceType,
            recurrencePattern: recurrencePattern,
            recurrenceIndex: recurrencePattern.getRecurrenceIndex(),
            recurPatterns: recurPatterns,
            neverEnds: neverEnds
        };
    },

    componentDidMount: function () {
        this._setDateData();
    },

    componentDidUpdate: function () {
        this._setDateData();
    },

    render: function () {
        var displayCancel = null;
        var displayEndDate = null;
        var displayPattern = this._handleDisplayRecurrenceType(this.state.recurrenceType);

        // If the display if NOT from dialog, then lets display the cancel button
        if (this.props.displayType != 'dialog') {
            displayCancel = (<FlatButton label='Cancel' onClick={this._handleBackButton}/>);
        }

        // Display the end date input
        if (!this.state.neverEnds) {
            displayEndDate = (
                <TextField
                    ref='inputDateEnd'
                    hintText="End Date"
                    onBlur={this._handleDateBlur.bind(this, 'dateEnd')}/>
            );
        }

        return (
            <div>
                <div className='recurrence'>
                    <fieldset>
                        <legend>Recurrence Pattern</legend>
                        <DropDownMenu
                            selectedIndex={this.state.recurrenceIndex}
                            menuItems={this.props.recurrencePattern.getTypeMenu()}
                            onChange={this._handleRecurrenceChange}/>

                        <div className='recurrence-pattern'>
                            {displayPattern}
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Range of Recurrence</legend>
                        <TextField
                            ref='inputDateStart'
                            hintText="Start Date"
                            onBlur={this._handleDateBlur.bind(this, 'dateStart')}/>

                        {displayEndDate}

                        <Checkbox
                            ref="neverEnds"
                            value="default"
                            label="Never Ends"
                            defaultSwitched={this.state.neverEnds}
                            onCheck={this._handleNeverEnds}/>
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
     * Handles the never ends check box and updates the state with the changes
     *
     * @param {DOMEvent} e          Reference to the DOM event being sent
     * @param {bool} isChecked      The current state of the checkbox
     *
     * @private
     */
    _handleNeverEnds: function (e, isChecked) {
        this.setState({neverEnds: isChecked})
    },

    /**
     * Handles the save button. Passes the recurrence data to the entity object
     *
     * @private
     */
    _handleSaveButton: function () {
        var recurrencePattern = this.state.recurPatterns[this.state.recurrenceType];

        recurrencePattern.dateStart = this.refs.inputDateStart.getValue();

        if (this.refs.inputDateEnd) {
            recurrencePattern.dateEnd = this.refs.inputDateEnd.getValue();
        } else {
            recurrencePattern.dateEnd = '';
        }

        if (this.props.onSave) {
            this.props.onSave(recurrencePattern);
        }
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
        if (!key) {
            key = this.state.recurrenceIndex;
        }

        this.setState({
            recurrenceType: menuItem.value,
            recurrenceIndex: key
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

        var recurrenceTypes = this.props.recurrencePattern.getRecurrenceTypes();
        var recurrencePattern = null;
        var displayPattern = null;
        var ref = 'recurrence' + type;

        // If recurrence data is set for this type, then lets use it to load the values for the pattern
        if (this.state.recurPatterns[type]) {
            recurrencePattern = this.state.recurPatterns[type];
        } else {
            recurrencePattern = Object.create(this.state.recurrencePattern);
            recurrencePattern.type = type;
            recurrencePattern.reset(); // Reset the existing values and set to default values
            recurrencePattern.setDefaultValues(); // Set the default values based on the type of recurrence

            this.state.recurPatterns[type] = recurrencePattern;
        }

        switch (type.toString()) {
            case recurrenceTypes.DAILY:
                displayPattern = (
                    <DailyComponent
                        ref={ref}
                        recurrencePattern={recurrencePattern}/>
                );
                break;
            case recurrenceTypes.WEEKLY:
                displayPattern = (
                    <WeeklyComponent
                        ref={ref}
                        recurrencePattern={recurrencePattern}/>
                );
                break;
            case recurrenceTypes.MONTHLY:
            case recurrenceTypes.MONTHNTH:
                displayPattern = (
                    <MonthlyComponent
                        ref={ref}
                        recurrencePattern={recurrencePattern}
                        recurrenceTypes={recurrenceTypes}
                        onTypeChange={this._handleRecurrenceChange}/>
                );
                break;
            case recurrenceTypes.YEARLY:
            case recurrenceTypes.YEARNTH:
                displayPattern = (
                    <YearlyComponent
                        ref={ref}
                        recurrencePattern={recurrencePattern}
                        recurrenceTypes={recurrenceTypes}
                        onTypeChange={this._handleRecurrenceChange}/>
                );
                break;
            default: // Does not repeat
                break;
        }

        return displayPattern;
    },

    /**
     * Handles the blur event on the input dates
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @private
     */
    _handleDateBlur: function (dateType, e) {
        this.props.recurrencePattern[dateType] = e.target.value;
    },

    /**
     * Set the input date data.
     *
     * @private
     */
    _setDateData: function () {
        if (this.refs.inputDateStart) {
            this.refs.inputDateStart.setValue(this.props.recurrencePattern.getDateStart());
        }

        if (this.refs.inputDateEnd) {
            this.refs.inputDateEnd.setValue(this.props.recurrencePattern.getDateStart());
        }
    }


});

module.exports = Recurrence;
