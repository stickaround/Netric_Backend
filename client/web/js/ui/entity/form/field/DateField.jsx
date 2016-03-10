/**
 * Date field component
 *

 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var DatePicker = Chamel.DatePicker;

/**
 * Base level element for enetity forms
 */
var DateField = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    render: function() {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);

        if (this.props.editMode) {

            return (
                <DatePicker
                    floatingLabelText={field.title}
                    value={fieldValue}
                    type="date"
                    onChange={this._handleInputChange} />
            );

        } else {

            if (fieldValue) {
                return (
                    <div>
                        <div className="entity-form-field-label">
                            {field.title}
                        </div>
                        <div className="entity-form-field-value">
                            {this.props.entity.getTime(fieldName, true)}
                        </div>
                    </div>
                );
            } else {
                // Hide if no value was set
                return (<div />);
            }
        }

    },

    /**
     * Handle value change
     */
    _handleInputChange: function(evt, date) {
        this.props.entity.setValue(this.props.xmlNode.getAttribute('name'), date);
    }

});

module.exports = DateField;