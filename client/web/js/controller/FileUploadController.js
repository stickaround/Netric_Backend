/**
 * @fileoverview File Upload
 *
 * Manages the file uploading to the server.
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var UiFileUpload = require("../ui/fileupload/FileUpload.jsx");
var fileUploader = require("../entity/fileUploader");
var entityLoader = require("../entity/loader");
var entitySaver = require("../entity/saver");
var File = require("../entity/fileupload/File");

/**
 * Controller that loads a File Upload Component
 */
var FileUploadController = function () {
}

/**
 * Extend base controller class
 */
netric.inherits(FileUploadController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
FileUploadController.prototype._rootReactNode = null;

/**
 * The entity of the file object
 *
 * @private
 * @type {netric.entity.Entity}
 */
File.prototype._entity = null;

/**
 * The files that are already uploded
 * Should contain the collection of File instances (entity/definition/File)
 *
 * @private
 * @type {Array}
 */
FileUploadController.prototype._uploadedFiles = [];

/**
 * The name of the object type we are working with
 *
 * @public
 * @type {string}
 */
FileUploadController.prototype.objType = 'file';

/**
 * The files that are already uploded
 * Should contain the collection of File instances (entity/definition/File)
 *
 * @private
 * @type {Array}
 */
FileUploadController.prototype.fileEntity = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
FileUploadController.prototype.onLoad = function (opt_callback) {

    var callbackWhenLoaded = opt_callback || null;

    // Load the entity and get a promised entity back
    entityLoader.factory(this.objType, function (ent) {

        this._entity = ent;

        if (callbackWhenLoaded) {
            callbackWhenLoaded();
        } else {
            this.render();
        }
    }.bind(this));


}

/**
 * Render this controller into the dom tree
 */
FileUploadController.prototype.render = function () {

    // Set outer application container
    var domCon = this.domNode_;

    // Define the data
    var data = {
        title: this.props.title || "Upload Files",
        currentPath: this.props.currentPath,
        folderId: this.props.folderId,
        uploadedFiles: this._uploadedFiles,
        onUpload: function (file, index, folder) {
            this._handleUploadFile(file, index, folder)
        }.bind(this),
        onRemove: function (index) {
            this._handleRemoveFile(index)
        }.bind(this),
        getFileUrl: function (index) {
            this._getFileUrl(index)
        }.bind(this)
    }

    // Render browser component
    this._rootReactNode = ReactDOM.render(
        React.createElement(UiFileUpload, data),
        domCon
    );
}

/**
 * Handles the uploading of files.
 *
 * @param {object} file     File to be uploaded
 * @param {int} index       Index of the current file to be uploaded
 * @param {array} folder    Collection of folder data used to save the files
 *
 * @private
 */
FileUploadController.prototype._handleUploadFile = function (files, index, folder) {

    // Check if the index is existing in the files collection
    if (files[index]) {

        // Get the File Instance
        var fileIndex = this._uploadedFiles.length;
        var fileName = files[index].name;

        // Set the formData to be posted in the server
        var formData = new FormData();
        formData.append('uploadedFiles[]', files[index], fileName);

        if (folder.id) {
            formData.append('folderid', folder.id);
        }

        if (folder.path) {
            formData.append('path', escape(folder.path));
        }

        var file = new File(this._entity);
        file.setValue('name', fileName);

        // Add the file in the uploadedFiles[] array
        this._uploadedFiles[fileIndex] = file;
        this.render();

        // Re render the fileupload and display the progress of the upload
        var funcProgress = function (evt) {
            this._uploadedFiles[fileIndex].progress = evt.data;
            this.render();
        }.bind(this);

        // Re render the fileUpload with the result of the uploaded files
        var funcCompleted = function (result) {
            this._uploadedFiles[fileIndex].setValue('id', result[0].id);
            this._getFileUrl(fileIndex);
            this.render();

            // Continue to the next upload file if there's any
            this._handleUploadFile(files, index + 1, folder);
        }.bind(this);

        // Re render the fileupload and display the error
        var funcError = function (evt) {
            this._uploadedFiles[fileIndex].progress.errorText = evt.errorText;
            this.render();

            // Continue to the next upload file if there's any
            this._handleUploadFile(files, index + 1, folder);
        }.bind(this);

        // Upload the file to the server
        fileUploader.upload(formData, funcProgress, funcCompleted, funcError);
    }
}

/**
 * Handles the uploading of files.
 *
 * @param {int} index      The index of the file to be deleted
 *
 * @private
 */
FileUploadController.prototype._handleRemoveFile = function (index) {

    var funcCompleted = function (result) {
        this._uploadedFiles.splice(index, 1);
        this.render();
    }.bind(this);

    // Remove the file from the server
    entitySaver.remove(this.objType, this._uploadedFiles[index].getValue('id'), funcCompleted);
}

/**
 * Gets the url of the file from the server
 *
 * @param {int} index      The index of the file to be deleted
 *
 * @private
 */
FileUploadController.prototype._getFileUrl = function (index) {
    var funcCompleted = function (result) {
        this._uploadedFiles[index].url = result.urlDownload;
        this.render();
    }.bind(this);

    // Set the flag that we have tried loading the url for this file
    this._uploadedFiles[index].urlLoaded = true;

    // Get the file url preview
    fileUploader.view(this._uploadedFiles[index].getValue('id'), funcCompleted);
}

/**
 * Handles the adding of files in the uploadedFiles collection.
 *
 * @param {entity/fileupload/file} file     The file object to be added in the collection
 *
 * @public
 */
FileUploadController.prototype.addFile = function (file) {
    this._uploadedFiles.push(file);
}

module.exports = FileUploadController;

