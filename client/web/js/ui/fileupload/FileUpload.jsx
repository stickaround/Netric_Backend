/**
 * File uploaded component.
 * Specify the folderId or currentPath to determine where to upload the files.
 * uploadedFiles[] array should have the collection of File instances (entity/definition/File)
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var Chamel = require('chamel');
var IconButton = Chamel.IconButton;
var FlatButton = Chamel.FlatButton;
var File = require('./File.jsx');

var FileUpload = React.createClass({

    propTypes: {
        title: React.PropTypes.string,
        uploadedFiles: React.PropTypes.array,
        currentPath: React.PropTypes.string,
        folderId: React.PropTypes.number,
        onUpload: React.PropTypes.func,
        onRemove: React.PropTypes.func,
        getFileUrl: React.PropTypes.func
    },

    getDefaultProps: function() {
        return {
            folderId: null,
            currentPath: '%tmp%',
            title: 'Upload Files',
        }
    },

    componentDidMount: function() {
        if(this.props.uploadedFiles.length == 0) {
            this._handleShowUpload();
        }
    },

    render: function() {
        var displayFiles = [];

        for(var idx in this.props.uploadedFiles) {
            var file = this.props.uploadedFiles[idx];

            // If file is already existing in the server and url is not then lets try to get it from the server
            if(!file.url && !file.urlLoaded && file.id) {
                if(this.props.getFileUrl) this.props.getFileUrl(idx);
            }

            displayFiles.push(<File
                key={idx}
                index={idx}
                file={file}
                onRemove={this.props.onRemove}
                />);
        }

        return (
            <div>
                <FlatButton label={this.props.title} onClick={this._handleShowUpload} />
                <input
                    type='file'
                    ref='inputFile'
                    onChange={this._handleFileUpload}
                    multiple
                    style={{display: 'none'}} />
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
    _handleShowUpload: function(e) {
        ReactDOM.findDOMNode(this.refs.inputFile).click();
    },

    /**
     * Handles the uploading of selected files
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @private
     */
    _handleFileUpload: function(e) {
        if(this.props.onUpload) {
            var folder = {
                id: this.props.folderId,
                path: this.props.currentPath
            };

            this.props.onUpload(e.target.files, 0, folder);
        }

        e.preventDefault()
    }

});

module.exports = FileUpload;
