/**
 * @fileoverview Entity viewer/editor
 */
'use strict';

var React = require('react');
var netric = require("../base");
var controller = require("./controller")
var AbstractController = require("./AbstractController");
var UiEntity = require("../ui/Entity.jsx");
var definitionLoader = require("../entity/definitionLoader");
var entityLoader = require("../entity/loader");

/**
 * Controller that loads an entity browser
 */
var EntityController = function() {
}

/**
 * Extend base controller class
 */
netric.inherits(EntityController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
EntityController.prototype.rootReactNode_ = null;

/**
 * Handle to the entity definition
 *
 * @private
 * @type {netric.entity.Definition}
 */
EntityController.prototype.entityDefinition_ = null;

/**
 * The entity we are editing
 *
 * @private
 * @type {netric.entity.Entity}
 */
EntityController.prototype.entity_ = null;

/**
 * Object used for handling custom events through the entity form
 *
 * @private
 * @type {Object}
 */
EntityController.prototype.eventsObj_ = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
EntityController.prototype.onLoad = function(opt_callback) {

    var callbackWhenLoaded = opt_callback || null;

    if (!this.props.objType) {
        throw "objType is a required property to load an entity";
    }

    // Create object to subscribe to events in the UI form
    this.eventsObj_ = {};

    // Add route to load entities
    this.addSubRoute(":objType/:oid",
        EntityController, {
            type: controller.types.PAGE
        }
    );

    // Capture an entity click and handle either loading a dialog or routing it
    alib.events.listen(this.eventsObj_, "entityclick", function(evt) {

        if (this.getRoutePath()) {
            netric.location.go(this.getRoutePath() + "/" + evt.data.objType + "/" + evt.data.id);
        } else {
            // TODO: load a dialog
        }
        
    }.bind(this));

    // Get the entity definition then call the loaded callback (if set)
    definitionLoader.get(this.props.objType, function(def){

        if (!def) {
            throw "Could not get entity definition for " + this.props.objType + " which is required";
        }

        this.entityDefinition_ = def;

        // Now load the entity if set
        if (this.props.eid) {

            entityLoader.get(this.props.objType, this.props.eid, function(ent) {

                // Set local entity
                this.entity_ = ent;

                // Set listener to call this.render when properties change
                alib.events.listen(this.entity_, "change", function(evt){
                    // Re-render
                    this.render();
                }.bind(this));

                if (callbackWhenLoaded) {
                    // Let the application router know we're all loaded
                    callbackWhenLoaded();
                }

            }.bind(this));

        } else if (callbackWhenLoaded) {
            // Let the application router know we're all loaded
            callbackWhenLoaded();
        }
    }.bind(this));
}

/**
 * Render this controller into the dom tree
 */
EntityController.prototype.render = function() {

    // Set outer application container
    var domCon = this.domNode_;

    // Set data properties to forward to the view
    var data = {
        objType: this.props.objType,
        oid: this.props.oid,
        eventsObj: this.eventsObj_,
        entity: this.entity_,
        onNavBtnClick: function(evt) {
            this.close();
        }.bind(this)
    }

    // Load up the correct UIXML form based on the device size
    switch(netric.getApplication().device.size) {
        case netric.Device.sizes.small:
            data.form = this.entityDefinition_.forms.small;
            break;
        case netric.Device.sizes.medium:
            data.form = this.entityDefinition_.forms.medium;
            break;
        case netric.Device.sizes.large:
            data.form = this.entityDefinition_.forms.large;
            break;
    }

    // Render component
    this.rootReactNode_ = React.render(
        React.createElement(UiEntity, data),
        domCon
    );

}

/**
 * Render this controller into the dom tree
 */
EntityController.prototype.close = function() {

    if (this.getType() == controller.types.DIALOG) {
        this.unload();
    } else if (this.getParentController()) {
        var path = this.getParentController().getRoutePath();
        netric.location.go(path);
    } else {
        window.close();
    }
    
}

module.exports = EntityController;