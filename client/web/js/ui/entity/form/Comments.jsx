/**
 * Component that handles rendering both an add comment form and comments list into an entity form
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var EntityCommentsController = require('../../../controller/EntityCommentsController');
var controller = require("../../../controller/controller");
var netric = require("../../../base");
var Device = require("../../../Device");
var Chamel = require("chamel");
var IconButton = Chamel.IconButton;
var FlatButton = Chamel.FlatButton;

/**
 * Constant indicating the smallest device that we can print comments in
 *
 * All other devices will open comments in a dialog when clicked
 *
 * @type {number}
 * @private
 */
var _minimumInlineDeviceSize = Device.sizes.large;

/**
 * Render comments into an entity form
 */
var Comments = React.createClass({

    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    getInitialState: function() {
        return {
            commentsController: null
        }
    },

    componentDidMount: function() {

        this._loadComments();
    },

    /**
     * Invoked immediately after the component's updates are flushed to the DOM.
     *
     * This method is not called for the initial render and we use it to check if
     * the id has changed - meaning a new entity was saved.
     *
     * @param {prevProps} object    Contains the preview props before the update
     */
    componentDidUpdate: function(prevProps) {
        
        // Only load the comments if the previous num_comments and current entity's num_comments are not equal
        if(this.props.entity.getValue('num_comments') != prevProps.entity.getValue('num_comments')) {
            this._loadComments();
        }
    },

    render: function() {

        var numCommentsLabel = "0 Comments";

        if (this.props.entity.getValue("num_comments")) {
            numCommentsLabel = this.props.entity.getValue("num_comments") + " Comments";
        }

        var content = null;

        // Smaller devices should print number of comments and a link
        if (netric.getApplication().device.size < _minimumInlineDeviceSize) {
            content = (
                <div>
                    <IconButton
                        onClick={this._loadComments}
                        iconClassName="fa fa-comment-o"
                    />
                    <FlatButton
                        label={numCommentsLabel}
                        onClick={this._loadComments}
                    />
                </div>
            );
        }

        // If this is a new entity display nothing
        if (!this.props.entity.id) {
            content = <div />;
        }

        return (<div ref="comcon">{content}</div>);


    },

    /**
     * Load the comments controller either inline or as dialog for smaller devices
     *
     * @private
     */
    _loadComments: function() {

        // Only load comments if this device displays inline comments (size > medium)
        if (netric.getApplication().device.size < _minimumInlineDeviceSize) {
            return;
        }

        // We only display comments if workign with an actual entity
        if (!this.props.entity.id) {
            return;
        }

        // Check if we have already loaded the controller
        if (this.state.commentsController) {
            // Just refresh the results and return
            this.state.commentsController.refresh();
            return;
        }

        // Default to a dialog
        var controllerType = controller.types.DIALOG;
        var inlineCon = null;
        var hideToolbar = false;

        // If we are on a larger device then print inline
        if (netric.getApplication().device.size >= _minimumInlineDeviceSize) {
            controllerType = controller.types.FRAGMENT;
            inlineCon = ReactDOM.findDOMNode(this.refs.comcon);
            hideToolbar = true;
        }

        var comments = new EntityCommentsController();
        comments.load({
            type: controllerType,
            title: "Comments",
            objType: "comment",
            hideToolbar: hideToolbar,
            objReference: this.props.entity.objType + ":" + this.props.entity.id
        }, inlineCon);

        this.setState({commentsController: comments});
    }
});

module.exports = Comments;
