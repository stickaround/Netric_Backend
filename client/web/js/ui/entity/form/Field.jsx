/**
 * Field component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

// Form elements used in the UIML
var TextField = require("./field/TextField.jsx");
var GroupingField = require("./field/GroupingField.jsx");

/**
 * Base level element for enetity forms
 */
var Field = React.createClass({

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

		
        var fieldContent = null;

        var field = this.props.entity.def.getField(fieldName);

        switch (field.type)
        {
        case field.types.fkey:
        case field.types.fkeyMulti:
       		fieldContent = <GroupingField {...this.props} />
        	break;
        case field.types.text:
        	fieldContent = <TextField {...this.props} />
        	break;
        default:
        	var fieldValue = this.props.entity.getValue(fieldName);
        	fieldContent = <div>Field ToDo: {fieldName}:{fieldValue}</div>;
        	break;
        }
        /*
        case 'object':
            break;
        case 'bool':
            break;
        case 'alias':
            break;
        case 'date':
            break;
        case 'timestamp':
            break;
        case 'number':
        case 'numeric':
        case 'integer':
        case 'float':
            break;
        case 'text':
        default:
            this.input = new AntObject_FieldInput_Text(this, con, opts);
            break;
        */

        return (
            <div>
                {fieldContent}
            </div>
        );
    }
});

module.exports = Field;