/**
 * Component that handles the membership field for an entity
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('Chamel');
var TextField = Chamel.TextField;
var IconButton = Chamel.IconButton;
var TextFieldAutoComplete = require("../../mixins/TextFieldAutoComplete.jsx");

/**
 * Members element
 */
var Members = React.createClass({

    mixins: [TextFieldAutoComplete],

    render: function () {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('field');
        var field = this.props.entity.def.getField(fieldName);

        var membersDisplay = [];
        var members = this.props.entity.getValueName(fieldName);

        // If we have members
        if (members) {
            members.map(function (member) {
                membersDisplay.push(
                    <div key={member.key} className="entity-form-field">
                        <div className="entity-form-member-value">{member.value}</div>
                        <div className="entity-form-member-remove">
                            <IconButton
                                onClick={this._removeAttendee.bind(this, member.key)}
                                tooltip={"Remove"}
                                className="cfi cfi-close"
                            />
                        </div>
                        <div className="clearfix"></div>
                    </div>
                );

            }.bind(this));
        }

        var autoCompleteAttributes = {
            autoComplete: true,
            autoCompleteDelimiter: '',
            autoCompleteTrigger: '@',
            autoCompleteTransform: this.transformAutoCompleteSelected,
            autoCompleteGetData: this.getAutoCompleteData,
            autoCompleteSelected: this._addAttendees
        }

        return (
            <div>
                <div className="entity-form-field-value">
                    <TextField
                        {... autoCompleteAttributes}
                        ref="textFieldMembers"
                        floatingLabelText={field.title}/>
                </div>
                {membersDisplay}
            </div>
        );
    },

    _addAttendees: function (member) {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('field');

        // Add the file in the entity object
        this.props.entity.addMultiValue(fieldName, member.payload, member.text);

        this.refs.textFieldMembers.clearValue();
    },

    _removeAttendee: function (memberId) {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('field');

        this.props.entity.remMultiValue(fieldName, memberId);
    }
});

module.exports = Members;