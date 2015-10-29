/**
 * Main controller view for entity comments
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var controller = require("../controller/controller");
var Device = require("../Device");
var Chamel = require("chamel");
var TextField = Chamel.TextField;
var FlatButton = Chamel.FlatButton;
var IconButton = Chamel.IconButton;

/**
 * Handle rendering comments browser inline and add comment
 */
var EntityComments = React.createClass({

    propTypes: {
        name: React.PropTypes.string,
        onAddComment: React.PropTypes.func,
        deviceSize: React.PropTypes.number,
        commentsBrowser: React.PropTypes.object
    },

    componentDidMount: function() {
        this._loadCommentsBrowser();
    },

    render: function() {

        // Render slightly different forms based on the current device size
        var addCommentForm = null;
        if (this.props.deviceSize > Device.sizes.small) {
            // medium-xlarge devices will show the comments form inline after the browser
            addCommentForm = (<div>Comments not yet enabled for this device</div>);
        } else {
            // Small devices show the comments form as a floating toolbar
            // TODO: Add - <div className="entity-comments-form-left">[i]</div>
            addCommentForm = (
              <div className="entity-comments-form">
                  <div className="entity-comments-form-center">
                    <TextField ref="commInput" hintText="Add Comment" multiLine={true} />
                  </div>
                  <div className="entity-comments-form-right">
                    <IconButton
                        tooltip="Send Comment"
                        iconClassName="fa fa-paper-plane"
                        onClick={this._handleCommentSend}
                    />
                  </div>
              </div>
            );
        }

        return (
            <div className="entity-comments">
                <div ref="commCon"></div>
                {addCommentForm}
            </div>
        );
    },

    /**
     * Load browser inline (FRAGMENT) with objType='comment'
     *
     * @private
     */
    _loadCommentsBrowser: function() {

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var BrowserController = require("../controller/EntityBrowserController");
        var browser = new BrowserController();
        browser.load({
            type: controller.types.FRAGMENT,
            title: "Comments",
            objType: "comment",
            hideToolbar: true
        }, this.refs.commCon.getDOMNode());
    },

    /**
     * Handle when a user hits send on the comment form
     *
     * @param {DOMEvent} evt
     * @private
     */
    _handleCommentSend: function(evt) {
        var value = this.refs.commInput.getValue();
        if (this.props.onAddComment) {
            this.props.onAddComment(value);
        }

        // Clear the form
        this.refs.commInput.setValue("");
    }
});

module.exports = EntityComments;
