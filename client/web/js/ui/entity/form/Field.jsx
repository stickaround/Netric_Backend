/**
 * Field component
 *

 */
'use strict';

var React = require('react');

// Form elements used in the UIML
var TextField = require("./field/TextField.jsx");
var BoolField = require("./field/BoolField.jsx");
var GroupingField = require("./field/GroupingField.jsx");
var ObjectField = require("./field/ObjectField.jsx");
var ObjectMultiField = require("./field/ObjectMultiField.jsx");
var StatusUpdate = require("./StatusUpdate.jsx");
var ObjectMultiField = require("./field/ObjectMultiField.jsx");
var NumberField = require("./field/NumberField.jsx");
var DateField = require("./field/DateField.jsx");
var Comments = require("./Comments.jsx");
var Activity = require("./Activity.jsx");
var Image = require("./Image.jsx");

/**
 * Base level element for enetity forms
 */
var Field = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * Current element node level
         *
         * @type {entity/form/FormNode}
         */
        elementNode: React.PropTypes.object.isRequired,

        /**
         * Entity being edited
         *
         * @type {entity\Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Generic object used to pass events back up to controller
         *
         * @type {Object}
         */
        eventsObj: React.PropTypes.object,

        /**
         * Flag indicating if we are in edit mode or view mode
         *
         * @type {bool}
         */
        editMode: React.PropTypes.bool
    },

    /**
     * Render this component
     */
    render: function () {

        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');
        var classes = "entity-form-field";
        if (elementNode.getAttribute('class')) {
            classes += " font-style-" + elementNode.getAttribute('class');
        } else if (!this.props.editMode) {
            classes += " font-style-body-1";
        }

        var fieldContent = null;
        var field = this.props.entity.def.getField(fieldName);

        // If we have an invalid field, then let's throw an error.
        if(!field) {
            throw 'Trying to render an invalid field. Check the field name: ' + fieldName;
        }

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
            case field.types.timestamp:
            case field.types.date:
                fieldContent = <DateField {...this.props} />;
                break;
            case field.types.integer:
            case field.types.number:
                fieldContent = <NumberField {...this.props} />;
                break;
            case field.types.object:

                // If the file object is used as profile image
                if(field.subtype == "file" && elementNode.getAttribute('profile_image') == "t") {
                    fieldContent = <Image {...this.props} label="Profile Picture" />;
                } else {
                    fieldContent = <ObjectField {...this.props} />;
                }
                break;
            case field.types.objectMulti:

                // We do not need to display the objectMulti if we do not have an entity id yet
                if (this.props.entity.id) {

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
                } else if(field.subtype != 'comment') {

                    // Display information for comment subtype if the entity is not yet saved.
                    fieldContent = <div>Please save changes to view more details.</div>;
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