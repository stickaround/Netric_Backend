/**
 * Object reference component
 *
 */
'use strict';

var React = require('react');
var controller = require("../../../../controller/controller");
var ObjectSelect = require("../../ObjectSelect.jsx");
var entityLoader = require("../../../../entity/loader");
var definitionLoader = require("../../../../entity/definitionLoader");
var ObjectTypeDropDown = require("../../ObjectTypeDropDown.jsx");
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
        elementNode: React.PropTypes.object.isRequired,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    getInitialState: function () {

        return ({
            // Used if valueName was not set for the field value
            valueLabel: "",
            definitions: null,
            subtype: null
        });
    },


    /**
     * Render the component
     */
    render: function () {
        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');

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

        /*
         * If we still do not have a value for fieldSubtype but we have a fieldValue
         * Then we try to get the subtype value from the fieldValue by decoding it and use the objType as our fieldSubtype
         * The format of fieldValue is usually objType:entityId
         */
        if(!fieldSubtype && fieldValue) {
            var objRef = this.props.entity.decodeObjRef(fieldValue);

            fieldSubtype = objRef.objType || null;
        }

        var definitionsDropDown = null;
        // If this field does NOT have a subtype, then load the definitions and let the user pick a subtype
        if (field.subtype === '' || field.subtype === null) {
            definitionsDropDown = (
                <div className="entity-form-object-type-dropdown">
                    <ObjectTypeDropDown
                        objType={fieldSubtype}
                        onChange={this._handleDefintionsMenuSelect}
                    />
                </div>
            );
        }

        if (this.props.editMode) {
            return (
                <div>
                    <div className="entity-form-field-label">
                        {field.title}
                    </div>
                    {definitionsDropDown}
                    <div className="entity-form-field-value">
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

        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');
        this.props.entity.setValue(fieldName, oid, title);
    },

    /**
     * Callback used to handle the selecting of definition
     *
     * @param {string} objType The object type that was selected
     * @private
     */
    _handleDefintionsMenuSelect: function (objType) {
        this.setState({subtype: objType});
    }
});

module.exports = ObjectField;