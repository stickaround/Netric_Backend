/**
 * @fileoverview Controller for viewing activities of an entity
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var netric = require("../base");
var controller = require("./controller")
var AbstractController = require("./AbstractController");
var UiEntityActivity = require("../ui/EntityActivity.jsx");
var definitionLoader = require("../entity/definitionLoader");
var entityLoader = require("../entity/loader");


/**
 * Controller that loads an entity browser for entity activities
 */
var EntityActivityController = function () {
}

/**
 * Extend base controller class
 */
netric.inherits(EntityActivityController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
EntityActivityController.prototype.rootReactNode_ = null;

/**
 * Handle to the entity definition
 *
 * @private
 * @type {netric.entity.Definition}
 */
EntityActivityController.prototype.entityDefinition_ = null;

/**
 * The object type to use for activity
 *
 * @private
 * @const
 * @type {string}
 */
EntityActivityController.prototype.COMMENT_OBJ_TYPE = "activity";

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
EntityActivityController.prototype.onLoad = function (opt_callback) {

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
EntityActivityController.prototype.render = function () {

    // Set outer application container
    var domCon = this.domNode_;

    // Set data properties to forward to the view
    var data = {
        objReference: this.props.objReference || null
    }

    // Render component
    this.rootReactNode_ = ReactDOM.render(
        React.createElement(UiEntityActivity, data),
        domCon
    );

}

module.exports = EntityActivityController;