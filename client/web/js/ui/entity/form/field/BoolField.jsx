/**
 * Boolean field component
 *

 */
'use strict';

var React = require('react');
var Chamel = require("chamel");
var Checkbox = Chamel.Checkbox;
var log = require("../../../../log");

/**
 * Base level element for enetity forms
 */
var BoolField = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        elementNode: React.PropTypes.object.isRequired,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    /**
     * Render the component
     */
    render: function () {
        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');

        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);

        if (this.props.editMode) {
            return (<Checkbox
                name={fieldName}
                value={fieldValue.toString()}
                label={field.title}
                onCheck={this._handleCheck}
                defaultSwitched={fieldValue} />
            );
        } else {
            var valLabel = (fieldValue) ? "Yes" : "No";
            return (
                <div>
                    <div className="entity-form-field-label">{field.title}</div>
                    <div className="entity-form-field-value">{valLabel}</div>
                </div>
            );
        }

    },

    /**
     * Handle value change
     */
    _handleCheck: function(evt, isInputChecked) {
        var val = evt.target.value;
        log.info("Setting", this.props.elementNode.getAttribute('name'), "to", isInputChecked);
        this.props.entity.setValue(this.props.elementNode.getAttribute('name'), isInputChecked);
    }
});

module.exports = BoolField;