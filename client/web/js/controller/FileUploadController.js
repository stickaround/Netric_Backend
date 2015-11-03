/**
 * @fileoverview File Upload
 */
'use strict';

var React = require('react');
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var UiFileUpload = require("../ui/fileupload/FileUpload.jsx");
var fileUploader = require("../entity/fileUploader");

/**
 * Controller that loads an Advanced Search
 */
var FileUploadController = function() {}

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
FileUploadController.prototype.rootReactNode_ = null;

/**
 * Authentication string used to send files in stateless mode
 *
 * @private
 * @type {String}
 */
FileUploadController.prototype.authString = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
FileUploadController.prototype.onLoad = function(opt_callback) {

    var callbackWhenLoaded = opt_callback || null;

    if (callbackWhenLoaded) {
        callbackWhenLoaded();
    } else {
        this.render();
    }
}

/**
 * Render this controller into the dom tree
 */
FileUploadController.prototype.render = function() {

    // Set outer application container
    var domCon = this.domNode_;
    var entityFields = new Array();

    // Define the data
    var data = {
        title: this.props.title || "File Upload",
        onUpload: this._handleUploadButton
    }

    // Render browser component
    this.rootReactNode_ = React.render(
        React.createElement(UiFileUpload, data),
        domCon
    );
}

FileUploadController.prototype._handleUploadButton = function(e, data) {

    var data = {
        folderid: data.folderid,
        path: data.path,
        files: []
    };

    fileUploader.upload(data);
}

module.exports = FileUploadController;

