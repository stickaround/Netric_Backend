/**
 * Integer field component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require("chamel");
var DropDownMenu = Chamel.DropDownMenu;

/**
 * Base level element for enetity forms
 */
var IntegerField = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    getInitialState: function() {

        // Return the initial state
        return {
            selectedIndex: 0
        };
    },

    componentDidMount: function() {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        var optionalValuesData = this._getOptionalValues();

        // If field value is null and we have optionalValuesData then lets set a default value
        if(this.props.entity.getValue(fieldName) == null && optionalValuesData.length >  0) {

            // Set a default value using the optionalValuesData first index
            this.props.entity.setValue(fieldName, optionalValuesData[0].key);
        }
    },

    /**
     * Render the component
     */
    render: function () {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);

        if (this.props.editMode) {
            return (
                <DropDownMenu
                    menuItems={this._getOptionalValues()}
                    selectedIndex={this.state.selectedIndex}
                    onChange={this._handleChange} />
            );
        } else {
            return (
                <div></div>
            );
        }
    },

    /**
     * Callback used to handle when user selects the a value in the dropdown
     *
     * @param {DOMEvent} e          Reference to the DOM event being sent
     * @param {int} key             The index of the menu clicked
     * @param {array} menuItem      The object value of the menu clicked
     * @private
     */
    _handleChange: function(e, key, menuItem) {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        this.props.entity.setValue(fieldName, menuItem.key);
        this.setState({selectedIndex: key});
    },

    _getOptionalValues: function () {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        var field = this.props.entity.def.getField(fieldName);
        var optionalValues = field.optionalValues;
        var optionalValuesData = [];

        if (optionalValues) {
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

module.exports = IntegerField;