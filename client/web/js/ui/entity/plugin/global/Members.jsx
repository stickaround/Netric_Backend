/**
 * Plugin for Members
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('Chamel');
var TextField = Chamel.TextField;
var IconButton = Chamel.IconButton;
var TextFieldAutoComplete = require("../../../mixins/TextFieldAutoComplete.jsx");

/**
 * Plugin that handles the membership field for an entity
 */
var Members = React.createClass({

    mixins: [TextFieldAutoComplete],

    /**
     * Expected props
     */
    propTypes: {
        eventsObj: React.PropTypes.object,
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    getInitialState: function () {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('field');
        var field = this.props.entity.def.getField(fieldName);
        var members = this.props.entity.getValueName(fieldName);

        // Setup the entity to accept members
        this.props.entity.setupMembers(fieldName);

        var stateMembers = [];

        // If we have members
        if (members) {
            members.map(function (member) {
                var memberEntity = this.props.entity.members.add(member);
                stateMembers.push(memberEntity);
            }.bind(this));
        }

        // Return the initial state
        return {
            members: stateMembers
        };
    },

    /**
     * Render the component
     */
    render: function () {
        var membersDisplay = []
        for(var idx in this.state.members) {
            var member = this.state.members[idx];

            membersDisplay.push(
                <div key={idx} className="entity-form-field">
                    <div className="entity-form-member-value">{member.name}</div>
                    <div className="entity-form-member-remove">
                        <IconButton
                            onClick={this._removeMember.bind(this, member.id)}
                            tooltip={"Remove"}
                            className="cfi cfi-close"
                        />
                    </div>
                    <div className="clearfix"></div>
                </div>
            );
        }

        var autoCompleteAttributes = {
            autoComplete: true,
            autoCompleteDelimiter: '',
            autoCompleteTrigger: '@',
            autoCompleteTransform: this.transformAutoCompleteSelected,
            autoCompleteGetData: this.getAutoCompleteData,
            autoCompleteSelected: this._addMember
        }

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('field');
        var field = this.props.entity.def.getField(fieldName);

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
        )
            ;
    },

    /**
     * Add a member to the entity
     *
     * @param {object} selectedMember The member selected in the autocomplete
     * @private
     */
    _addMember: function (selectedMember) {

        var entityMember = this.props.entity.members.add(selectedMember);

        // Override the member name with the transformed text so the member will be notified
        entityMember.name = this.transformAutoCompleteSelected(selectedMember);

        var stateMember = this.state.members;
        stateMember.push(entityMember);

        this.setState({member: stateMember});

        this.refs.textFieldMembers.clearValue();
    },

    /**
     * Remove a member to the entity
     *
     * @param {int} memberId The memberId that will be removed
     * @private
     */
    _removeMember: function (memberId) {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('field');

        this.props.entity.remMultiValue(fieldName, memberId);

        // TODO loop thru the memberstate and remove the member
    },
});

module.exports = Members;