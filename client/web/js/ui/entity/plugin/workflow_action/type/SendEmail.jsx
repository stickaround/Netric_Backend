/**
 * Handle a send email type action
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
var netric = require("../../../../../base");
var controller = require("../../../../../controller/controller");
var Selector = require("../Selector.jsx");
var Controls = require('../../../../Controls.jsx');
var TextField = Controls.TextField;
var RadioButton = Controls.RadioButton;
var RadioButtonGroup = Controls.RadioButtonGroup;
var FlatButton = Controls.FlatButton;

var emailType = {
    COMPOSE: 1,
    TEMPLATE: 2
}

/**
 * Manage action data for SendEmail
 */
var SendEmail = React.createClass({

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
        return {
            emailType: emailType.COMPOSE
        };
    },

    componentDidUpdate: function () {
        this._setInputValues();
    },

    /**
     * Render action type form
     *
     * @returns {JSX}
     */
    render: function () {
        let additionalSelectorData = [{
            value: 'default',
            text: 'Default'
        }];

        if (this.props.editMode) {

            let displayEmailCompose = null;

            if (emailType.COMPOSE) {
                displayEmailCompose = (
                    <div>
                        <div>
                            <div className="entity-form-field-inline-block">
                                <TextField
                                    floatingLabelText='Subject'
                                    defaultValue={this.props.data.subject}
                                    />
                            </div>
                            <div className="entity-form-field-inline-block">
                                <FlatButton
                                    label='Insert Merge Field'
                                    onClick={this._handleInsertMergeField}
                                    />
                            </div>
                        </div>
                        <div>
                            <TextField
                                floatingLabelText='Body'
                                multiLine={true}
                                defaultValue={this.props.data.body}
                                />
                        </div>
                    </div>
                );
            }

            return (
                <div className="entity-form-field">
                    <div>
                        <div className="entity-form-field-inline-block">
                            <TextField
                                floatingLabelText='From'
                                ref="fromInput"
                                defaultValue={this.props.data.from}
                                />
                        </div>
                        <div className="entity-form-field-inline-block">
                            <Selector
                                objType={this.props.objType}
                                displayType="dropdown"
                                filterBy="subtype"
                                fieldType="user"
                                selectedField={this.props.data.from}
                                additionalMenuData={additionalSelectorData}
                                onChange={this._handleMenuSelect}
                                />
                        </div>
                    </div>
                    <div>
                        <div className="entity-form-field-label">
                            To
                        </div>
                        <div>
                            <Selector
                                objType={this.props.objType}
                                displayType="checkbox"
                                filterBy="subtype"
                                fieldType="user"
                                selectedField={this.props.data.to}
                                onCheck={this._handleCheckboxSelect}
                                />
                            <TextField
                                floatingLabelText='Other email addresses - separate with commas'
                                ref="toEmailOther"
                                defaultValue={this.props.data.to_other}
                                onBlur={this._handleTextInputChange.bind(this, 'to_other')}
                                />
                        </div>
                    </div>
                    <div>
                        <div className="entity-form-field-label">
                            Cc
                        </div>
                        <div>
                            <Selector
                                objType={this.props.objType}
                                displayType="checkbox"
                                filterBy="subtype"
                                fieldType="user"
                                selectedField={this.props.data.cc}
                                onCheck={this._handleCheckboxSelect}
                                />
                            <TextField
                                floatingLabelText='Other email addresses - separate with commas'
                                ref="toEmailOther"
                                defaultValue={this.props.data.cc_other}
                                onBlur={this._handleTextInputChange.bind(this, 'cc_other')}
                                />
                        </div>
                    </div>
                    <div>
                        <div className="entity-form-field-label">
                            Bcc
                        </div>
                        <div>
                            <Selector
                                objType={this.props.objType}
                                displayType="checkbox"
                                filterBy="subtype"
                                fieldType="user"
                                selectedField={this.props.data.bcc}
                                onCheck={this._handleCheckboxSelect}
                                />
                            <TextField
                                floatingLabelText='Other email addresses - separate with commas'
                                ref="toEmailOther"
                                defaultValue={this.props.data.bcc_other}
                                onBlur={this._handleTextInputChange.bind(this, 'bcc_other')}
                                />
                        </div>
                    </div>
                    <div className="entity-form-group">
                        <RadioButtonGroup
                            name='emailType'
                            defaultSelected={this.state.emailType}
                            onChange={this._handleTypeChange}
                            inline={true}>
                            <RadioButton
                                value='1'
                                label='Compose New Email '
                                />
                            <RadioButton
                                value='2'
                                label='Use Email Template'
                                />
                        </RadioButtonGroup>
                    </div>
                    {displayEmailCompose}
                </div>
            );
        } else {

            let displayData = [];

            for (var field in this.props.data) {
                displayData.push(
                    <div>
                        <div className="entity-form-field-label">
                            {field}
                        </div>
                        <div>
                            {this.props.data[field]}
                        </div>
                    </div>
                )
            }

            // If we are not on editMode then lets just display the send email info
            return (
                <div className="entity-form-field">
                    {displayData}
                </div>
            );
        }
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
     * Callback used to handle the changing of compose email type
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {string} newSelection The new selected value
     * @private
     */
    _handleTypeChange: function (e, newSelection) {
        this.setState({emailType: newSelection})
    },

    /**
     * Callback used to handle the changing of text inputs for send email data
     *
     * @param {string} property The name of the property that was changed
     * @param {DOMEvent} evt Reference to the DOM event being sent
     * @private
     */
    _handleTextInputChange: function (property, evt) {
        this._handleDataChange(property, evt.target.value);
    },

    /**
     * Callback used to handle the selecting of user dropdown menu
     *
     * @param {string} fieldValue The value of the field that was selected
     * @private
     */
    _handleMenuSelect: function (fieldValue) {
        if (fieldValue === 'default') {
            this._handleDataChange('from', netric.getApplication().getAccount().getUser().email);
        } else {
            this._handleDataChange('from', fieldValue);
        }
    },

    /**
     * Callback used to handle the inserting of merge field
     *
     * @private
     */
    _handleInsertMergeField: function() {
        var EntityPluginController = require("../../../../../controller/EntityPluginController");
        var entityPlugin = new EntityPluginController();

        entityPlugin.load({
            type: controller.types.DIALOG,
            pluginName: "workflow_action.MergeField",
            objType: this.props.objType,
            title: "Select Merge Field",
            onFinishedAction: function () {
            }.bind(this)
        });
    },

    /**
     * Callback used to handle the selecting of field checkbox
     *
     * @param {string} fieldValue The value of the field that was checked
     * @param {bool} isChecked The current state of the checkbox
     * @private
     */
    _handleCheckboxSelect: function (fieldValue, isChecked) {
        var emailTo = this.props.data.to;

        // if emailTo data is not defined, then lets set it to an array variable type
        if (!emailTo) {
            emailTo = [];
        }

        if (isChecked) {
            emailTo.push(fieldValue)
        } else {

            // if the fieldValue is deselected, then we need to remove that fieldValue in the data array
            for (var idx in emailTo) {
                if (emailTo[idx] == fieldValue) {
                    emailTo.splice(idx, 1);
                }
            }
        }

        this._handleDataChange('to', emailTo);
    },

    /**
     * Set intial values for the input text for send email from
     * @private
     */
    _setInputValues: function () {
        let from = this.props.data.from || null;
        this.refs.fromInput.setValue(from);
    }
});

module.exports = SendEmail;
