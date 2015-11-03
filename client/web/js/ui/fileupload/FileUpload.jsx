/**
 * Attachments
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var IconButton = Chamel.IconButton;
var FlatButton = Chamel.FlatButton;

var FileUpload = React.createClass({

    propTypes: {
        currentPath: React.PropTypes.string,
        folderId: React.PropTypes.number,
        fileId: React.PropTypes.number,
        onUpload: React.PropTypes.func
    },

    getDefaultProps: function() {
        return {
            folderId: null,
            currentPath: '%tmp%'
        }
    },

    getInitialState: function() {
        // Return the initial state
        return {
        };
    },

    componentDidMount: function() {
        this.refs.inputFile.getDOMNode().click();
    },

    render: function() {
        return (
            <div>
                <form encType='multipart/form-data'>
                    <input type='file' ref='inputFile' onChange={this._handleFileUpload} multiple />
                </form>
            </div>
        );
    },

    /**
     * Handles the uploading of selected files
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @private
     */
    _handleFileUpload: function(e) {
        if(this.props.onUpload) {
            var data = {
                folderid: this.props.folderId,
                path: this.props.currentPath
            };

            this.props.onUpload(e, data);
        }

        e.preventDefault()
    }

});

module.exports = FileUpload;
