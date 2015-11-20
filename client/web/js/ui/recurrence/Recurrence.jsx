/**
 * Recurrence component.
 *
 * This will display the different recurrence pattern types.
 * The pattern display will depend on what type is currently selected.
 * The props data will be stored in the state.data while the pattern data will be stored in state.patternData
 * To get the pattern data, we will be calling the private function _getPatternData().
 * Each recurrence pattern sub component will have a public function ::getData().
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
        data: React.PropTypes.object,
        dateToday: React.PropTypes.string,
        displayType: React.PropTypes.string,
        dayOfWeek: React.PropTypes.array.isRequired,
        instance: React.PropTypes.array.isRequired,
        onSave: React.PropTypes.func,
        onNavBackBtnClick: React.PropTypes.func
    },

    getDefaultProps: function () {
        var data = {
            type: 0,
            dateEnd: null,
            typeIndex: 0
        }

        return {
            displayType: 'page',
            data: data
        }
    },

    getInitialState: function () {

        // Return the initial state
        return {
            recurrenceType: this.props.data.type,
            recurrenceIndex: this.props.data.typeIndex,
            patternData: [],
            data: this.props.data
        };
    },

    componentDidMount: function () {
        this._setDateData();
    },

    componentDidUpdate: function () {
        if (this.props.getData) {
            this.props.onGetData('data');
        }

        this._setDateData();
    },

    render: function () {
        var neverEnds = true;
        var displayCancel = null;
        var displayEndDate = null;
        var displayPattern = this._handleDisplayRecurrenceType(this.state.recurrenceType);

        // If the display if NOT from dialog, then lets display the cancel button
        if (this.props.displayType != 'dialog') {
            displayCancel = (<FlatButton label='Cancel' onClick={this._handleBackButton}/>);
        }

        // Display the end date input
        if (this.state.data.dateEnd) {

            neverEnds = false;

            displayEndDate = (<TextField
                ref='inputDateEnd'
                hintText="End Date"/>);
        }

        return (
            <div>
                <div className='recurrence'>
                    <fieldset>
                        <legend>Recurrence Pattern</legend>
                        <DropDownMenu
                            selectedIndex={parseInt(this.state.recurrenceIndex)}
                            menuItems={recurrenceType}
                            onChange={this._handleRecurrenceChange}/>

                        <div className='recurrence-pattern'>
                            {displayPattern}
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Range of Recurrence</legend>
                        <TextField
                            ref='inputDateStart'
                            hintText="Start Date"/>

                        {displayEndDate}

                        <Checkbox
                            ref="neverEnds"
                            value="default"
                            label="Never Ends"
                            defaultSwitched={neverEnds}
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
        var data = this.state.data;
        data.dateEnd = isChecked ? null : this.props.dateToday;

        this.setState({data: data})
    },

    /**
     * Handles the save button. Passes the recurrence data to the entity object
     *
     * @private
     */
    _handleSaveButton: function () {
        var data = this._getPatternData() || this.state.data;

        data.dateStart = this.refs.inputDateStart.getValue();

        if (this.refs.inputDateEnd) {
            data.dateEnd = this.refs.inputDateEnd.getValue();
        } else {
            data.dateEnd = '';
        }

        if (this.props.onSave) {
            this.props.onSave(data);
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

        /*
         * When changing the type of recurrence, lets save the current recurrence type data to the state
         * So if the user decides to select back this recurrence type
         * We can just display its saved data instead of using the default values
         * This will only apply to Daily and Weekly since they are not using state data
         */
        var patternData = this.state.patternData;

        if (this.state.recurrenceType <= 2) {
            patternData[this.state.recurrenceType] = this._getPatternData();
        }

        this.setState({
            recurrenceType: menuItem.value,
            recurrenceIndex: key,
            patternData: patternData
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

        // If recurrence data is set, then lets use that data to set the default values
        if (this.state.data && this.state.data.type == type) {
            data = Object.create(this.state.data);
        } else if (this.state.patternData[type]) {
            data = this.state.patternData[type];
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
            case '3': // Monthly
            case '4': // Monthly Nth
            case 'm':
                displayPattern = (
                    <Monthly
                        ref={ref}
                        dayOfWeek={this.props.dayOfWeek}
                        instance={this.props.instance}
                        data={data}/>
                );
                break;
            case '5': // Yearly
            case '6': // Yearly Nth
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

    /**
     * Get the recurrence pattern data
     *
     * @private
     */
    _getPatternData: function () {
        var data = null;
        var ref = 'recurrence' + this.state.recurrenceType;

        // Check first we have already rendered the recurrence type before we try to get its data
        if (this.refs[ref]) {
            data = this.refs[ref].getData();
        }

        return data;
    },

    /**
     * Set the input date data.
     *
     * @private
     */
    _setDateData: function () {
        if (this.refs.inputDateStart) {
            var dateStart = this.state.data.dateStart || this.props.dateToday;
            this.refs.inputDateStart.setValue(dateStart);
        }

        if (this.refs.inputDateEnd) {
            var dateEnd = this.state.data.dateEnd || this.props.dateToday;
            this.refs.inputDateEnd.setValue(dateEnd);
        }
    }


});

module.exports = Recurrence;
