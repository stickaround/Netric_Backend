/**
 * Plugin for following up a customer or opportunity
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var IconButton = Chamel.IconButton;
var FlatButton = Chamel.FlatButton;
var AppBar = Chamel.AppBar;

var Followup = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * The entity that we want to follow-up
         *
         * @type {Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Function that should be called when the user selects an action
         *
         * @type {function}
         */
        onActionFinished: React.PropTypes.func,

        /**
         * Function that is called when clicking the back button
         *
         * @type {function}
         */
        onNavBtnClick: React.PropTypes.func
    },

    render: function () {

        // Determine if we need to display the toolbar or just the icon button
        var toolBar = null;
        if (!this.props.hideToolbar) {
            var elementLeft = (
                <IconButton
                    iconClassName='fa fa-arrow-left'
                    onClick={this._handleBackButtonClicked}
                />
            );

            toolBar = (
                <AppBar
                    iconElementLeft={elementLeft}
                    title={this.props.title}>
                </AppBar>
            );
        }

        return (
            <div className='entity-form'>
                {toolBar}
                <div className="row entity-form-group">
                    <div className="col-small-3">
                        <FlatButton label='Create Task' onClick={this._handleFollowupAction.bind(this, 'task')}/>
                    </div>
                    <div className="col-small-7">
                        Create a task to be completed by you or someone else in your organization.
                    </div>
                </div>
                <div className="row entity-form-group">
                    <div className="col-small-3">
                        <FlatButton label='Schedule Event' onClick={this._handleFollowupAction.bind(this, 'calendar_event')}/>
                    </div>
                    <div className="col-small-7">
                        Create a future calendar event that is associated with this customer.
                    </div>
                </div>
                <div className="row entity-form-group">
                    <div className="col-small-3">
                        <FlatButton label='Record Activity' onClick={this._handleFollowupAction.bind(this, 'activity')}/>
                    </div>
                    <div className="col-small-7">
                        Record an activity like a "Phone Call" or "Sent a Letter."
                    </div>
                </div>
            </div>
        );
    },

    /**
     * Function that is called when the back back button is clicked
     *
     * @param evt
     * @private
     */
    _handleBackButtonClicked: function (evt) {
        if (this.props.onNavBtnClick) {
            this.props.onNavBtnClick();
        }
    },

    /**
     * Handles the follow-up actions depending on which type of button is clicked (task, event, or activity)
     *
     * @param objType The type of action that is clicked
     * @private
     */
    _handleFollowupAction: function(objType) {
        if(this.props.onActionFinished) {
            var params = [];
            params['customer_id'] = this.props.entity.id;
            params['customer_id_val'] = encodeURIComponent(this.props.entity.getName());

            var postAction = {
                type: 'createNewEntity',
                data: {
                    objType: objType,
                    params: params
                }
            }
            this.props.onActionFinished(postAction);
        }
    }
});

module.exports = Followup;