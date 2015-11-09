/**
 * Displays the info and actions for the uploaded files
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var IconButton = Chamel.IconButton;
var FlatButton = Chamel.FlatButton;
var LinearProgress = Chamel.LinearProgress;

var File = React.createClass({

    propTypes: {
        index: React.PropTypes.string.isRequired,
        file: React.PropTypes.object.isRequired,
        onRemove: React.PropTypes.func
    },

    componentDidMount: function() {
    },

    render: function() {
        var status = null;
        var displayProgress = null;
        var percentComplete = null;
        var fileView = null;
        var progress = this.props.file.progress;
        var statusClass = 'file-upload-status';

        if(progress.errorText) {
            status = progress.errorText;
            statusClass = 'file-upload-status-error';

        }else if(progress.percentComplete == 100) {
            status = 'Completed - ';

            // Let the user know that the file link is still loading.
            if(!this.props.file.url) {
                fileView = 'Loading the file link...';
            }

        }else if(progress.percentComplete > 0 && progress.percentComplete < 100) {
            status = 'Uploading - ' + progress.percentComplete + '%';

            displayProgress = <LinearProgress mode="determinate" min={0} max={progress.total} value={progress.loaded} />;

        }else if(this.props.file.id == null) {
            status = 'Queue';
            displayProgress = <LinearProgress mode="indeterminate" />;
        }

        // If file preview url is available then lets display it.
        if(this.props.file.url) {
            fileView =  <a href={this.props.file.url} target='_blank'>View File</a>;
        }

        return (
            <div className='file-upload file-upload-container'>
                <div>
                    <div className='file-upload-name'>{this.props.file.name}</div>
                    <div className='file-upload-remove'>
                        <IconButton
                            onClick={this._handleRemoveFile}
                            className="fa fa-times" />
                    </div>
                    <div className='clearFix clear'></div>
                </div>
                <div className={statusClass}>{status} {fileView}</div>
                {displayProgress}
            </div>
        );
    },

    /**
     * Handles the removing of file
     *
     * @private
     */
    _handleRemoveFile: function() {
        if(this.props.onRemove) this.props.onRemove(this.props.index);
    }
});

module.exports = File;
