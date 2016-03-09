/**
 * Handle a assign type action
 *
 * All actions have a 'data' field, which is just a JSON encoded string
 * used by the backend when executing the action.
 *
 * When the ActionDetails plugin is rendered it will decode or parse the string
 * and pass it down to the type component.
 *
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var Field = require('../../../../../entity/definition/Field.js');
var FieldsDropDown = require("../../../FieldsDropDown.jsx");
var GroupingSelect = require("../../../GroupingSelect.jsx");
var Controls = require('../../../../Controls.jsx');
var TextFieldAutoComplete = require("../../../../mixins/TextFieldAutoComplete.jsx");
var TextField = Controls.TextField;
var RadioButton = Controls.RadioButton;
var RadioButtonGroup = Controls.RadioButtonGroup;

/**
 * Manage action data for assign
 */
var CheckCondition = React.createClass({

    mixins: [TextFieldAutoComplete],

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
     * Get the starting state of this component
     */
    getInitialState: function () {

        // We need to know the type of object we are acting on
        return ({
            type: 'team'
        });
    },

    /**
     * Render action type form
     *
     * @returns {JSX}
     */
    render: function () {

        let displayAssignTo = null,
            displayUserField = null,
            displayAssignField = null;

        // If we are in editMode, then let's display the inputs used for assign workflow action
        if (this.props.editMode) {

            switch (this.state.type) {
                case 'team':
                    displayAssignTo = (
                        <GroupingSelect
                            objType='user'
                            key='team'
                            fieldName='team_id'
                            allowNoSelection={false}
                            label={'none'}
                            selectedValue={this.props.data.team_id}
                            onChange={this._handleGroupingSelect.bind(this, 'team_id')}
                        />
                    );
                    break;
                case 'group':
                    displayAssignTo = (
                        <GroupingSelect
                            objType='user'
                            key='group'
                            fieldName='groups'
                            allowNoSelection={false}
                            label={'none'}
                            selectedValue={this.props.data.group_id}
                            onChange={this._handleGroupingSelect.bind(this, 'group_id')}
                        />
                    );
                    break;
                case 'users':
                    var autoCompleteAttributes = {
                        autoComplete: true,
                        autoCompleteDelimiter: ',',
                        autoCompleteTrigger: '@',
                        autoCompleteTransform: this._handleAutoCompleteTransform,
                        autoCompleteGetData: this.getAutoCompleteData
                    }

                    displayAssignTo = (
                        <TextField
                            {...autoCompleteAttributes}
                            floatingLabelText="Enter user IDs separated by a comma ','. Press '@' to display the list of users as you write their name."
                            ref="usersAssignTo"
                            defaultValue={this.props.data.users}
                            onBlur={this._handleTextInputChange}
                        />
                    );
                    break;
            }

            displayUserField = (
                <FieldsDropDown
                    objType={this.props.objType}
                    firstEntryLabel="Select Field"
                    filterFieldSubtypes={['user']}
                    hideFieldTypes={[Field.types.objectMulti]}
                    selectedField={this.props.data.field}
                    onChange={this._handleMenuSelect}
                />
            );

            displayAssignField = (
                <RadioButtonGroup
                    name='type'
                    defaultSelected={this.state.type}
                    onChange={this._handleTypeChange}
                    inline={true}>
                    <RadioButton
                        value='team'
                        label='Team'
                    />
                    <RadioButton
                        value='group'
                        label='Group'
                    />
                    <RadioButton
                        value='users'
                        label='Users'
                    />
                </RadioButtonGroup>
            );
        } else {
            displayUserField = this.props.data.field;

            if (this.props.data.group_id) {
                displayAssignTo = this.props.data.group_id;
                displayAssignField = "Group";
            } else if (this.props.data.team_id) {
                displayAssignTo = this.props.data.team_id;
                displayAssignField = "Team";
            } else if (this.props.data.users) {
                displayAssignTo = this.props.data.users;
                displayAssignField = "Users";
            }
        }

        return (
            <div className="entity-form-field">
                <div>
                    <div className="entity-form-field-label">
                        User Field
                    </div>
                    <div className="entity-form-field-value">
                        {displayUserField}
                    </div>
                </div>
                <div>
                    <div className="entity-form-field-label">
                        Assign Field
                    </div>
                    <div className="entity-form-field-value">
                        {displayAssignField}
                    </div>
                </div>
                <div>
                    <div className="entity-form-field-label">
                        Assign To
                    </div>
                    <div className="entity-form-field-value">
                        {displayAssignTo}
                    </div>
                </div>
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
     * Callback used to handle the selecting of fieldname in the dropdown menu
     *
     * @param {string} fieldValue The value of the fieldname that was selected
     * @private
     */
    _handleMenuSelect: function (fieldValue) {
        this._handleDataChange('field', fieldValue);
    },

    /**
     * Callback used to handle the changing of assign type
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {string} newSelection The new selected value
     * @private
     */
    _handleTypeChange: function (e, newSelection) {
        this.setState({type: newSelection});
    },

    /**
     * Callback used to handle commands when user selects a value in the dropdown groupings input
     *
     * @param {string} property The name of the property that was changed
     * @param {string} payload The value of the selected menu
     * @param {string} text The text of the selected menu
     * @private
     */
    _handleGroupingSelect: function (property, payload, text) {
        this._handleDataChange(property, payload);
    },

    /**
     * AutoComplete function that will transform the selected data to something else
     *
     * @param {object} data     THe autocomplete selected data
     * @returns {string}
     * @public
     */
    _handleAutoCompleteTransform: function (data) {
        return data.payload;
    }
});

module.exports = CheckCondition;
