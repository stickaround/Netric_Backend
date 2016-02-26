/**
 * The workflow action selector.
 *
 * This selector is used by workflow action dialogs to get available
 *  options to select an entity field as a variable based on the field.type/field.subtype provided
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var netric = require("../../../../base");
var definitionLoader = require("../../../../entity/definitionLoader");
var Controls = require('../../../Controls.jsx');
var DropDownMenu = Controls.DropDownMenu;
var Checkbox = Controls.Checkbox;

/**
 * Displays a dropdown that will let the user select an entity field as variable
 */
var WorkflowActionSelector = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * The object type where we will get the entity fields
         *
         * @type {string}
         */
        objType: React.PropTypes.string.isRequired,

        /**
         * This will determine how we will display the fields
         *
         * @type {string}
         */
        displayType: React.PropTypes.oneOf(['dropdown', 'checkbox']),

        /**
         * This will determine how we will filter the entity fields
         *
         * @type {string}
         */
        filterBy: React.PropTypes.oneOf(['none', 'type', 'subtype']),

        /**
         * The field type that we will use as a filter
         *
         * @type {string}
         */
        fieldType: React.PropTypes.string,

        /**
         * Callback called when the user selects a field (Applicable only with dropdown)
         *
         * @var {function}
         */
        onChange: React.PropTypes.func,

        /**
         * Callback called when the user selects a field (Applicable only with checkbox)
         *
         * @var {function}
         */
        onCheck: React.PropTypes.func,

        /**
         * The field that was selected
         *
         * @var {string}
         */
        selectedField: React.PropTypes.any,

        /**
         * If we have an additional custom data for menu, then we specify them here
         *
         * data[0]: {
         *  value: browse,
         *  text: select specific user
         * }
         *
         * @var {array}
         */
        additionalMenuData: React.PropTypes.array
    },

    /**
     * Return the default props of this component
     *
     * @returns {{}}
     */
    getDefaultProps: function () {
        return {
            displayType: 'dropdown',
            filterBy: 'none'
        }
    },

    /**
     * Get the starting state of this component
     */
    getInitialState: function () {

        // We need to know the type of object we are acting on
        return {
            entityDefinition: null
        };
    },

    /**
     * We have entered the DOM
     */
    componentDidMount: function () {
        definitionLoader.get(this.props.objType, function (def) {
            this._handleEntityDefinititionLoaded(def);
        }.bind(this));
    },

    /**
     * Render the component
     */
    render: function () {

        if (!this.state.entityDefinition) {
            // Entity definition is loading still so return an empty div
            return (<div />);
        }

        // This will contain the entity fields
        let fields = null;

        // This will contain the field menu data that will be used in the dropdown menu or checkbox list
        let fieldData = [];

        // Determine on how we will get the entity fields
        switch (this.props.filterBy) {
            case 'none':

                // Get the entity fields
                fields = this.state.entityDefinition.getFields();
                break;

            case 'type':

                // Get the entity fields by filtering the field.type
                fields = this.state.entityDefinition.getFieldsByType(this.props.fieldType);
                break;

            case 'subtype':

                // Get the entity fields by filtering the field.subtype
                fields = this.state.entityDefinition.getFieldsBySubtype(this.props.fieldType);
                break;
        }

        // Determine on how we will display the entity field selector
        switch (this.props.displayType) {
            case 'dropdown':

                // If no field name has been selected, enter a first explanation entry
                if (!this.props.selectedField) {
                    fieldData.push({
                        value: '',
                        text: 'Select ' + this.props.fieldType
                    });
                }

                // This will loop thru each entity field and save it in the fieldData array to be used in the dropdown menu
                this._prepFieldData(fieldData, fields);

                // If we have additional custom menu data, then lets add it in our menu data
                if (this.props.additionalMenuData) {
                    this.props.additionalMenuData.map(function (data) {
                        fieldData.push(data);
                    })
                }
                
                let selectedFieldIndex = (this.props.selectedField) ?
                    this._getSelectedIndex(fieldData, this.props.selectedField) : 0;

                return (
                    <div>
                        <DropDownMenu
                            menuItems={fieldData}
                            selectedIndex={parseInt(selectedFieldIndex)}
                            onChange={this._handleFieldChange}/>
                    </div>
                );
                break;

            case 'checkbox':

                let checkboxDisplay = [];

                // This will loop thru each entity field and save it in the fieldData array to be used in the checkbox list
                this._prepFieldData(fieldData, fields);

                // Loop through fields and prepare the checkbox inputs
                for (var idx in fieldData) {
                    let field = fieldData[idx];
                    let isChecked = false;

                    // Make sure the selectedField is an array, and it contains the currentFieldData then we set the checkbox to checked
                    if(this.props.selectedField instanceof Array && this.props.selectedField.indexOf(field.value) > -1) {
                        isChecked = true;
                    }

                    checkboxDisplay.push(
                        <Checkbox
                            key={idx}
                            value={field.value}
                            label={field.text}
                            onCheck={this._handleFieldCheck}
                            defaultSwitched={isChecked}/>
                    );
                }

                return (
                    <div>
                        {checkboxDisplay}
                    </div>
                );
                break;

            default:
                var errorMsg = this.props.displayType + ' display Type is not available. Please provide a valid display type.';
                log.error(errorMsg);
                return (
                    <div>
                        errorMsg
                    </div>
                );
                break;
        }
    },

    /**
     * Callback used to handle commands when user selects a field name
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {Object} data    The object value of the menu clicked
     * @private
     */
    _handleFieldChange: function (e, key, data) {
        if (this.props.onChange) {
            this.props.onChange(data.value);
        }
    },

    /**
     * Handles the clicking of checkbox when user selects a field name
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {bool} isChecked The current state of the checkbox
     *
     * @private
     */
    _handleFieldCheck: function (e, isChecked) {
        if (this.props.onCheck) {
            this.props.onCheck(e.target.value, isChecked);
        }
    },

    /**
     * Gets the index of a field based on the name
     *
     * @param {Array} data Array of data that will be mapped to get the index of the saved field/operator/blogic value
     * @param {string} value The value that will be used to get the index
     * @private
     */
    _getSelectedIndex: function (data, value) {
        var index = 0;
        for (var idx in data) {
            if (data[idx].value == value) {
                index = idx;
                break;
            }
        }

        return index;
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
    },

    /**
     * Prepares the field menu data to be used in a dropdown menu or checkbox list
     *
     * @param {array} fields Array of filtered/unfiltered fields of an entity
     * @returns {Array} Data that will be used in the menu dropdown
     * @private
     */
    _prepFieldData: function (fieldData, fields) {

        // Loop through fields and pass to dropdown menu data
        for (var idx in fields) {
            let field = fields[idx];

            fieldData.push({
                value: "<%" + field.name + "%>",
                text: this.props.objType + '.' + field.title
            });

            // Add Manager
            fieldData.push({
                value: "<%" + field.name + ".manager_id%>",
                text: this.props.objType + '.' + field.title + '.Manager'
            });
        }
    }
});

module.exports = WorkflowActionSelector;
