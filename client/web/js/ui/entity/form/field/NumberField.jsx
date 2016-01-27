/**
 * Numeric field input
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var TextFieldComponent = Chamel.TextField;
var DropDownMenu = Chamel.DropDownMenu;

/**
 * Field input for numeric field types
 */
var NumberField = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    getInitialState: function () {
        return ({
            errorText: null,
            selectedIndex: 0
        });
    },

    componentDidMount: function () {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        var optionalValuesData = this._getOptionalValues();

        // If field value is null and we have optionalValuesData then lets set a default value
        if (this.props.entity.getValue(fieldName) == null && optionalValuesData) {

            // Set a default value using the optionalValuesData first index
            this.props.entity.setValue(fieldName, optionalValuesData[0].key);
        }
    },

    render: function () {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);
        var optionalValues = this._getOptionalValues();

        if (this.props.editMode) {

            var fieldDisplay = null;

            // If the field entity has optionalValues, then lets display it in a dropdown
            if (optionalValues) {
                fieldDisplay = (
                    <div>
                        <div className="entity-form-field-label">
                            {field.title}
                        </div>
                        <DropDownMenu
                            menuItems={this._getOptionalValues()}
                            selectedIndex={this.state.selectedIndex}
                            onChange={this._handleDropdownChange}/>
                    </div>
                );
            } else {

                // If there is no optionalValues available, then just display a text field
                fieldDisplay = (
                    <TextFieldComponent
                        floatingLabelText={field.title}
                        value={fieldValue}
                        errorText={this.state.errorText}
                        onChange={this._handleInputChange}/>
                );
            }

            return (
                fieldDisplay
            );


        } else {

            // If there is no value then we don't need to show this field at all
            if (!fieldValue) {
                return (<div />);
            } else {

                // If there is an optionalValues available, then lets try to find the text value of the field value
                if (optionalValues) {

                    // Loop thru optinalValues to find the corresponding text value of the field value
                    optionalValues.map(function (optVal) {
                        if (optVal.key == fieldValue) {
                            fieldValue = optVal.text;
                        }
                    })
                }

                return (
                    <div>
                        <div className="entity-form-field-label">{field.title}</div>
                        <div className="entity-form-field-value">{fieldValue}</div>
                        <div className="clearfix"></div>
                    </div>
                );
            }
        }
    },

    /**
     * Handle value change
     *
     * @param {DOMEvent} evt Reference to the DOM event being sent
     * @private
     */
    _handleInputChange: function (evt) {
        var value = evt.target.value;
        var isNumeric = !isNaN(parseFloat(value)) && isFinite(value);

        this.setState({
            errorText: isNumeric ? null : 'This field must be numeric.',
        });

        if (isNumeric) {
            this.props.entity.setValue(this.props.xmlNode.getAttribute('name'), value);
        }
    },

    /**
     * Callback used to handle when user selects the a value in the dropdown
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {array} menuItem The object value of the menu clicked
     * @private
     */
    _handleDropdownChange: function (e, key, menuItem) {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        this.props.entity.setValue(fieldName, menuItem.key);
        this.setState({selectedIndex: key});
    },

    /**
     * Get the optional values of the entity field
     *
     * @return array Collection of optional values data
     * @private
     */
    _getOptionalValues: function () {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        var field = this.props.entity.def.getField(fieldName);
        var optionalValues = field.optionalValues;
        var optionalValuesData = null;

        if (optionalValues) {
            optionalValuesData = [];

            for (var key in optionalValues) {
                optionalValuesData.push({
                    key: key,
                    text: optionalValues[key]
                });
            }
        }

        return optionalValuesData;
    }

});

module.exports = NumberField;