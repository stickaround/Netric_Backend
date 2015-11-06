/**
 * @fileOverview File Uploader.
 * Uploads the files to the server
 *
 * @author:  Marl Tumulak, marl.tumulak@aereus.com;
 *           Copyright (c) 2015 Aereus Corporation. All rights reserved.
 */
'use strict';

var BackendRequest = require("../BackendRequest");
var nertic = require("../base");

var FileUploader = {
    /**
     * Uploads multiple files
     *
     * @param {FormData} data   The form data to be saved. This will include the files to be uploaded
     * @param {function} opt_finishedCallback Optional callback to call when saved
     * @public
     */
    upload: function(data, opt_finishedCallback) {

        if (!data) {
            throw "File data should be defined";
        }

        // If we are connected
        if (netric.server.online) {
            var request = new BackendRequest();
            // Save the data remotely
            request.setDataIsForm(true);
            request.send("controller/AntFs/upload", "POST", function(resp) {
            //request.send("svr/antfs/upload", function(resp) {

                // First check to see if there was an error
                if (resp.error) {
                    throw "Error uploading files: " + resp.error;
                }

                console.log(resp);

                // Invoke callback if set
                if (opt_finishedCallback) {
                    opt_finishedCallback();
                }

            }, 'POST', data);

        } else {
            // TODO: Save the data locally into an "outbox"
            // to be saved on the next connection
        }
    }
}

module.exports = FileUploader;