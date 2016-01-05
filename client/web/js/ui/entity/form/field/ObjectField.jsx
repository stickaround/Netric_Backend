/**
 * Object reference component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require("chamel");
var Dialog = Chamel.Dialog;
var controller = require("../../../../controller/controller");
var ObjectSelect = require("../../ObjectSelect.jsx");
var entityLoader = require("../../../../entity/loader");


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

    getInitialState: function() {
      return ({
          // Used if valueName was not set for the field value
          valueLabel: ""
      });
    },

    componentDidMount: function() {
        // Make sure we have the field value label set
        var fieldName = this.props.xmlNode.getAttribute('name');
        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);
        var valueLabel = this.props.entity.getValueName(fieldName, fieldValue);

        if (fieldValue && !valueLabel && !this.state.valueLabel) {
            entityLoader.get(field.subtype, fieldValue, function(ent) {
                this.setState({valueLabel: ent.getName()});
            }.bind(this));
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

        var objSelectDisplay = null;
        if(field.subtype) {
            objSelectDisplay = (<ObjectSelect
                onChange={this._handleSetValue}
                objType={this.props.entity.def.objType}
                fieldName={fieldName}
                value={fieldValue}
                label={valueLabel}
                field={field}
                />);
        }

        if (this.props.editMode) {
            return (
                <div>
                    <div className="entity-form-field-label">
                        {field.title}
                    </div>
                    <div className="entity-form-field-value">
                        {objSelectDisplay}
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
    _handleSetValue: function(oid, title) {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        this.props.entity.setValue(fieldName, oid, title);
    }
});

module.exports = ObjectField;