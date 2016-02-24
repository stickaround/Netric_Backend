/**
 * Handle the create entity type action
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
var entityLoader = require('../../../../../entity/loader');
var definitionLoader = require("../../../../../entity/definitionLoader");
var FieldInput = require("../../../FieldInput.jsx");
var Field = require('../../../../../entity/definition/Field.js');

/**
 * Manage action data for create entity
 */
var CreateEntity = React.createClass({

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

    getInitialState: function () {
        return ({
            entityDefinition: null,
        });
    },

    componentDidMount: function () {
        let func = function ProcessReturnedDefinitions(definitions) {
            this.setState({entityDefinition: definitions});
        }.bind(this);

        // Get all the object types
        definitionLoader.getAll(func);
    },

    /**
     * Render action type form
     *
     * @returns {JSX}
     */
    render: function () {

        let objType = this.props.data.obj_type || null;
        let definitionsDropDown = null;
        let entityFieldsDisplay = [];

        // If we have a loaded all the entityDefinition, then lets display it in a dropdown menu
        if (this.state.entityDefinition) {

            // If we are on editMode, then let's display the dropdown menu of object types
            if (this.props.editMode) {

                // Get the definition menu data
                let definitionsMenuData = this._getDefinitionMenuData();

                // Get the selected index of the objType
                let selectedIndex = (objType) ?
                    this._getSelectedIndex(definitionsMenuData, objType) : 0;

                definitionsDropDown = (
                    <div className="entity-form-field-value">
                        <DropDownMenu
                            menuItems={definitionsMenuData}
                            selectedIndex={parseInt(selectedIndex)}
                            onChange={this._handleDefintionsMenuSelect}/>
                    </div>
                );
            } else {

                // If we are NOT on editMode, then let's just display the objType text
                definitionsDropDown = (
                    <div>
                        {objType}
                    </div>
                );
            }


            // If we have an objType selected, then lets display the entity fields
            if (objType) {
                let entity = entityLoader.factory(objType);

                // Loop thru the entity fields and display each field using <FieldInput>
                entity.def.fields.map(function (field) {

                    // Do not display the fields that are read only && are not objectMulti
                    if (!field.readonly && (field.type && field.type != Field.types.objectMulti)) {

                        var key = objType + field.id;
                        var value = this.props.data[field.name] || null;

                        // If we are on editMode, then let's display the field input of each entity fields
                        if (this.props.editMode) {
                            entityFieldsDisplay.push(
                                <div key={key + 'div'}>
                                    <div className="entity-form-field-label">
                                        {field.title}
                                    </div>
                                    <div className="entity-form-field-value">
                                        <FieldInput
                                            key={key}
                                            objType={objType}
                                            fieldName={field.name}
                                            value={value}
                                            onChange={this._handleValueChange}
                                            entityDefinition={entity.def}
                                        />
                                    </div>
                                </div>
                            );
                        } else { // If we are NOT on editMode, then let's just display the label and the value of each entity field

                            // Make sure we have an existing value in our data before we display the entity value
                            if (value) {
                                entityFieldsDisplay.push(
                                    <div key={key + 'label'}>
                                        <div className="entity-form-field-label">
                                            {field.title}
                                        </div>
                                        <div>
                                            {this.props.data[field.name]}
                                        </div>
                                    </div>
                                );
                            }
                        }
                    }
                }.bind(this));
            }
        } else {
            definitionsDropDown = "Loading Object Types..."
        }

        return (
            <div className="entity-form-field">
                <div>
                    <div className="entity-form-field-label">
                        Object Type
                    </div>
                    <div>
                        {definitionsDropDown}
                    </div>
                </div>
                {entityFieldsDisplay}
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
     * Callback used to handle the selecting of definition
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {array} menuItem The object value of the menu clicked
     * @private
     */
    _handleDefintionsMenuSelect: function (e, key, menuItem) {
        this._handleDataChange('obj_type', menuItem.objType);
    },

    /**
     * Handle the input value changing for the entity field
     *
     * @param {string} fieldName The name of the field changed
     * @param {any} fieldValue The value the field was changed to
     * @param {string} opt_fieldValueLabel Optional string describing an ID value
     * @private
     */
    _handleValueChange: function (fieldName, fieldValue, opt_fieldValueLabel) {
        let fieldValueLabel = opt_fieldValueLabel || null;
        this._handleDataChange(fieldName, fieldValue);
    },

    /**
     * Function that will loop thru state.entityDefinition (Entity/Definition) to get the objType and create a menu data
     *
     * @returns {Array} Menu data of the objTypes
     * @private
     */
    _getDefinitionMenuData: function () {
        let definitionsMenuData = [];

        for (let idx in this.state.entityDefinition) {
            let def = this.state.entityDefinition[idx];

            definitionsMenuData.push({
                objType: def.objType,
                text: def.title
            })
        }

        // Sort entityDefinition
        definitionsMenuData.sort(function (a, b) {
            if (a.text < b.text) return -1;
            if (a.text > b.text) return 1;
            return 0;
        });

        // Insert in the first index the default dropdown value
        definitionsMenuData.splice(0, 0, {
            objType: '',
            text: 'Select Object Type'
        });

        return definitionsMenuData;
    },

    /**
     * Gets the index of an objType based on the name
     *
     * @param {Array} data Array of data that will be mapped to get the index of the saved objType
     * @param {string} value The value that will be used to get the index
     * @private
     */
    _getSelectedIndex: function (data, value) {
        var index = 0;
        for (var idx in data) {
            if (data[idx].objType == value) {
                index = idx;
                break;
            }
        }

        return index;
    }
});

module.exports = CreateEntity;
