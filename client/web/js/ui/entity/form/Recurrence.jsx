/**
 * Entity Recurrence
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

var Recurrence = React.createClass({

    render: function() {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);

        // TODO: We have to load the recurrence plugin here and handle updating this.props.entity

        if (this.props.editMode) {

            return (
                <div>Put recurrence plugin here</div>
            );


        } else {

            // If there is no value then we don't need to show this field at all
            if (!fieldValue) {
                return (<div />);
            } else {
                return (
                    <div>
                        <div className="entity-form-field-label">Repeats</div>
                        <div className="entity-form-field-value">Human Description Here</div>
                    </div>
                );
            }
        }
    }

});

module.exports = Recurrence;