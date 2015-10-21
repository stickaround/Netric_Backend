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

    /**
     * Render the component
     */
    render: function () {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);
        var valueLabel = this.props.entity.getValueName(fieldName);
        if (!valueLabel) {
            valueLabel = "Not Set";
        }

        if (this.props.editMode) {
            return (
                <ObjectSelect
                    onChange={this._handleSetValue}
                    objType={this.props.entity.def.objType}
                    fieldName={fieldName}
                    value={fieldValue}
                    label={this.props.entity.getValue(fieldName)}
                    />
            );
        } else {
            return (<div>{valueLabel}</div>);
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