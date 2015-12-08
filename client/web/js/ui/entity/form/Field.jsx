/**
 * Field component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

// Form elements used in the UIML
var TextField = require("./field/TextField.jsx");
var BoolField = require("./field/BoolField.jsx");
var GroupingField = require("./field/GroupingField.jsx");
var ObjectField = require("./field/ObjectField.jsx");
var ObjectMultiField = require("./field/ObjectMultiField.jsx");
var Comments = require("./Comments.jsx");
var Activity = require("./Activity.jsx");
var StatusUpdate = require("./StatusUpdate.jsx");
var ObjectMultiField = require("./field/ObjectMultiField.jsx");
var NumberField = require("./field/NumberField.jsx");
var DateField = require("./field/DateField.jsx");

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

    /**
     * Render this component
     */
    render: function () {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        var classes = "entity-form-field";
        if (xmlNode.getAttribute('class')) {
            classes += " font-style-" + xmlNode.getAttribute('class');
        } else if (!this.props.editMode) {
            classes += " font-style-body-1";
        }

        var fieldContent = null;

        var field = this.props.entity.def.getField(fieldName);

        switch (field.type) {
            case field.types.bool:
                fieldContent = <BoolField {...this.props} />;
                break;
            case field.types.fkey:
            case field.types.fkeyMulti:
                fieldContent = <GroupingField {...this.props} />;
                break;
            case field.types.text:
                fieldContent = <TextField {...this.props} />;
                break;
            case field.types.date:
                fieldContent = <DateField {...this.props} />;
                break;
            case field.types.number:
                fieldContent = <NumberField {...this.props} />;
                break;
            case field.types.object:
                fieldContent = <ObjectField {...this.props} />;
                break;
            case field.types.objectMulti:

                // We do not need to display the objectMulti if we do not have an entity id yet
                if(this.props.entity.id) {

                    // Print object browser based on subtype
                    switch (field.subtype) {
                        case "comment":
                            fieldContent = <Comments {...this.props} />;
                            break;
                        case "activity":
                            fieldContent = <Activity {...this.props} />;
                            break;
                        case "status_update":
                            fieldContent = <StatusUpdate {...this.props} />;
                            break;
                        default:
                            fieldContent = <ObjectMultiField {...this.props} />;
                    }
                }

                break;
            default:
                var fieldValue = this.props.entity.getValue(fieldName);
                fieldContent = <div>Field ToDo: {field.type} - {fieldName}:{fieldValue}</div>;
                break;
        }


        // Print an HR after any field with a value
        //var hr = (!this.props.editMode && this.props.entity.getValue(fieldName)) ? <hr /> : null;
        var hr = null; // May not be needed anymore since we are using spacing better - Sky

        return (
            <div>
                <div className={classes}>
                    {fieldContent}
                </div>
                {hr}
            </div>
        );
    }
});

module.exports = Field;