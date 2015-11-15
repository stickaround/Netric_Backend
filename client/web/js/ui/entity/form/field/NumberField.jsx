/**
 * Numeric field input
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var TextFieldComponent = Chamel.TextField;

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

    getInitialState: function() {
        return ({
            errorText: null
        });
    },

    render: function() {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);

        if (this.props.editMode) {

            return (
                <TextFieldComponent
                    floatingLabelText={field.title}
                    value={fieldValue}
                    errorText={this.state.errorText}
                    onChange={this._handleInputChange} />
            );


        } else {

            // If there is no value then we don't need to show this field at all
            if (!fieldValue) {
                return (<div />);
            } else {
                return (
                    <div>
                        <div className="entity-form-field-label">{field.title}</div>
                        <div className="entity-form-field-value">{fieldValue}</div>
                    </div>
                );
            }
        }
    },

    /**
     * Handle value change
     */
    _handleInputChange: function(evt) {
        var value = evt.target.value;
        var isNumeric = !isNaN(parseFloat(value)) && isFinite(value);

        this.setState({
            errorText: isNumeric ? null : 'This field must be numeric.',
        });

        if (isNumeric) {
            this.props.entity.setValue(this.props.xmlNode.getAttribute('name'), value);
        }
    },


});

module.exports = NumberField;