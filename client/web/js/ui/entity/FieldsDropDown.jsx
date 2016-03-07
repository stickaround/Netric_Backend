/**
 * Render a field select dropdown
 *

 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var groupingLoader = require("../../entity/groupingLoader");
var Where = require("../../entity/Where");
var definitionLoader = require("../../entity/definitionLoader");
var Field = require("../../entity/definition/Field");
var ObjectSelect = require("./ObjectSelect.jsx");
var GroupingSelect = require("./GroupingSelect.jsx");
var DropDownMenu = Chamel.DropDownMenu;
var TextField = Chamel.TextField;
var IconButton = Chamel.IconButton;

/**
 * Create a DropDown that shows all fields for an object type
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
         * Option to show one level deep of referenced entity fields
         *
         * This is useful if working with things like an entity index
         * that can query across entity types. For example, lead.owner.name
         *
         * @var {bool}
         */
        showReferencedFields: React.PropTypes.bool,

        /**
         * Flag to indicate if we should include read-only fields
         *
         * If we are querying fields then of course we would want to
         * but if we are setting anything then we probably don't want
         * read-only fields to even show up in the list.
         *
         * @var {bool}
         */
        showReadOnlyFields: React.PropTypes.bool,

        /**
         * Optional list of field types to exclude from the list
         *
         * @var {array}
         */
        hideFieldTypes: React.PropTypes.array,

        /**
         * The field that was selected
         *
         * @var {string}
         */
        selectedField: React.PropTypes.string,
    },


    /**
     * Set defaults
     *
     * @returns {{}}
     */
    getDefaultProps: function() {
        return {
            showReferencedFields: false,
            showReadOnlyFields: true,
            hideFieldTypes: []
        }
    },

    /**
     * Return the starting state of this component
     *
     * @returns {{}}
     */
    getInitialState: function() {
        return {
            entityDefinition: null
        };
    },

    /**
     * We have entered the DOM
     */
    componentDidMount: function() {
        definitionLoader.get(this.props.objType, function(def) {
            this._handleEntityDefinititionLoaded(def);
        }.bind(this));
    },

    /**
     * Render the dropdown containing all fields
     */
    render: function() {

        if (!this.state.entityDefinition) {
            // Entity definition is loading still so return an empty div
            return (<div />);
        }

        // Set list of fields to Load
        let fieldData = [];

        // If no field name has been selected, enter a first explanation entry
        if (!this.props.selectedField) {
            fieldData.push({
                payload: "",
                text: "Select Field"
            });
        }

        // TOOD: We should add sub-fields for cross entity reference
        let fields = this.state.entityDefinition.getFields();
        for (var i in fields) {

            // Skip read-only fields if not set to be displayed
            if (!this.props.showReadOnlyFields && fields[i].readonly) {
                continue;
            }

            // Skip fields with types that have been hidden
            if (this.props.hideFieldTypes.indexOf(fields[i].type) !== -1) {
                continue;
            }

            fieldData.push({
                payload: fields[i].name,
                text: fields[i].title
            });
        }
        let selectedFieldIndex = (this.props.selectedField) ?
            this._getSelectedIndex(fieldData, this.props.selectedField) : 0;


        return (
            <DropDownMenu
                menuItems={fieldData}
                selectedIndex={parseInt(selectedFieldIndex)}
                onChange={this._handleFieldChange}
            />
        );
    },

    /**
     * Callback used to handle commands when user selects a field name
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {Object} data	The object value of the menu clicked
     * @private
     */
    _handleFieldChange: function(e, key, data) {
        if (this.props.onChange) {
            this.props.onChange(data.payload);
        }
    },

    /**
     * Gets the index of a field based on the name
     *
     * @param {Array} data Array of data that will be mapped to get the index of the saved field/operator/blogic value
     * @param {string} value The value that will be used to get the index
     * @private
     */
    _getSelectedIndex: function(data, value) {
        var index = 0;
        for(var idx in data) {
            if(data[idx].payload == value) {
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
    _handleEntityDefinititionLoaded: function(entityDefinition) {
        this.setState({
            entityDefinition: entityDefinition
        });
    }
});

module.exports = FieldsDropDown;
