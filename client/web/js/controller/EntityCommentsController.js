/**
 * @fileoverview Controller for viewing and adding comments to an entity
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var netric = require("../base");
var controller = require("./controller")
var AbstractController = require("./AbstractController");
var UiEntityComments = require("../ui/EntityComments.jsx");
var definitionLoader = require("../entity/definitionLoader");
var entityLoader = require("../entity/loader");
var entitySaver = require("../entity/saver");
var log = require("../log");

/**
 * Controller that loads an entity browser for comments and adds new comments
 */
var EntityCommentsController = function () {
}

/**
 * Extend base controller class
 */
netric.inherits(EntityCommentsController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
EntityCommentsController.prototype.rootReactNode_ = null;

/**
 * Handle to the entity definition
 *
 * @private
 * @type {netric.entity.Definition}
 */
EntityCommentsController.prototype.entityDefinition_ = null;

/**
 * The object type to use for comments
 *
 * @private
 * @const
 * @type {string}
 */
EntityCommentsController.prototype.COMMENT_OBJ_TYPE = "comment";

/**
 * Contains the attachment data
 *
 * @private
 * @type {netric.entity.Definition}
 */
EntityCommentsController.prototype.attachedFiles_ = [];

/**
 * The entity we are editing
 *
 * @private
 * @type {netric.entity.Entity}
 */
EntityCommentsController.prototype.entity_ = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
EntityCommentsController.prototype.onLoad = function (opt_callback) {

    var callbackWhenLoaded = opt_callback || null;

    // Get the entity definition then call the loaded callback (if set)
    definitionLoader.get(this.COMMENT_OBJ_TYPE, function (def) {
        if (!def) {
            throw "Could not get entity definition for " + this.COMMENT_OBJ_TYPE;
        }

        this.entityDefinition_ = def;


        if (callbackWhenLoaded) {
            // Let the application router know we're all loaded
            callbackWhenLoaded();
        }
    }.bind(this));
}

/**
 * Render this controller into the dom tree
 */
EntityCommentsController.prototype.render = function () {

    // Set outer application container
    var domCon = this.domNode_;

    // Unhide toolbars if we are in a page mode
    var hideToolbar = this.props.hideToolbar || false;
    if (this.getType() === controller.types.PAGE) {
        hideToolbar = false;
    }

    // Set data properties to forward to the view
    var data = {
        objReference: this.props.objReference || null,
        hideToolbar: hideToolbar,
        deviceSize: netric.getApplication().device.size,
        attachedFiles: this.attachedFiles_,
        onNavBtnClick: function (evt) {
            this.close();
        }.bind(this),
        onAddComment: function (comment) {
            this._handleAddComment(comment);
        }.bind(this),
        onAttachFiles: function (fileId, fileName) {
            this._handleAttachFiles(fileId, fileName);
        }.bind(this),
        onRemoveFiles: function (fileId) {
            this._handleRemoveFiles(fileId);
        }.bind(this),
    }

    // Render component
    this.rootReactNode_ = ReactDOM.render(
        React.createElement(UiEntityComments, data),
        domCon
    );
}

/**
 * Add a new comment
 *
 * @param {string} comment The comment text
 */
EntityCommentsController.prototype._handleAddComment = function (comment) {

    // Do not save an empty comment or if we have no attached file
    if (!comment && this.attachedFiles_.length == 0) {
        return;
    }

    // Create a new comment and save it
    var ent = entityLoader.factory(this.COMMENT_OBJ_TYPE);

    if (comment) {
        ent.setValue("comment", comment);
    }

    if (this.attachedFiles_.length) {
        
        // Loop thru the files
        for(var idx in this.attachedFiles_) {
            var attachedFile = this.attachedFiles_[idx];
            
            // Add the file in this comment entity
            ent.addMultiValue('attachments', attachedFile.id, attachedFile.name);
        }
    }

    // Add the user
    var userId = -3; // -3 is 'current_user' on the backend
    if (netric.getApplication().getAccount().getUser()) {
        userId = netric.getApplication().getAccount().getUser().id;
    }
    ent.setValue("owner_id", userId);

    // Add an object reference
    if (this.props.objReference) {

        // This is how we associate comments with a specific entity object
        ent.setValue("obj_reference", this.props.objReference);

        // TODO: Not sure if we should do this here or on the backend?
        //ent.addMultiValue("associations", this.props.objReference);
    }

    /*
     // Check for adding customer reference
     // Currently this is only used for cases and the reference is to customer_id field
     // We may expand in the future, but this is working well for the time being - Sky Stebnicki
     if (this.parentObject && sendToCust)
     {
     if (notify) notify += ",";
     notify += "customer:" + this.parentObject.getValue("customer_id");
     }

     if (notify)
     obj.setValue("notify", notify);

     // Attachments
     for (var i in attachments)
     obj.setMultiValue("attachments", attachments[i]);
     */

    // Save the entity
    entitySaver.save(ent, function () {
        log.info("Saved comment on", this.props.objReference);
        this.rootReactNode_.refreshComments();

        // Clear the attached files
        if (this.attachedFiles_.length) {
            this.attachedFiles_ = [];
            this.render();
        }
    }.bind(this));
}

/**
 * Adds the uploaded file in the attachedFiles_ variable
 *
 * @param {entity/fileupload/file} file     Instance of the file model
 * @private
 */
EntityCommentsController.prototype._handleAttachFiles = function (file) {
    this.attachedFiles_.push(file);

    this.render();
}

/**
 * Handles the removing of files in the attachedFiles_ variable
 *
 * @param {int} index       Index of the file to be removed
 *
 * @private
 */
EntityCommentsController.prototype._handleRemoveFiles = function (index) {
    this.attachedFiles_.splice(index, 1);
    this.render();
}

/**
 * Refresh the comments list
 */
EntityCommentsController.prototype.refresh = function () {
    this.rootReactNode_.refreshComments();
}

module.exports = EntityCommentsController;