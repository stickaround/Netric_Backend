/**
 * Main controller view for entity comments
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var controller = require("../controller/controller");
var Device = require("../Device");
var Where = require("../entity/Where");
var File = require("./fileupload/File.jsx");
var Chamel = require("chamel");
var TextField = Chamel.TextField;
var FlatButton = Chamel.FlatButton;
var IconButton = Chamel.IconButton;
var AppBar = Chamel.AppBar;

/**
 * Handle rendering comments browser inline and add comment
 */
var EntityComments = React.createClass({

    propTypes: {
        name: React.PropTypes.string,
        // Navigation back button - left arrow to the left of the title
        onNavBtnClick: React.PropTypes.func.isRequired,
        onAddComment: React.PropTypes.func,
        onAttachFiles: React.PropTypes.func,
        onRemoveFiles: React.PropTypes.func,
        deviceSize: React.PropTypes.number,
        commentsBrowser: React.PropTypes.object,
        // Get the objReference - the object for which we are displaying/adding comments
        objReference: React.PropTypes.string,
        hideAppBar: React.PropTypes.bool,
        attachedFiles: React.PropTypes.array,
    },

    componentDidMount: function () {
        this._loadCommentsBrowser();
    },

    getInitialState: function () {
        return {
            commBrowser: null
        };
    },

    render: function () {

        var toolBar = null;

        if (!this.props.hideAppBar) {
            var elementLeft = (
                <IconButton
                    iconClassName="fa fa-arrow-left"
                    onClick={this._handleBackButtonClicked}
                    />
            );
            var elementRight = null;

            toolBar = (
                <AppBar
                    iconElementLeft={elementLeft}
                    title="Comments">
                    {elementRight}
                </AppBar>
            );
        }

        // Render slightly different forms based on the current device size
        var addCommentForm = null;
        if (this.props.deviceSize > Device.sizes.small) {
            // medium-xlarge devices will show the comments form inline after the browser
            addCommentForm = (
                <div className="entity-comments-form">
                    <div className="entity-comments-form-left">
                        <IconButton
                            label="Attach Files"
                            iconClassName="fa fa-paperclip"
                            onClick={this._handleFileUpload}
                            />
                    </div>
                    <div className="entity-comments-form-center">
                        <TextField ref="commInput" hintText="Add Comment" multiLine={true}/>
                    </div>
                    <div className="entity-comments-form-right">
                        <FlatButton
                            label="Send"
                            iconClassName="fa fa-paper-plane"
                            onClick={this._handleCommentSend}
                            />
                    </div>
                </div>
            );
        } else {
            // Small devices show the comments form as a floating toolbar
            // TODO: Add - <div className="entity-comments-form-left">[i]</div>
            addCommentForm = (
                <div className="entity-comments-form">
                    <div className="entity-comments-form-left">
                        <IconButton
                            label="Attach File(s)"
                            iconClassName="fa fa-paperclip"
                            onClick={this._handleFileUpload}
                        />
                    </div>
                    <div className="entity-comments-form-center">
                        <TextField ref="commInput" hintText="Add Comment" multiLine={true}/>
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

        var displayAttachedFiles = [];

        // Loop thru the attachedFiles and create the display for the file details using the File Component
        for (var idx in this.props.attachedFiles) {
            var file = this.props.attachedFiles[idx];

            displayAttachedFiles.push(<File
                key={idx}
                index={idx}
                file={file}
                onRemove={this._handleRemoveFiles}
                />);
        }

        return (
            <div>
                {toolBar}
                <div className="entity-comments">
                    <div ref="commCon"></div>
                    {addCommentForm}
                    {displayAttachedFiles}
                </div>
            </div>
        );
    },

    /**
     * Load browser inline (FRAGMENT) with objType='comment'
     *
     * @private
     */
    _loadCommentsBrowser: function () {

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var BrowserController = require("../controller/EntityBrowserController");
        var browser = new BrowserController();

        // Add filter to only show comments from the referenced object
        var filterWhere = new Where("obj_reference");
        filterWhere.equalTo(this.props.objReference);

        browser.load({
            type: controller.types.FRAGMENT,
            title: "Comments",
            objType: "comment",
            hideAppBar: true,
            filters: [filterWhere]
        }, ReactDOM.findDOMNode(this.refs.commCon));

        this.setState({commBrowser: browser});
    },

    /**
     * Handle when a user hits send on the comment form
     *
     * @param {DOMEvent} evt
     * @private
     */
    _handleCommentSend: function (evt) {
        var value = this.refs.commInput.getValue();
        if (this.props.onAddComment) {
            this.props.onAddComment(value);
        }

        // Clear the form
        this.refs.commInput.setValue("");
    },

    /**
     * Respond when the user clicks the back button
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
     * Handles the uploading of attached files
     *
     * @param evt
     * @private
     */
    _handleFileUpload: function (evt) {

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var FileUploadController = require("../controller/FileUploadController");
        var fileUpload = new FileUploadController();

        fileUpload.load({
            type: controller.types.DIALOG,
            title: "Attach File(s)",
            onFilesUploaded: function (fileId, fileName) {
                this._handleAttachFiles(fileId, fileName)
            }.bind(this),
            onRemoveFilesUploaded: function (fileId, index) {
                this._handleRemoveFiles(index);
            }.bind(this)
        });
    },

    /**
     * Handle the attachment of uploaded file
     *
     * @param {int} fileId          The id of the file uploaded
     * @param {string} fileName     The name of the file uploaded
     *
     * @private
     */
    _handleAttachFiles: function (fileId, fileName) {
        if (this.props.onAttachFiles) this.props.onAttachFiles(fileId, fileName);
    },

    /**
     * Handles the removing of uploaded file
     *
     * @param {int} index       Index of the file to be removed
     *
     * @private
     */
    _handleRemoveFiles: function (index) {
        if (this.props.onRemoveFiles) this.props.onRemoveFiles(index);
    },

    /**
     * Refresh the comments entity browser
     *
     * @public
     */
    refreshComments: function () {
        if (this.state.commBrowser) {
            this.state.commBrowser.refresh();
        }
    }
});

module.exports = EntityComments;
