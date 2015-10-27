/**
 * Component that handles rendering both an add comment form and comments list into an entity form
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var EntityCommentsController = require('../../../controller/EntityCommentsController');
var controller = require("../../../controller/controller");

/**
 * Render comments into an entity form
 */
var Comments = React.createClass({

    propTypes: {
        id: React.PropTypes.number,
        name: React.PropTypes.string,
        onRemove: React.PropTypes.func
    },

    componentDidMount: function() {
        // TODO: Only call loadcomments if this device > medium
        this._loadComments();
    },

    render: function() {

        var actionButtons = null;
        // TODO: render loadComments icon if device < large

        return (<div ref="comcon">{actionButtons}</div>);
    },

    /**
     * Load the comments controller either inline or as dialog for smaller devices
     *
     * @private
     */
    _loadComments: function() {

        // TODO: this should change to dialog if device is < large
        var controllerType = controller.types.FRAGMENT;

        var comments = new EntityCommentsController();
        comments.load({
            type: controllerType,
            title: "Comments",
            objType: "comment"
        }, this.refs.comcon.getDOMNode());
    }
});

module.exports = Comments;
