/**
 * Image component
 *

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

        /**
         * Current element node level
         *
         * @type {entity/form/Node}
         */
        elementNode: React.PropTypes.object.isRequired,

        /**
         * Entity being edited
         *
         * @type {entity\Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Generic object used to pass events back up to controller
         *
         * @type {Object}
         */
        eventsObj: React.PropTypes.object,

        /**
         * Flag indicating if we are in edit mode or view mode
         *
         * @type {bool}
         */
        editMode: React.PropTypes.bool,

        /**
         * The label that will be used in image upload button
         *
         * @type {string}
         */
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
        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');
        var fieldValue = this.props.entity.getValue(fieldName);

        var iconButtonDisplay = null;
        var imageDisplay = null;

        // If we have a field value, then lets display the image
        if (fieldValue) {
            let imageSource = server.host + "/files/images/" + this.props.entity.getValue(fieldName) + "/48";
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
                        onClick={this._handleImageUploadClick}
                    />
                    <FlatButton label={this.props.label} onClick={this._handleImageUploadClick}/>
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
    _handleImageUploadClick: function () {

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
        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');

        // Set the image in the entity object
        this.props.entity.setValue(fieldName, image.id, image.name);
    },

    /**
     * Clear the value of this entity
     *
     * @private
     */
    _clearValue: function () {
        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');

        // Set the entity field value to null
        this.props.entity.setValue(fieldName, null);
    }
});

module.exports = Image;