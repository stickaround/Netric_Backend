/**
 * Text field compnent
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var TextFieldComponent = require('../../../TextField.jsx');

/**
 * Base level element for enetity forms
 */
var TextField = React.createClass({

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
        var multiline = (xmlNode.getAttribute('multiline') == 't') ? true : false;

        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);

        if (this.props.editMode) {
          return (
              <TextFieldComponent
                floatingLabelText={field.title}
                value={fieldValue} 
                multiLine={multiline}
                onChange={this._handleInputChange} />
          );
        } else {
          return (
            <div>{fieldValue}</div>
          );
        }
        
    },

    /**
     * Handle value change
     */
    _handleInputChange: function(evt) {
        var val = evt.target.value;
        this.props.entity.setValue(this.props.xmlNode.getAttribute('name'), val);
    }
});

module.exports = TextField;