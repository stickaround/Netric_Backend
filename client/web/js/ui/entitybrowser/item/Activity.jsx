/**
 * List Item used where object type is 'activity'
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var UserProfileImage = require('../../UserProfileImage.jsx');
var File = require("../../fileupload/File.jsx");

/**
 * List item for an activity
 */
var ActivityItem = React.createClass({

    render: function () {
        var entity = this.props.entity;

        var headerTime = entity.getTime(null, true);
        var userId = entity.getValue('user_id');
        var entityId = entity.getValue('id');
        var owner = entity.getValueName('user_id', userId);
        var activity = entity.getActivity();

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

        if(activity.notes) {
            var notes = this._processNotes(activity.notes);

            displayNotes = (
                <div dangerouslySetInnerHTML={notes}/>
            )
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
                        {owner} {activity.description} {activity.name}
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

});

module.exports = ActivityItem;