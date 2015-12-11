/**
 * List Item used where object type is 'status_update'
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var UserProfileImage = require('../../UserProfileImage.jsx');

/**
 * List item for an StatusUpdate
 */
var StatusUpdateItem = React.createClass({

    propTypes: {
        entity: React.PropTypes.object,

        /**
         * Function that will handle the clicking of object reference link
         *
         * @var {func}
         */
        onEntityListClick: React.PropTypes.func
    },

    render: function () {
        var entity = this.props.entity;

        var timestamp = entity.getTime(null, true);
        var ownerId = entity.getValue('owner_id');
        var ownerName = entity.getValueName('owner_id', ownerId);
        var notes = this._processNotes(entity.getValue('comment'));

        return (
            <div className='entity-browser-activity'>
                <div className='entity-browser-activity-img'>
                    <UserProfileImage width={32} userId={ownerId}/>
                </div>
                <div className='entity-browser-activity-details'>
                    <div className='entity-browser-activity-header'>
                        {ownerName}
                    </div>
                    <div className='entity-browser-activity-body'>
                        <div dangerouslySetInnerHTML={notes}/>
                    </div>
                    <div className='entity-browser-activity-title'>
                        {timestamp}
                        <div>
                            <a href='javascript: void(0);' onClick={this._handleObjReferenceClick}>Details</a>
                        </div>
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
     * Handles the clicking of object reference link
     *
     * @private
     */
    _handleObjReferenceClick: function () {
        if (this.props.onEntityListClick) {
            this.props.onEntityListClick('status_update', this.props.entity.id);
        }
    }
});

module.exports = StatusUpdateItem;