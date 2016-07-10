/**
 * Image component
 */
'use strict';

var React = require('react');
var Controls = require("../../Controls.jsx");
var DropDownIcon = Controls.DropDownIcon;
var FileUpload = require("../../fileupload/FileUpload.jsx");
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
         * @type {entity/form/FormNode}
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

    /**
     * Set defaults
     *
     * @returns {{}}
     */
    getDefaultProps: function () {
        return {
            label: 'Image'
        }
    },

    /**
     * Return the starting state of this component
     *
     * @returns {{}}
     */
    getInitialState: function () {
        return {
            openMenu: false
        };
    },

    /**
     * Render the component
     */
    render: function () {
        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');
        var fieldValue = this.props.entity.getValue(fieldName);
        var imageSource = server.host + "/images/icons/objects/files/image_48.png";

        // Actions available for the image.
        var iconMenuItems = [
            {payload: 'upload', text: 'Upload File'},
            {payload: 'select', text: 'Select Uploaded File'}
        ];

        // If we have a field value, then lets display the image
        if (fieldValue) {
            var imageSource = server.host + "/files/images/" + this.props.entity.getValue(fieldName) + "/48";

            // If there is a value saved, then let's display the remove action
            iconMenuItems.push({payload: 'remove', text: 'Remove File'});
        }

        return (
            <div>
                <div>
                    <img
                        src={imageSource}
                        style={{width: "48px", height: "48px", cursor: "pointer"}}
                        title={"Change " + this.props.label}
                        onClick={this._handleImageUpload}
                    />
                </div>
                <DropDownIcon
                    iconClassName="fa fa-pencil-square-o"
                    menuItems={iconMenuItems}
                    onChange={this._handleSelectMenuItem}/>
            </div>
        );
    },

    /**
     * Callback used to handle commands when user selects an action from the menu
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {Object} data The object value of the menu clicked
     * @private
     */
    _handleSelectMenuItem: function (e, key, data) {
        switch (data.payload) {
            case 'upload':
                this._handleImageUploadClick();
                break;
            case 'select':
                this._handleImageSelect();
                break;
            case 'remove':
                this._handleRemoveImage();
                break;
        }
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
     * Function that will open an object browser and let the user select an uploaded image
     *
     * @private
     */
    _handleImageSelect: function () {

        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');
        var field = this.props.entity.def.getField(fieldName);

        // Make sure the field is an object, otherwise fail
        if (field.type != field.types.object && field.subtype) {
            throw "Field " + field.name + " is not an object/entity reference";
        }

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var BrowserController = require("../../../controller/EntityBrowserController");
        var browser = new BrowserController();
        browser.load({
            type: controller.types.DIALOG,
            title: "Select Uploaded Image",
            objType: field.subtype,
            onSelect: function (objType, oid, title) {
                this.props.entity.setValue(fieldName, oid, title);
            }.bind(this)
        });
    },

    /**
     * Remove the image selected by clearing the value of the entity field
     *
     * @private
     */
    _handleRemoveImage: function () {
        var elementNode = this.props.elementNode;
        var fieldName = elementNode.getAttribute('name');

        // Set the entity field value to null
        this.props.entity.setValue(fieldName, null);
    }
});

module.exports = Image;