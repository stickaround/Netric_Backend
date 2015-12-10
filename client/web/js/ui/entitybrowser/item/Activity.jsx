/**
 * List Item used where object type is 'activity'
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var UserProfileImage = require('../../UserProfileImage.jsx');
var File = require('../../fileupload/File.jsx');

/**
 * List item for an activity
 */
var ActivityItem = React.createClass({

    propTypes: {
        entity: React.PropTypes.object,

        /**
         * The main entity's object reference
         *
         * @var {string objType:eid}
         */
        objReference: React.PropTypes.string,

        /**
         * Function that will handle the clicking of object reference link
         *
         * @var {func}
         */
        onObjReferenceClick: React.PropTypes.func
    },

    render: function () {
        var entity = this.props.entity;

        var headerTime = entity.getTime(null, true);
        var userId = entity.getValue('user_id');
        var userName = entity.getValueName('user_id', userId);
        var activity = this._getActivityDetails();

        // Get the attached files
        var attachedFiles = [];
        var files = entity.getAttachments();
        for (var idx in files) {
            var file = files[idx];

            // Check if file is an image
            attachedFiles.push(
                <File
                    key={idx}
                    index={idx}
                    file={file}
                    />
            );
        }

        var displayNotes = null;

        if (activity.notes) {
            var notes = this._processNotes(activity.notes);

            displayNotes = (
                <div dangerouslySetInnerHTML={notes}/>
            )
        }

        var activityName = activity.name;

        /**
         * We need to check if main entity's object reference and this activity's object reference is the same
         * If they are the same, then we do not to create a href link for this activity's object reference
         */
        if (activity.objReference && this.props.objReference != activity.objReference) {
            activityName = (<a href='javascript: void(0);' onClick={this._handleObjReferenceClick.bind(this, activity.objReference, activity.name)}>{activity.name}</a>);
        }

        return (
            <div className='entity-browser-activity'>
                <div className='entity-browser-activity-img'>
                    <UserProfileImage width={32} userId={userId}/>
                </div>
                <div className='entity-browser-activity-details'>
                    <div className='entity-browser-activity-header'>
                        {headerTime}
                    </div>
                    <div className='entity-browser-activity-title'>
                        {userName} {activity.description} {activityName}

                    </div>
                    <div className='entity-browser-activity-body'>
                        {displayNotes}
                        {attachedFiles}
                    </div>
                </div>
            </div>
        );
    },

    /**
     * Render text to HTML for viewing
     *
     * @param {string} val The value to process
     */
    _processNotes: function (val) {

        // Convert new lines to line breaks
        if (val) {
            var re = new RegExp('\n', 'gi');
            val = val.replace(re, '<br />');
        }

        // Convert email addresses into mailto links?
        //fieldValue = this._activateLinks(fieldValue);

        /*
         * TODO: Make sanitized html object. React requires this because
         * setting innherHTML is a pretty dangerous option in that it
         * is often used for cross script exploits.
         */
        return (val) ? {__html: val} : null;
    },

    /**
     * Get the activity details of the entity
     *
     * @return {object}
     */
    _getActivityDetails: function () {

        var entity = this.props.entity;
        var direction = entity.getValue('direction');
        var typeId = entity.getValue('type_id');
        var activityType = entity.getValueName('type_id', typeId);

        // Create the activity object with a name index
        var activity = {
            name: entity.getValue('name'),
            notes: entity.getValue('notes'),
            objReference: entity.getValue('obj_reference')
        };

        switch (activityType.toLowerCase()) {
            case 'email':
                if (direction == 'i') {
                    activity.description = 'received an email ';
                } else {
                    activity.description = 'sent an email ';
                }

                break;
            case 'phone call':
                if (direction == 'i') {
                    activity.description = 'logged an innbound call ';
                } else {
                    activity.description = 'logged an outbound call ';
                }
            case 'comment':
                activity.description = 'commented on ';

                break;
            case 'status update':
                activity.description = 'added a ';
                activity.name = activityType;

                break;
            default:
                var verb = entity.getValue('verb');
                if (verb == 'create' || verb == 'created') {
                    activity.description = 'created a new ' + activityType + ' ';
                } else {
                    activity.description = verb + ' ';
                }

                activity.notes = null;

                break;
        }

        return activity;
    },

    /**
     * Handles the clicking of object reference link
     *
     * @param {string objType:eid} string   The object reference of this activity
     * @param {string} title                Title of the object reference
     * @private
     */
    _handleObjReferenceClick: function (objReference, title) {
        var parts = objReference.split(':');
        var objType = parts[0];
        var eid = parts[1] || null;

        if(this.props.onObjReferenceClick) {
            this.props.onObjReferenceClick(objType, eid, title);
        }
    }
});

module.exports = ActivityItem;