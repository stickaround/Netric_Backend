/**
 * Component that handles rendering both an add comment form and comments list into an entity form
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
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

    componentDidMount: function() {

        // Only load comments if this device displays inline comments (size > medium)
        if (netric.getApplication().device.size >= _minimumInlineDeviceSize) {
            this._loadComments();
        }
    },

    render: function() {

        var numCommentsLabel = "0 Comments";

        if (this.props.entity.getValue("num_comments")) {
            numCommentsLabel = this.props.entity.getValue("num_comments") + " Comments";
        }

        var actionButtons = null;
        // Smaller devices should print number of comments and a link
        if (netric.getApplication().device.size < _minimumInlineDeviceSize) {
            actionButtons = (
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

        return (<div ref="comcon">{actionButtons}</div>);


    },

    /**
     * Load the comments controller either inline or as dialog for smaller devices
     *
     * @private
     */
    _loadComments: function() {

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
    }
});

module.exports = Comments;
