/**
 * Handle a request approval type action
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
var netric = require("../../../../../base");
var Field = require('../../../../../entity/definition/Field.js');
var entityLoader = require('../../../../../entity/loader');
var controller = require('../../../../../controller/controller');
var TextFieldAutoComplete = require("../../../../mixins/TextFieldAutoComplete.jsx");
var FieldsDropDown = require("../../../FieldsDropDown.jsx");
var Controls = require('../../../../Controls.jsx');
var DropDownMenu = Controls.DropDownMenu;
var TextField = Controls.TextField;

/**
 * Manage action data for request approval
 */
var RequestApproval = React.createClass({

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
         * The workflow_action entity that we are currently working on
         */
        entity: React.PropTypes.object,

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
            payload: 'browse',
            text: 'Specific User'
        }];

        return (
            <div className="entity-form-field">
                <div>
                    <div className="entity-form-field-inline-block">
                        <TextField
                            floatingLabelText='Request Approval From'
                            ref="approvalFromInput"
                            defaultValue={this.props.data.approval_from}
                            />
                    </div>
                    <div className="entity-form-field-inline-block">
                        <FieldsDropDown
                            objType={this.props.objType}
                            filterBy="subtype"
                            filterText="user"
                            fieldFormat={{prepend: '<%', append: '%>'}}
                            includeFieldManager={true}
                            selectedField={this.props.data.approval_from}
                            additionalMenuData={additionalSelectorData}
                            onChange={this._handleMenuSelect}
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
     * @param {string} fieldValue The value of the fieldname that was selected
     * @private
     */
    _handleMenuSelect: function (fieldValue) {

        if (fieldValue === 'browse') {
            this._handleSelectExistingUser();
        } else {
            this._handleDataChange('approval_from', fieldValue);
        }
    },

    /**
     * Callback used to display the entity browser and enable the user to select a specific user
     *
     * @private
     */
    _handleSelectExistingUser: function () {

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        let BrowserController = require('../../../../../controller/EntityBrowserController');
        let browser = new BrowserController();
        browser.load({
            type: controller.types.DIALOG,
            title: 'Select User',
            objType: 'user',
            onSelect: function (objType, id, name) {
                let selectedUser = this.transformAutoCompleteSelected({payload: id, text: name});
                this._handleDataChange('approval_from', selectedUser);
            }.bind(this)
        });
    },

    /**
     * Set intial values for the input text for request approval from
     * @private
     */
    _setInputValues: function () {
        let approvalFrom = this.props.data.approval_from || null;
        this.refs.approvalFromInput.setValue(approvalFrom);
    }
});

module.exports = RequestApproval;
