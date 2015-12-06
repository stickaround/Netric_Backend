/**
 * Displays the info and actions for the uploaded files
 * Pass the file entity (entity/fileupload/file) in the file props to display the file details
 * File Upload Progressbar will be displayed if file.progress is specified
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var IconButton = Chamel.IconButton;
var FlatButton = Chamel.FlatButton;
var LinearProgress = Chamel.LinearProgress;
var server = require('../../server');

var File = React.createClass({

    propTypes: {
        index: React.PropTypes.string.isRequired,

        /**
         * Object representing a file entity (but not an entity)
         *
         * @var {entity/fileupload/File}
         */
        file: React.PropTypes.object.isRequired,

        /**
         * If true the show progress bar when uploading a file
         */
        displayProgress: React.PropTypes.bool,

        /**
         * Callback to call when the user removes a file
         */
        onRemove: React.PropTypes.func
    },

    getDefaultProps: function () {
        return {
            displayProgress: false
        }
    },

    render: function () {
        var statusClass = 'file-upload-status';
        var status = null;
        var displayRemoveBtn = null;
        var displayProgress = null;
        var percentComplete = null;
        var fileView = null;
        var file = this.props.file;

        // Check if we have progress event
        if (this.props.displayProgress) {
            var progress = this.props.file.progress;

            if (progress.errorText) {
                status = progress.errorText;
                statusClass = 'file-upload-status-error';
            } else if (progress.progressCompleted == 100) {
                status = 'Completed - ';
            } else if (progress.progressCompleted > 0 && progress.progressCompleted < 100) {
                status = 'Uploading - ' + progress.progressCompleted + '%';
                displayProgress = (
                    <LinearProgress
                        mode="determinate"
                        min={0}
                        max={progress.total}
                        value={progress.uploaded}
                    />
                );
            } else if (!this.props.file.id) {
                status = 'Uploading';
                displayProgress = <LinearProgress mode="indeterminate"/>;
            }
        }

        // If file preview url is available then lets display it.
        if (this.props.file.getFileUrl()) {
            fileView = <a href={this.props.file.getFileUrl()} target='_blank'>View File</a>;
        }

        // Check if we have a remove function set in the props
        if (this.props.onRemove) {
            displayRemoveBtn = (<IconButton
                onClick={this._handleRemoveFile}
                className="fa fa-times"/>);
        }

        // Set the thumb
        var thumb = null;
        if (file.isImage() && file.id) {
            let imageSource = server.host + "/antfs/images/" + file.id;
            thumb = (<img src={imageSource} />);
        } else {
            let fileTypeClassName = file.getIconClassName();
            thumb = (<i className={fileTypeClassName} />);
        }

        return (
            <div className='file-upload file-upload-container'>
                <div className='file-upload-thumb'>
                    {thumb}
                </div>
                <div className='file-upload-details'>
                    <div className='file-upload-name'>{this.props.file.name}</div>
                    {displayProgress}
                    <div className={statusClass}>{status} {fileView}</div>
                </div>
                <div className='file-upload-remove'>
                    {displayRemoveBtn}
                </div>
            </div>
        );
    },

    /**
     * Handles the removing of file
     *
     * @private
     */
    _handleRemoveFile: function () {
        if (this.props.onRemove) this.props.onRemove(this.props.index);
    }
});

module.exports = File;
