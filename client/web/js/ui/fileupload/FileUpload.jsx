/**
 * File uploaded component.
 * Specify the folderId or currentPath to determine where to upload the files.
 * uploadedFiles[] array should have the collection of File instances (entity/definition/File)
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var Chamel = require('chamel');
var File = require('./File.jsx');
var IconButton = Chamel.IconButton;
var FlatButton = Chamel.FlatButton;
var AppBar = Chamel.AppBar;

var FileUpload = React.createClass({

    propTypes: {
        title: React.PropTypes.string,
        uploadedFiles: React.PropTypes.array,
        currentPath: React.PropTypes.string,
        folderId: React.PropTypes.number,
        onUpload: React.PropTypes.func,
        onRemove: React.PropTypes.func,
        getFileUrl: React.PropTypes.func,
        onNavBtnClick: React.PropTypes.func,
        hideToolbar: React.PropTypes.bool,
        allowMultipleSelect: React.PropTypes.bool,
        buttonLabel: React.PropTypes.string,
        iconClassName: React.PropTypes.string,
    },

    getDefaultProps: function () {
        return {
            folderId: null,
            currentPath: '%tmp%',
            title: 'Add Attachment',
            buttonLabel: 'Attach File(s)',
            allowMultipleSelect: true,
            iconClassName: 'fa fa-paperclip'
        }
    },

    componentDidMount: function () {
        if (this.props.uploadedFiles.length == 0) {
            this._handleShowUpload();
        }
    },

    render: function () {
        var displayFiles = [];

        for (var idx in this.props.uploadedFiles) {
            var file = this.props.uploadedFiles[idx];

            displayFiles.push(
                <File
                    key={idx}
                    index={idx}
                    file={file}
                    displayProgress={true}
                    onRemove={this.props.onRemove}
                />
            );
        }

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

        var multiple = null;
        if(this.props.allowMultipleSelect) {
            multiple = "multiple";
        }

        return (
            <div>
                {toolBar}
                <IconButton
                    label={this.props.buttonLabel}
                    iconClassName={this.props.iconClassName}
                    onClick={this._handleShowUpload}
                />
                <FlatButton label={this.props.buttonLabel} onClick={this._handleShowUpload}/>
                <input
                    type='file'
                    ref='inputFile'
                    onChange={this._handleFileUpload}
                    multiple={multiple}
                    style={{display: 'none'}}/>
                {displayFiles}
            </div>
        );
    },

    /**
     * Handles the showing the dialog to browse files
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @private
     */
    _handleShowUpload: function (e) {
        ReactDOM.findDOMNode(this.refs.inputFile).click();
    },

    /**
     * Handles the uploading of selected files
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @private
     */
    _handleFileUpload: function (e) {
        if (this.props.onUpload) {
            var folder = {
                id: this.props.folderId,
                path: this.props.currentPath
            };

            this.props.onUpload(e.target.files, 0, folder);
        }

        e.preventDefault()
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
    }

});

module.exports = FileUpload;
