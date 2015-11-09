/**
 * Attachments
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var FlatButton = Chamel.FlatButton;

var controller = require("../../../controller/controller");

var Attachments = React.createClass({

    render: function() {

        var xmlNode = this.props.xmlNode;
        return (
            <div>
                <FlatButton label='Attachment' onClick={this._handleAttachment} />
            </div>
        );
    },

    _handleAttachment: function() {
        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var FileUploadController = require("../../../controller/FileUploadController");
        var File = require("../../../entity/definition/File");
        var fileUpload = new FileUploadController();

        /*
         * sample code to include existing files in the file upload component
        var sampleFile = new File({
            id: 30,
            name: 'composer_phar_error.png'
        })


        fileUpload.addFile(sampleFile);
         */
        
        fileUpload.load({
            type: controller.types.DIALOG,
            title: "Attach Files"
        });
    }

});

module.exports = Attachments;
