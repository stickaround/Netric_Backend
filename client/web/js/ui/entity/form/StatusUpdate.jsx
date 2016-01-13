/**
 * Handles the rendering a status update form and displaying the entity browser list for status update/activity
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var CustomEventTrigger = require("../../mixins/CustomEventTrigger.jsx");
var controller = require('../../../controller/controller');
var Where = require('../../../entity/Where');
var Chamel = require('chamel');
var DropDownMenu = Chamel.DropDownMenu;
var TextField = Chamel.TextField;
var FlatButton = Chamel.FlatButton;
var IconButton = Chamel.IconButton;

/**
 * Render Status into an entity form
 */
var StatusUpdate = React.createClass({

    mixins: [CustomEventTrigger],

    propTypes: {
        entity: React.PropTypes.object
    },

    render: function () {

        return (
            <div className='entity-comments'>
                <div className='entity-comments-form'>
                    <div className='entity-comments-form-center'>
                        <TextField ref='statusInput' hintText='Add Status' multiLine={true}/>
                    </div>
                    <div className='entity-comments-form-right'>
                        <FlatButton
                            label='Send'
                            iconClassName='fa fa-paper-plane'
                            onClick={this._handleStatusSend}
                            />
                    </div>
                </div>
            </div>
        );
    },

    /**
     * Handles the sending of status updates
     *
     * @private
     */
    _handleStatusSend: function () {
        var status = this.refs.statusInput.getValue();
        var statusUpdateManager = require('../../../entity/statusUpdateManager');

        var refreshFunc = function() {

            // Refresh the activity list
            this.triggerCustomEvent("entityBrowserRefresh");
        }.bind(this);

        // Send the status
        statusUpdateManager.send(status, this.props.entity, refreshFunc);

        // Clear the status input
        this.refs.statusInput.clearValue();
    }
});

module.exports = StatusUpdate;
