/**
 * Attachments
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var FlatButton = Chamel.FlatButton;
var IconButton = Chamel.IconButton;
var controller = require("../../../controller/controller");
var File = require("../../../ui/fileupload/File.jsx");
var CustomEventTrigger = require("../../mixins/CustomEventTrigger.jsx");

var Attachments = React.createClass({

    mixins: [CustomEventTrigger],

    /**
     * Expected props
     */
    propTypes: {
        entity: React.PropTypes.object
    },

    getInitialState: function () {

        // Return the initial state
        return {
            attachedFiles: this.props.entity.getAttachments()
        };
    },

    render: function () {
        var xmlNode = this.props.xmlNode;
        var displayFiles = [];

        // Loop thru the attachedFiles and create the display for the file details using the File Component
        for (var idx in this.state.attachedFiles) {
            var file = this.state.attachedFiles[idx];

            displayFiles.push(<File
                key={idx}
                index={idx}
                file={file}
                onRemove={this._handleRemoveFiles}
                />);
        }

        return (
            <div className='entity-form-attachments'>
                <IconButton
                    label="Attach File(s)"
                    iconClassName="fa fa-paperclip"
                    onClick={this._handleAttachment}
                    />
                <FlatButton label='Attach File(s)' onClick={this._handleAttachment}/>
                {displayFiles}
            </div>
        );
    },

    /**
     * Handles the file attachment display
     *
     * @private
     */
    _handleAttachment: function () {

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var FileUploadController = require("../../../controller/FileUploadController");
        var fileUpload = new FileUploadController();

        fileUpload.load({
            type: controller.types.DIALOG,
            title: "Add Attachment",
            onFilesUploaded: function (fileId, fileName) {
                this._handleFilesUploaded(fileId, fileName);
            }.bind(this),
            onRemoveFilesUploaded: function (fileId) {
                this._handleRemoveFilesUploaded(fileId);
            }.bind(this),
            onEntitySave: function () {
                this._sendEntitySaveEvent();
            }.bind(this)
        });
    },

    /**
     * Saves the fileId and fileName of the uploaded file to the entity field 'attachments'
     *
     * @param {entity/fileupload/file} file     Instance of the file model
     *
     * @private
     */
    _handleFilesUploaded: function (file) {

        // Add the file in the entity object
        this.props.entity.addMultiValue('attachments', file.id, file.name);

        // Push the added file in the state to update the attached files display
        var attachedFiles = this.state.attachedFiles;
        attachedFiles.push(file);

        // Update the state and this will re-render the attachments
        this.setState({attachedFiles: attachedFiles});
    },

    /**
     * Removes the file uploaded in the entity object
     *
     * @param {int} fileId          The id of the file uploaded
     *
     * @private
     */
    _handleRemoveFilesUploaded: function (fileId) {

        // Loop thru the attachedFiles state variable and look for the removed fileId
        var attachedFiles = this.state.attachedFiles;
        for (var idx in attachedFiles) {
            if (fileId == attachedFiles[idx].id) {

                // Remove the file from the entity object using the index of the file
                this._handleRemoveFiles(idx);
                break;
            }
        }
    },

    /**
     * Handles the removing of file in the entity object.
     *
     * @param {int} index      The index of the file to be deleted
     *
     * @private
     */
    _handleRemoveFiles: function (index) {

        var attachedFiles = this.state.attachedFiles;

        // Remove the file from the entity object
        this.props.entity.remMultiValue('attachments', attachedFiles[index].id);

        // Remove the file from the attachedFiles state variable and update the state for re-rendering
        attachedFiles.splice(index, 1);
        this.setState({attachedFiles: attachedFiles});

        // Trigger the save entity event when file is removed
        this._sendEntitySaveEvent();
    },

    /**
     * Trigger a custom event to save the entity's attached files
     *
     * @private
     */
    _sendEntitySaveEvent: function () {
        if (this.props.entity.id > 0) {
            this.triggerCustomEvent("entitysave");
        }
    }
});

module.exports = Attachments;
