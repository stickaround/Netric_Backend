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
var AppBar = Chamel.AppBar;

// Recurrence Pattern Components
var DailyComponent = require('./Daily.jsx');
var WeeklyComponent = require('./Weekly.jsx');
var MonthlyComponent = require('./Monthly.jsx');
var YearlyComponent = require('./Yearly.jsx');

var Recurrence = React.createClass({

    propTypes: {
        recurrencePattern: React.PropTypes.object.isRequired,
        onSave: React.PropTypes.func,
        onNavBtnClick: React.PropTypes.func,
        hideToolbar: React.PropTypes.bool
    },

    getDefaultProps: function () {
        return {
            recurrenceIndex: 0
        }
    },

    getInitialState: function () {
        var neverEnds = true;
        var recurPatterns = [];
        var recurrencePattern = this.props.recurrencePattern;
        var recurrenceType = this.props.recurrencePattern.type;

        recurPatterns[recurrenceType] = recurrencePattern;

        if (recurrencePattern.dateEnd) {
            neverEnds = false;
        }

        // Return the initial state
        return {
            recurrenceType: recurrenceType,
            recurrencePattern: recurrencePattern,
            recurrenceIndex: recurrencePattern.getRecurrenceTypeOffset(),
            recurPatterns: recurPatterns,
            neverEnds: neverEnds
        };
    },

    render: function () {
        var displayCancel = null;
        var displayEndDate = null;
        var displayPattern = this._handleDisplayRecurrenceType(this.state.recurrenceType);

        // Display the end date input
        if (!this.state.neverEnds) {
            displayEndDate = (
                <DatePicker
                    floatingLabelText='End Date'
                    value={this.props.recurrencePattern.getDateEnd()}
                    type="date"
                    onChange={this._handleDateChange.bind(this, 'dateEnd')}/>
            );
        }

        var toolBar = null;
        if (!this.props.hideToolbar) {
            var elementLeft = (
                <IconButton
                    iconClassName="fa fa-arrow-left"
                    onClick={this._handleBackButtonClicked}
                    />
            );

            toolBar = (
                <AppBar
                    iconElementLeft={elementLeft}
                    title="Recurrence">
                </AppBar>
            );
        }

        return (
            <div>
                {toolBar}
                <div className='recurrence'>
                    <fieldset>
                        <legend>Recurrence Pattern</legend>
                        <DropDownMenu
                            selectedIndex={this.state.recurrenceIndex}
                            menuItems={this.props.recurrencePattern.getTypeMenuData()}
                            onChange={this._handleRecurrenceChange}/>

                        <div className='recurrence-pattern'>
                            {displayPattern}
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Range of Recurrence</legend>

                        <DatePicker
                            floatingLabelText='Start Date'
                            value={this.props.recurrencePattern.getDateStart()}
                            type="date"
                            onChange={this._handleDateChange.bind(this, 'dateStart')}/>

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
     * Respond when the user clicks the back button
     *
     * @param evt
     * @private
     */
    _handleBackButtonClicked: function (evt) {
        if (this.props.onNavBtnClick) this.props.onNavBtnClick();
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
     * Handles the changing of dates
     *
     * @param {string} dateType     Type of date to be changed (either dateStart or dateEnd)
     * @param {DOMEvent} evt        Reference to the DOM event being sent
     * @param {date} date           The date that was set by the user
     * @private
     */
    _handleDateChange: function (dateType, evt, date) {
        this.state.recurPatterns[this.state.recurrenceType][dateType] = date;
    },

    /**
     * Handles the save button. Passes the recurrence data to the entity object
     *
     * @private
     */
    _handleSaveButton: function () {
        var recurrencePattern = this.state.recurPatterns[this.state.recurrenceType];

        if (this.state.neverEnds) {
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
        if (key == null) {
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
});

module.exports = Recurrence;
