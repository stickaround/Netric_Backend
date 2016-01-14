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
var objectsLoader = require("../../../../entity/objectsLoader");
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
            objects: null,
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

        // If this field do NOT have a subtype, then load the objects and let the user pick a subtype
        if (!field.subtype) {
            var func = function (objects) {
                this.setState({objects: objects});
            }.bind(this);

            objectsLoader.get(func);
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
        var objectsDropdown = null;

        // If we have a loaded objects, then lets display it in a dropdown menu
        if (this.state.objects) {
            var objectMenu = [];
            this.state.objects.map(function (object) {
                objectMenu.push({
                    objType: object.obj_type,
                    text: object.title
                })
            });

            objectsDropdown = (<DropDownMenu
                menuItems={objectMenu}
                selectedIndex={this.state.subtypeIndex}
                onChange={this._handleObjectsMenuSelect}/>);

            // If we have a null subtype, then lets assign a default 1 (needed for the first loading)
            if(!fieldSubtype) {
                fieldSubtype = objectMenu[0].objType;
            }
        }

        if (this.props.editMode) {
            return (
                <div>
                    <div className="entity-form-field-label">
                        {field.title}
                    </div>
                    <div className="entity-form-field-value">
                        {objectsDropdown}
                        <ObjectSelect
                            onChange={this._handleSetValue}
                            objType={this.props.entity.def.objType}
                            fieldName={fieldName}
                            value={fieldValue}
                            label={valueLabel}
                            field={field}
                            subtype={fieldSubtype}
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
     * Callback used to handle the selecting of objects
     *
     * @param {DOMEvent} e          Reference to the DOM event being sent
     * @param {int} key             The index of the menu clicked
     * @param {array} menuItem      The object value of the menu clicked
     * @private
     */
    _handleObjectsMenuSelect: function(e, key, menuItem) {

        this.setState({
            subtype: menuItem.objType,
            subtypeIndex: key
        });
    }
});

module.exports = ObjectField;