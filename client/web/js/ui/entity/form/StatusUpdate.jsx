/**
 * Component that handles rendering Status Updates
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var Activity = require("./Activity.jsx");
var Chamel = require('chamel');
var TextField = Chamel.TextField;
var FlatButton = Chamel.FlatButton;
var IconButton = Chamel.IconButton;

/**
 * Render Status into an entity form
 */
var StatusUpdate = React.createClass({

    propTypes: {
        entity: React.PropTypes.object,

        /**
         * Type of activity to be displayed
         *
         * Possible values are: activity, status_update
         */
        type: React.PropTypes.string,

        /**
         * Reference field to be filtered in the entity browser list
         *
         * Possible values are: obj_reference, associations
         */
        referenceField: React.PropTypes.string,
    },

    getDefaultProps: function () {
        return {
            type: 'status_update',
            referenceField: 'associations'
        }
    },

    getInitialState: function () {

        // Return the initial state
        return {
            refresh: false
        };
    },

    render: function () {
        return (
            <div className="entity-comments">
                <div className="entity-comments-form">
                    <div className="entity-comments-form-center">
                        <TextField ref="statusInput" hintText="Add Status" multiLine={true}/>
                    </div>
                    <div className="entity-comments-form-right">
                        <FlatButton
                            label="Send"
                            iconClassName="fa fa-paper-plane"
                            onClick={this._handleStatusSend}
                            />
                    </div>
                </div>
                <Activity
                    {...this.props}
                    refresh={this.state.refresh}
                    />
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
        var objRefeference = this.props.entity.objType + ":" + this.props.entity.id;

        this.props.entity.sendStatusUpdate(status, objRefeference, this._refreshStatusList);
    },

    /**
     * Refreshes the status update list
     *
     * @private
     */
    _refreshStatusList: function () {
        this.refs.statusInput.clearValue();
        this.setState({refresh: true});
    }
});

module.exports = StatusUpdate;
