/**
 * List Item used where object type is 'activity'
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var UserProfileImage = require('../../UserProfileImage.jsx');

/**
 * List item for an activity
 */
var ActivityItem = React.createClass({

    render: function () {
        var entity = this.props.entity;

        console.log(entity);

        var headerTime = entity.getTime(null, true);
        var userId = entity.getValue('user_id');
        var owner = entity.getValueName('user_id', userId);
        var notes = this._processNotes(entity.getValue(notes));
        var activity = entity.getActivity();
        
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
                        <div dangerouslySetInnerHTML={notes}/>
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
    _processNotes: function (activity) {

        // Convert new lines to line breaks
        if (activity) {
            var re = new RegExp('\n', 'gi');
            activity = activity.replace(re, '<br />');
        }

        // Convert email addresses into mailto links?
        //fieldValue = this._activateLinks(fieldValue);

        /*
         * TODO: Make sanitized html object. React requires this because
         * setting innherHTML is a pretty dangerous option in that it
         * is often used for cross script exploits.
         */
        return (activity) ? {__html: activity} : null;
    },

});

module.exports = ActivityItem;