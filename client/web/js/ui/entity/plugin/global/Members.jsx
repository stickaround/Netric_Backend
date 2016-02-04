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

        // If we have existing members in the entity, then lets add it in the members model
        if (members) {
            members.map(function (member) {
                var memberEntity = this.props.entity.members.add(member);
            }.bind(this));
        }

        // Return the initial state
        return {
            members: this.props.entity.members.getAll()
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
                    <div className="entity-form-member-value">{this.props.entity.members.extractNameReference(member.name).name}</div>
                    <div className="entity-form-member-remove">
                        <IconButton
                            onClick={this._removeMember.bind(this, member.id, member.name)}
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

        var entityMember = this.props.entity.members.add();

        // Set the member name with the transformed text ([user:userId:userName]) so the member will be notified
        entityMember.name = this.transformAutoCompleteSelected(selectedMember);

        // Update the state members
        this.setState({
            member: this.props.entity.members.getAll()
        });

        this.refs.textFieldMembers.clearValue();
    },

    /**
     * Remove a member to the entity
     *
     * @param {int} id The Id that will be removed
     * @param {int} name The name that will be removed. if Id is null, then we will use the name to remove the member
     * @private
     */
    _removeMember: function (id, name) {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('field');

        // We will only remove the member in the entity if meber has an id
        if(!id) {
            this.props.entity.remMultiValue(fieldName, id);
        }

        this.props.entity.members.remove(id, name);

        // Update the state members
        this.setState({
            member: this.props.entity.members.getAll()
        });
    },
});

module.exports = Members;