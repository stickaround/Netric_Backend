/**
 * All additional/custom fields
 *
 */
'use strict';

var React = require('react');
var Field = require('./Field.jsx');

/**
 * All additional will gather all custom (non-system) fields and print them
 *
 * This allows users to add custom fields without having to worry about placing
 * them into a form.
 */
var AllAdditional = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * Current xml node level
         *
         * @type {XMLNode}
         */
        xmlNode: React.PropTypes.object,

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

    render: function () {

        let fields = this.props.entity.def.getFields();
        let displayFields = [];

        if (fields) {
            fields.map(function (field, idx) {

                // Make sure that we have useWhen field attribute and the field is not a system field
                if (!field.system) {

                    // Get the decoded value of useWhen, if it is available
                    let useWhenObj = field.getDecodedUseWhen();

                    // If the useWhen value did not match with the entity field, then let's return and move to the next field
                    if (field.useWhen && this.props.entity.getValue(useWhenObj.name) != useWhenObj.value) {
                        return;
                    }

                    // Let's clone the props.xmlNode, so we assign the current field.name as its name attribute
                    let xmlNode = this.props.xmlNode.cloneNode(true);

                    // Set the attribute name of the current field
                    xmlNode.setAttribute("name", field.name);

                    displayFields.push(
                        <Field
                            key={idx}
                            entity={this.props.entity}
                            eventsObj={this.props.eventsObj}
                            editMode={this.props.editMode}
                            xmlNode={xmlNode}
                            />
                    )
                }
            }.bind(this))
        }

        return (
            <div className="entity-form-field">
                {displayFields}
            </div>
        );
    }
});

module.exports = AllAdditional;