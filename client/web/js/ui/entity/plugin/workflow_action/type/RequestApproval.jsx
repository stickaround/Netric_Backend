/**
 * Handle a request approval type action
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
var Chamel = require('chamel');
var DropDownMenu = Chamel.DropDownMenu;
var TextField = Chamel.TextField;
var netric = require("../../../../../base");
var Field = require('../../../../../entity/definition/Field.js');
var entityLoader = require('../../../../../entity/loader');
var controller = require('../../../../../controller/controller');
var TextFieldAutoComplete = require("../../../../mixins/TextFieldAutoComplete.jsx");

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

    getInitialState: function () {
        return ({
            selectedUserFieldsMenuIndex: 0
        });
    },

    /**
     * Render action type form
     *
     * @returns {JSX}
     */
    render: function () {

        // Get the entity of the objtype reference
        let objRefEntity = entityLoader.factory(this.props.objType);

        let userFields = objRefEntity.def.getFieldsBySubtype('user');

        let userFieldsMenuData = [{
            id: '',
            value: '',
            text: 'Select User'
        }];
        let selectedUserFieldsMenuIndex = this.state.selectedUserFieldsMenuIndex;

        // Loop through user fields and pass to dropdown menu data
        for (let idx in userFields) {
            let field = userFields[idx];

            userFieldsMenuData.push({
                id: field.id,
                value: "<%" + field.name + "%>",
                text: this.props.objType + '.' + field.title
            });

            // Add Manager
            userFieldsMenuData.push({
                id: field.id,
                value: "<%" + field.name + ".manager_id%>",
                text: this.props.objType + '.' + field.title + '.Manager'
            });
        }

        // Add option to select specific user
        userFieldsMenuData.push({
            id: '',
            value: 'browse',
            text: 'Select Specific User'
        });

        return (
            <div className="entity-form-field">
                <div>
                    <div className="entity-form-field-inline-block">
                        <TextField
                            floatingLabelText='Request Approval From'
                            ref="approvalFromInput"
                            defaultValue={this.props.data.approval_from}/>
                    </div>
                    <div className="entity-form-field-inline-block">
                        <DropDownMenu
                            menuItems={userFieldsMenuData}
                            selectedIndex={selectedUserFieldsMenuIndex}
                            onChange={this._handleMenuSelect}/>
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
     * @param {DOMEvent} evt Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {array} menuItem The object value of the menu clicked
     * @private
     */
    _handleMenuSelect: function (evt, key, menuItem) {
        this.setState({
            selectedUserFieldsMenuIndex: key
        });

        if (menuItem.value === 'browse') {
            this._handleSelectExistingUser();
        } else {
            this._handleDataChange('approval_from', menuItem.value);
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
    }

});

module.exports = RequestApproval;
