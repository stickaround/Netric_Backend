/**
 * @fileoverview Controller for viewing and adding comments to an entity
 */
'use strict';

var React = require('react');
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
var EntityCommentsController = function() {}

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
EntityCommentsController.prototype.onLoad = function(opt_callback) {

    var callbackWhenLoaded = opt_callback || null;

    // Get the entity definition then call the loaded callback (if set)
    definitionLoader.get(this.COMMENT_OBJ_TYPE, function(def){
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
EntityCommentsController.prototype.render = function() {

    // Set outer application container
    var domCon = this.domNode_;

    // Set data properties to forward to the view
    var data = {
        onNavBtnClick: function(evt) {
            this.close();
        }.bind(this),
        onAddComment: function(comment) {
            this._handleAddComment(comment);
        }.bind(this),
        deviceSize: netric.getApplication().device.size,
    }

    // Render component
    this.rootReactNode_ = React.render(
        React.createElement(UiEntityComments, data),
        domCon
    );

}

/**
 * Render this controller into the dom tree
 */
EntityCommentsController.prototype.close = function() {

    if (this.getType() == controller.types.DIALOG) {
        this.unload();
    } else if (this.getParentController()) {
        var path = this.getParentController().getRoutePath();
        netric.location.go(path);
    } else {
        window.close();
    }

}

/**
 * Save an entity
 */
EntityCommentsController.prototype.saveEntity = function() {

    // Save the entity
    entitySaver.save(this.entity_, function() {
        log.info("Entity saved");
    });

}

/**
 * Undo changes to an entity
 */
EntityCommentsController.prototype.revertChanges = function() {

    // TODO: save the entity
    log.info("Undo changes");

    if (!this.entity_.id)
        this.close();
}

/**
 * Add a new comment
 *
 * @param {string} comment The comment text
 */
EntityCommentsController.prototype._handleAddComment = function(comment) {

    // Create a new comment and save it
    var ent = entityLoader.factory(this.COMMENT_OBJ_TYPE);

    log.info("Add comment", comment);
}

module.exports = EntityCommentsController;