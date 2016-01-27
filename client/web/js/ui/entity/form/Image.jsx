/**
 * Image component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require("chamel");
var FlatButton = Chamel.FlatButton;
var IconButton = Chamel.IconButton;
var FileUpload = require("../../../ui/fileupload/FileUpload.jsx");
var controller = require("../../../controller/controller");
var server = require('../../../server');

/**
 * Image Element
 */
var Image = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool,
        label: React.PropTypes.string,
    },

    getDefaultProps: function () {
        return {
            label: 'Image'
        }
    },

    /**
     * Render the component
     */
    render: function () {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        var fieldValue = this.props.entity.getValue(fieldName);

        var iconButtonDisplay = null;
        var imageDisplay = null;

        // If we have a field value, then lets display the image
        if (fieldValue) {
            let imageSource = server.host + "/antfs/images/" + this.props.entity.getValue(fieldName) + "/48";
            imageDisplay = (
                <img
                    src={imageSource}
                    style={{width: "48px", height: "48px", cursor: "pointer"}}
                    title={"Change " + this.props.label}
                    onClick={this._handleImageUpload}
                />);
            iconButtonDisplay = (
                <IconButton
                    onClick={this._clearValue}
                    tooltip={"Clear " + this.props.label}
                    className="cfi cfi-close"
                />
            );
        } else {
            iconButtonDisplay = (
                <div>
                    <IconButton
                        label={this.props.label}
                        iconClassName='fa fa-picture-o'
                        onClick={this._handleImageUpload}
                    />
                    <FlatButton label={this.props.label} onClick={this._handleImageUpload}/>
                </div>
            );
        }

        return (
            <div>
                {imageDisplay}
                {iconButtonDisplay}
            </div>
        );
    },

    /**
     * Handles the image upload
     *
     * @private
     */
    _handleImageUpload: function () {

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var FileUploadController = require("../../../controller/FileUploadController");
        var fileUpload = new FileUploadController();

        fileUpload.load({
            type: controller.types.DIALOG,
            title: this.props.label + " Upload",
            buttonLabel: "Upload",
            multipleSelect: false,
            iconClassName: 'fa fa-picture-o',
            onFilesUploaded: function (image) {
                this._handleImageUploaded(image);
            }.bind(this)
        });
    },

    /**
     * Saves the imageId and imageName of the uploaded file to the entity field
     *
     * @param {entity/fileupload/file} image     Instance of the file model
     *
     * @private
     */
    _handleImageUploaded: function (image) {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        // Set the image in the entity object
        this.props.entity.setValue(fieldName, image.id, image.name);
    },

    /**
     * Clear the value of this entity
     *
     * @private
     */
    _clearValue: function () {
        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        // Set the entity field value to null
        this.props.entity.setValue(fieldName, null);
    }
});

module.exports = Image;