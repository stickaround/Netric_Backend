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
        onEntityListClick: React.PropTypes.func,

        /**
         * Callback used when an individual entity is removed
         *
         * @type {function}
         */
        onRemoveEntity: React.PropTypes.func
    },

    render: function () {
        var entity = this.props.entity;

        var timestamp = entity.getTime(null, true);
        var ownerId = entity.getValue('owner_id');
        var ownerName = entity.getValueName('owner_id', ownerId);
        var notes = this._processNotes(entity.getValue('comment'));
        var objReference = entity.getValue('obj_reference');
        var objectLinkReference = null;

        // Check if this status update has object reference, then we will set the entity onclick
        if (objReference) {

            // Get the object type of this status update
            var objType = objReference.split(':')[0];
            var objType = objType[0].toUpperCase() + objType.slice(1);

            objectLinkReference = (
                <div>
                    {objType}: <a href='javascript: void(0);'
                                 onClick={this._handleObjReferenceClick}>{entity.getValueName('obj_reference', objReference)}</a>
                </div>
            );
        }

        return (
            <div className='entity-browser-activity'>
                <div className='entity-browser-activity-img'>
                    <UserProfileImage width={32} userId={ownerId}/>
                </div>
                <div className='entity-browser-activity-details'>
                    <div className='entity-browser-activity-header'>
                        {ownerName}
                        {objectLinkReference}
                    </div>
                    <div className='entity-browser-activity-body'>
                        <div dangerouslySetInnerHTML={notes}/>
                    </div>
                    <div className='entity-browser-activity-title'>
                        {timestamp}
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
