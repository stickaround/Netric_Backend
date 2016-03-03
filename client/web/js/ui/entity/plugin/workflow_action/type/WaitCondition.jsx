/**
 * Handle a wait condition type action
 *
 * All actions have a 'data' field, which is just a JSON encoded string
 * used by the backend when executing the action.
 *
 * When the ActionDetails plugin is rendered it will decode or parse the string
 * and pass it down to the type component.
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var Controls = require('../../../../Controls.jsx');
var TextField = Controls.TextField;
var DropDownMenu = Controls.DropDownMenu;

/**
 * Manage action data for check condition
 */
var WaitCondition = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * Callback to call when a user changes any properties of the action
         */
        onChange: React.PropTypes.func,

        /**
         * Flag indicating if we are in edit mode or view mode
         */
        editMode: React.PropTypes.bool,

        /**
         * The object type this action is running against
         */
        objType: React.PropTypes.string.isRequired,

        /**
         * Data from the action - decoded JSON object
         */
        data: React.PropTypes.object
    },

    /**
     * Render action type form
     *
     * @returns {JSX}
     */
    render: function () {

        let displayWaitCondition = null;

        // Units of time for relative times
        let units = ['Minute', 'Hour', 'Day', 'Week', 'Month', 'Year'];
        let unitsMenuData = [];

        // Variable where we store the human description for the props.data.when_unit
        let waitHumanDesc = null;

        // Loop thru units to create the unitsMenuData to be used in the dropdown
        for (var idx in units) {
            let value = parseInt(idx) + 1;
            let desc = units[idx] + '(s)'

            unitsMenuData.push({
                payload: value,
                text: desc
            });

            if (this.props.data.when_unit == value) {
                waitHumanDesc = desc;
            }
        }

        // If we are on edit mode, then we will display the dropdown and input used to set the wait condition
        if (this.props.editMode) {

            var selectedFieldIndex = (this.props.data.when_unit) ?
                this._getSelectedIndex(unitsMenuData, this.props.data.when_unit) : 0;

            // Setup the wait condition inputs
            displayWaitCondition = (
                <div>
                    <div className="entity-form-field-inline-block">
                        <TextField
                            floatingLabelText='Interval'
                            type="number"
                            defaultValue={this.props.data.when_interval}
                            onBlur={this._handleTextInputChange}/>
                    </div>
                    <div className="entity-form-field-inline-block">
                        <DropDownMenu
                            menuItems={unitsMenuData}
                            selectedIndex={parseInt(selectedFieldIndex)}
                            onChange={this._handleUnitChange}
                        />
                    </div>
                </div>
            );
        } else {

            // If there is no unit/interval set or it if interval is set to 0, then let's just display 'Execute immediately'
            if (!this.props.data.when_unit
                || !this.props.data.when_interval
                || parseInt(this.props.data.when_interval) === 0) {
                waitHumanDesc = 'Execute immediately';
            } else {

                // Prepend the interval to complete the display of wait condition
                waitHumanDesc = this.props.data.when_interval + ' ' + waitHumanDesc;
            }

            // Display the wait condition data
            displayWaitCondition = (
                <div>
                    {waitHumanDesc}
                </div>
            );
        }

        return (
            <div className="entity-form-field">
                <div className="entity-form-field-label">
                    Wait Condition
                </div>
                {displayWaitCondition}
            </div>
        );
    },

    /**
     * When a property changes send an event so it can be handled
     *
     * @param {string} property The name of the property that was changed
     * @param {string|int|Object} value Whatever we set the property to
     * @private
     */
    _handleDataChange: function (property, value) {
        let data = this.props.data;
        data[property] = value;
        if (this.props.onChange) {
            this.props.onChange(data);
        }
    },

    /**
     * Callback used to handle commands when user selects the wait condition unit
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {Object} data The object value of the menu clicked
     * @private
     */
    _handleUnitChange: function (e, key, data) {
        this._handleDataChange('when_unit', data.payload);
    },

    /**
     * Callback used to handle the changing of text input for when_interval
     *
     * @param {string} property The name of the property that was changed
     * @param {DOMEvent} evt Reference to the DOM event being sent
     * @private
     */
    _handleTextInputChange: function (evt) {
        this._handleDataChange('when_interval', evt.target.value);
    },

    /**
     * Gets the index of the unit based on the unit value selected
     *
     * @param {Array} data Array of data that will be mapped to get the index of the saved field/operator/blogic value
     * @param {string} value The value that will be used to get the index
     * @private
     */
    _getSelectedIndex: function (data, value) {
        var index = 0;
        for (var idx in data) {
            if (data[idx].payload == value) {
                index = idx;
                break;
            }
        }
        
        return index;
    }
});

module.exports = WaitCondition;
