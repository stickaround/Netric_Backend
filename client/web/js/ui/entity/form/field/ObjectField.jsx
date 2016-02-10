/**
 * Object reference component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var controller = require("../../../../controller/controller");
var ObjectSelect = require("../../ObjectSelect.jsx");
var entityLoader = require("../../../../entity/loader");
var definitionLoader = require("../../../../entity/definitionLoader");
var Chamel = require("chamel");
var Dialog = Chamel.Dialog;
var DropDownMenu = Chamel.DropDownMenu;


/**
 * Handle displaying an object refrence
 */
var ObjectField = React.createClass({

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
            // Used if valueName was not set for the field value
            valueLabel: "",
            definitions: null,
            subtype: null,
            subtypeIndex: 0
        });
    },

    componentDidMount: function () {
        // Make sure we have the field value label set
        var fieldName = this.props.xmlNode.getAttribute('name');
        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);
        var valueLabel = this.props.entity.getValueName(fieldName, fieldValue);

        if (fieldValue && !valueLabel && !this.state.valueLabel) {
            entityLoader.get(field.subtype, fieldValue, function (ent) {
                this.setState({valueLabel: ent.getName()});
            }.bind(this));
        }

        // If this field does NOT have a subtype, then load the definitions and let the user pick a subtype
        if (field.subtype === '' || field.subtype == null) {
            var func = function ProcessReturnedDefinitions (definitions) {
                this.setState({definitions: definitions});
            }.bind(this);

            definitionLoader.getAll(func);
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
        var valueLabel = this.props.entity.getValueName(fieldName, fieldValue);

        // Handle blank labels
        if (!valueLabel && !fieldValue) {
            valueLabel = "Not Set";
        } else if (!valueLabel && this.state.valueLabel) {
            valueLabel = this.state.valueLabel;
        } else if (!valueLabel) {
            // We will set this.state.valueLabel after mounting
            valueLabel = "Loading...";
        }

        var fieldSubtype = field.subtype || this.state.subtype;
        var definitionsDropDown = null;

        // If we have a loaded all the definitions, then lets display it in a dropdown menu
        if (this.state.definitions) {
            var definitionsMenuData = [];
            for(var idx in this.state.definitions) {

                var def = this.state.definitions[idx];
                definitionsMenuData.push({
                    objType: def.objType,
                    text: def.title
                })
            }

            // Sort definitions
            definitionsMenuData.sort(function(a, b){
                if(a.text < b.text) return -1;
                if(a.text > b.text) return 1;
                return 0;
            })

            definitionsDropDown = (<DropDownMenu
                menuItems={definitionsMenuData}
                selectedIndex={this.state.subtypeIndex}
                onChange={this._handleDefintionsMenuSelect}/>);

            // If we have a null subtype, then lets assign a default 1 (needed for the first loading)
            if(!fieldSubtype) {
                fieldSubtype = definitionsMenuData[0].objType;
            }
        }

        if (this.props.editMode) {
            return (
                <div>
                    <div className="entity-form-field-label">
                        {field.title}
                    </div>
                    <div className="entity-form-field-value">
                        {definitionsDropDown}
                        <ObjectSelect
                            onChange={this._handleSetValue}
                            objType={this.props.entity.def.objType}
                            field={field}
                            subtype={fieldSubtype}
                            value={fieldValue}
                            label={valueLabel}
                            />
                    </div>
                </div>
            );
        } else {
            // Only display the field if a value exists
            if (valueLabel != 'Not Set') {
                return (
                    <div>
                        <div className="entity-form-field-label">
                            {field.title}
                        </div>
                        <div className="entity-form-field-value">
                            {valueLabel}
                        </div>
                    </div>
                );
            } else {
                return (<div />);
            }
        }
    },


    /**
     * Set the value of the entity which will trigger an onchange
     *
     * When the entity controller triggers an onChange, it will set the value here
     *
     * @param {int} oid The unique id of the entity selected
     * @param {string} title The human readable title of the entity selected
     * @private
     */
    _handleSetValue: function (oid, title) {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        this.props.entity.setValue(fieldName, oid, title);
    },

    /**
     * Callback used to handle the selecting of definition
     *
     * @param {DOMEvent} e          Reference to the DOM event being sent
     * @param {int} key             The index of the menu clicked
     * @param {array} menuItem      The object value of the menu clicked
     * @private
     */
    _handleDefintionsMenuSelect: function(e, key, menuItem) {

        this.setState({
            subtype: menuItem.objType,
            subtypeIndex: key
        });
    }
});

module.exports = ObjectField;