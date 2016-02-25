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
var Selector = require("../Selector.jsx");
var Controls = require('../../../../Controls.jsx');
var TextField = Controls.TextField;

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
                    <div className="entity-form-field-value">
                        <Selector
                            objType={this.props.objType}
                            displayType="checkbox"
                            filterBy="subtype"
                            fieldType="user"
                            onChange={this._handleCheckboxSelect}
                        />
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
     * Callback used to handle the selecting of user dropdown menu
     *
     * @param {string} fieldValue The value of the field that was selected
     * @private
     */
    _handleMenuSelect: function (fieldValue) {
        if(fieldValue === 'default') {
            this._handleDataChange('from', netric.getApplication().getAccount().getUser().email);
        } else {
            this._handleDataChange('from', fieldValue);
        }
    },

    _handleCheckboxSelect: function() {

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
