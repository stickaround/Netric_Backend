/**
 * Component get user input for any entity field
 *

 */
'use strict';

var React = require('react');
var groupingLoader = require("../../entity/groupingLoader");
var Where = require("../../entity/Where");
var definitionLoader = require("../../entity/definitionLoader");
var Field = require("../../entity/definition/Field");
var ObjectSelect = require("./ObjectSelect.jsx");
var GroupingSelect = require("./GroupingSelect.jsx");
var Controls = require('../Controls.jsx');
var DropDownMenu = Controls.DropDownMenu;
var TextField = Controls.TextField;
var IconButton = Controls.IconButton;

var boolInputMenu = [
    {payload: 'true', text: 'true'},
    {payload: 'false', text: 'false'},
];

/**
 * Create a entity field input based on the field type
 */
var FieldsDropDown = React.createClass({

    propTypes: {

        /**
         * The object type we are querying
         *
         * @var {string}
         */
        objType: React.PropTypes.string.isRequired,

        /**
         * Callback called when the user selects a field
         *
         * @var {function}
         */
        onChange: React.PropTypes.func,

        /**
         * The name of the field we are editing
         *
         * @var {string}
         */
        fieldName: React.PropTypes.string.isRequired,

        /**
         * Current value of the field input
         *
         * @var {mixed}
         */
        value: React.PropTypes.any,

        /**
         * If the field input is an object, then it should have a value label
         *
         * @var {string}
         */
        valueLabel: React.PropTypes.string,

        /**
         * Optional. The entity definition of the object
         *
         * If we are trying to display multiple input fields, we may want to provide the entityDefinition for faster performance
         *  so the component does not have to retrieve the entityDefinition everytime we load each input fields
         *
         * @var {object}
         */
        entityDefinition: React.PropTypes.object,

        /**
         * Flag indicating if we are in edit mode or view mode
         *
         * @type {bool}
         */
        editMode: React.PropTypes.bool,

        /**
         * Flag indicating if we will display the field title
         *
         * @type {bool}
         */
        displayFieldTitle: React.PropTypes.bool
    },

    /**
     * Return the default props of this component
     *
     * @returns {{}}
     */
    getDefaultProps: function () {
        return {
            displayFieldTitle: false,
            editMode: true,
            valueLabel: null,
            entityDefinition: null
        }
    },

    /**
     * Return the starting state of this component
     *
     * @returns {{}}
     */
    getInitialState: function () {
        return {
            entityDefinition: this.props.entityDefinition
        };
    },

    /**
     * We have entered the DOM
     */
    componentDidMount: function () {

        // If the entity definition is already provided, then we do not need to get the definition again
        if (!this.state.entityDefinition) {
            // Get the definition so we can get field details
            definitionLoader.get(this.props.objType, function (def) {

                this._handleEntityDefinititionLoaded(def);
            }.bind(this));
        }
    },

    /**
     * Render the dropdown containing all fields
     */
    render: function () {
        if (!this.state.entityDefinition) {
            // Entity definition is loading still so return an empty div
            return (<div />);
        }

        let field = this.state.entityDefinition.getField(this.props.fieldName);
        let value = this.props.value;
        let valueLabel = this.props.valueLabel;
        let displayFieldTitle = null;
        let displayField = null;

        // If we are on editMode, then let's display the input fields
        if (this.props.editMode) {
            switch (field.type) {
                case Field.types.fkey:
                case Field.types.fkeyMulti:
                    displayField = (
                        <GroupingSelect
                            onChange={this._handleGroupingSelect}
                            objType={this.props.objType}
                            fieldName={field.name}
                            allowNoSelection={false}
                            label={'none'}
                            selectedValue={value}
                        />
                    );
                    break;

                case Field.types.object:
                    displayField = (
                        <ObjectSelect
                            onChange={this._handleObjectSelect}
                            objType={this.props.objType}
                            field={field}
                            value={value}
                            label={valueLabel}
                        />
                    );
                    break;

                case Field.types.bool:
                    displayField = (
                        <DropDownMenu
                            onChange={this._handleValueSelect}
                            selectedIndex={ ( (value && value.toString()) === 'true' ? 0 : 1 )}
                            menuItems={boolInputMenu}
                        />
                    );
                    break;

                default:
                    displayField = (
                        <TextField
                            onBlur={this._handleValueInputBlur}
                            defaultValue={value}
                        />
                    );
                    break;
            }
        } else {

            // If we are on view mode and we have a field value
            if(value) {
                displayField = valueLabel || value;
            } else {

                // If we are on view mode and we do not have a field value, then let's just return a blank div
                return (
                    <div />
                )
            }

        }

        // This will display the field title and field input / field value
        if (this.props.displayFieldTitle) {
            return (
                <div>
                    <div className="entity-form-field-label">
                        {field.title}
                    </div>
                    <div className="entity-form-field-value">
                        {displayField}
                    </div>
                </div>
            )
        } else {

            // This will just display the field input
            return (
                <div>
                    {displayField}
                </div>
            );
        }
    },

    /**
     * Callback used to handle commands when user selects a field name
     *
     * @param {mixed} value    Value to send to the callback
     * @private
     */
    _handleValueChange: function (value) {
        console.log("setting", this.props.fieldName, "to", value);
        if (this.props.onChange) {
            this.props.onChange(this.props.fieldName, value);
        }
    },

    /**
     * Callback used to handle commands when user selects a value in the dropdown groupings input
     *
     * @param {string} payload The value of the selected menu
     * @param {string} text The text of the selected menu
     * @private
     */
    _handleGroupingSelect: function (payload, text) {
        this._handleValueChange(payload);
    },

    /**
     * Callback used to handle commands when user selects a value in the dropdown groupings input
     *
     * @param {string} oid The id of the selected entity
     * @param {string} name The name of the selected entity
     * @private
     */
    _handleObjectSelect: function (oid, name) {
        this._handleValueChange(oid);
    },

    /**
     * Callback used to handle commands when user selects a value in the dropdown if the value input is a boolean type
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {array} menuItem The object value of the menu clicked
     * @private
     */
    _handleValueSelect: function (e, key, menuItem) {
        this._handleValueChange(menuItem.payload);
    },

    /**
     * Handles blur on the value input
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @private
     */
    _handleValueInputBlur: function (e) {
        this._handleValueChange(e.target.value);
    },

    /**
     * Callback used when an entity definition loads (or changes)
     *
     * @param {EntityDefinition} entityDefinition The loaded definition
     */
    _handleEntityDefinititionLoaded: function (entityDefinition) {
        this.setState({
            entityDefinition: entityDefinition
        });
    }
});

module.exports = FieldsDropDown;
