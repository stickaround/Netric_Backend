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
var Controls = require("../Controls.jsx");
var TextField = Controls.TextField;
var IconButton = Controls.IconButton;
var DropDownMenu = Controls.DropDownMenu;
var MenuItem = Controls.MenuItem;
var Menu = Controls.Menu;
var MenuItem = Controls.MenuItem;
var NestedMenuItem = Controls.NestedMenuItem;
var RaisedButton = Controls.RaisedButton;
var Popover = Controls.Popover;

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
         * Callback called when the user selects the parent field
         *
         * @var {function}
         */
        onParentFieldSelect: React.PropTypes.func,

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

        /**
         * This will determine how we will filter the entity fields
         *
         * @type {string}
         */
        filterBy: React.PropTypes.oneOf(['none', 'type', 'subtype']),

        /**
         * The text that we will used as a filter
         *
         * @type {string}
         */
        filterText: React.PropTypes.string,

        /**
         * Optional data that will be added in the menu data
         *
         * data[0]: {
         *  value: browse,
         *  text: select specific user
         * }
         *
         * @var {array}
         */
        additionalMenuData: React.PropTypes.array,

        /**
         * Optional format for the field values
         *
         * fieldFormat = {
         *  prepend: '<%',
         *  append: '%>'
         * }
         *
         * @var {array}
         */
        fieldFormat: React.PropTypes.object,

        /**
         * This will determine how many levels we need to search for the referenced fields
         *
         * @type {int}
         */
        showReferencedFields: React.PropTypes.number,

        /**
         * This will determine that we are dealing with referenced entity.
         *
         * This will contain the field details
         *
         * @type {entity/definition/Field}
         */
        parentField: React.PropTypes.object,

        /**
         * The label that will be used in the button to display the declarative menu
         *
         * @type {string}
         */
        menuEntryLabel: React.PropTypes.string
    },


    /**
     * Set defaults
     *
     * @returns {{}}
     */
    getDefaultProps: function () {
        return {
            showReferencedFields: false,
            showReadOnlyFields: true,
            hideFieldTypes: [],
            filterBy: 'none',
            menuEntryLabel: 'Select Field',
            showReferencedFields: 0,
            fieldFormat: {
                prepend: '',
                append: ''
            },
        }
    },

    /**
     * Return the starting state of this component
     *
     * @returns {{}}
     */
    getInitialState: function () {
        return {
            fieldData: null,
            openMenu: false
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
     * Render the dropdown containing all fields
     */
    render: function () {
        var fieldData = this.state.fieldData;

        if (!fieldData) {
            // We are still getting the fieldData from the entity definition so return an empty div
            return (<div />);
        }

        // If showReferencedFields is 0 and we dont have props.parentField then we will just show the dropdown
        if (!this.props.parentField && this.props.showReferencedFields == 0) {
            var selectedFieldIndex = (this.props.selectedField) ?
                this._getSelectedIndex(fieldData, this.props.selectedField) : 0;

            return (
                <DropDownMenu
                    menuItems={fieldData}
                    selectedIndex={parseInt(selectedFieldIndex)}
                    onChange={this._handleFieldChange}
                />
            );
        } else {

            // Since showReferencedFields is greater than 0, then we will display a declarative menu
            var menuItems = [];

            // Loop thru the fieldData to get the entity fields for this objType
            for (var idx in fieldData) {
                var field = fieldData[idx];

                /*
                 * If the current field is an object and has a subtype and the showReferencedFields is greater than zero
                 *  then we will use this field as a parent and get the fields and reference it
                 *  We will use this field's subtype as the objType
                 */
                if (field.details
                    && field.details.subtype
                    && field.details.type == field.details.types.object
                    && this.props.showReferencedFields > 0) {

                    // Transfer the props to the child props
                    var {...childProps} = this.props;

                    // Override the props to determine that this is a referenced field (child field)
                    childProps.parentField = field;
                    childProps.objType = field.details.subtype;

                    // We will decrement the showReferencedFields to determine that we have searched 1 level deep
                    childProps.showReferencedFields = (this.props.showReferencedFields - 1);

                    menuItems.push(
                        <FieldsDropDown
                            {...childProps}
                            key={idx}
                        />
                    );
                } else {

                    // Just push the menuItem in the menuItems array
                    menuItems.push(
                        <MenuItem
                            key={idx}
                            index={parseInt(idx)}
                            payload={field.payload}
                            onClick={this._handleSelectMenuItem}>
                            {field.text}
                        </MenuItem>
                    );
                }
            }

            // If we have props.parentField this means that we are dealing a subMenu field so we will return the <NestedMenuItem />
            if (this.props.parentField) {
                return (
                    <NestedMenuItem
                        parentItem={this.props.parentField}
                        onParentItemClick={this._handleSelectParentMenuItem}
                        text={this.props.parentField.details.title}>
                        {menuItems}
                    </NestedMenuItem>
                );
            } else {
                return (
                    <div>
                        <RaisedButton
                            onClick={this._handlePopoverTouchTap}
                            label={this.props.menuEntryLabel}
                        />
                        <Popover
                            open={this.state.openMenu}
                            anchorEl={this.state.anchorEl}
                            anchorOrigin={{horizontal: 'left', vertical: 'bottom'}}
                            targetOrigin={{horizontal: 'left', vertical: 'top'}}
                            onRequestClose={this._handlePopoverRequestClose}>
                            <Menu>
                                {menuItems}
                            </Menu>
                        </Popover>
                    </div>
                );
            }
        }
    },

    /**
     * Callback used to handle commands when user clicks the button to display the declarative menu
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @private
     */
    _handlePopoverTouchTap: function (e) {

        // This prevents ghost click.
        e.preventDefault();

        this.setState({
            openMenu: this.state.openMenu ? false : true,
            anchorEl: e.currentTarget
        });
    },

    /**
     * Callback used to close the popover
     *
     * @private
     */
    _handlePopoverRequestClose: function () {
        this.setState({
            openMenu: false
        });
    },

    /**
     * Callback used to handle commands when user selects a field name
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {Object} data The object value of the menu clicked
     * @private
     */
    _handleFieldChange: function (e, key, data) {
        if (this.props.onChange) {
            this.props.onChange(data.payload);
        }
    },

    /**
     * Callback used to handle commands when user selects a field name
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @private
     */
    _handleSelectMenuItem: function (e, key) {
        if (this.props.onChange && this.state.fieldData[key]) {
            this.props.onChange(this.state.fieldData[key].payload);
        }
    },

    /**
     * Callback used to handle commands when user selects a parent field name
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {Object} data The object value of the menu clicked
     * @private
     */
    _handleSelectParentMenuItem: function (e, data) {
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
    _getSelectedIndex: function (data, value) {
        var index = 0;
        for (var idx in data) {
            if (data[idx].payload == value) {
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

        let fields = null;

        // Determine on how we will get the entity fields
        switch (this.props.filterBy) {
            case 'type':

                // Get the entity fields by filtering the field.type
                fields = entityDefinition.getFieldsByType(this.props.filterText);
                break;

            case 'subtype':

                // Get the entity fields by filtering the field.subtype
                fields = entityDefinition.getFieldsBySubtype(this.props.filterText);
                break;

            case 'none':
            default:

                // Get the entity fields
                fields = entityDefinition.getFields();
                break;
        }

        // Prepare the entity fields to be displayed in a dropdown
        this._prepFieldData(fields);
    },

    /**
     * Prepares the field menu data to be used in a dropdown menu
     *
     * @param {array} fields Array of filtered/unfiltered fields of an entity
     * @private
     */
    _prepFieldData: function (fields) {

        // Set list of fields to Load
        let data = [];

        // If no field name has been selected, enter a first explanation entry
        if (!this.props.selectedField
            && !this.props.parentField


            && this.props.showReferencedFields == 0) {
            data.push({
                payload: "",
                text: "Select Field"
            });
        }

        for (var idx in fields) {
            let field = fields[idx];

            // Skip read-only fields if not set to be displayed
            if (!this.props.showReadOnlyFields && field.readonly) {
                continue;
            }

            // Skip fields with types that have been hidden
            if (this.props.hideFieldTypes.indexOf(field.type) !== -1) {
                continue;
            }

            // If we have props.parentField, then we will prepend it in the field.name
            var parentNamePrepend = (this.props.parentField) ? this.props.parentField.details.name + '.' : '';

            data.push({
                payload: this.props.fieldFormat.prepend + parentNamePrepend + field.name + this.props.fieldFormat.append,
                text: field.title,
                details: field
            });
        }

        // If we have additional custom menu data, then lets add it in our field data
        if (this.props.additionalMenuData && !this.props.parentField) {
            this.props.additionalMenuData.map(function (additionalData) {
                data.push(additionalData);
            })
        }

        this.setState({fieldData: data});
    }
});

module.exports = FieldsDropDown;
