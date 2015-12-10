/**
 * @fileoverview Entity viewer/editor
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var netric = require("../base");
var controller = require("./controller")
var AbstractController = require("./AbstractController");
var UiEntity = require("../ui/Entity.jsx");
var definitionLoader = require("../entity/definitionLoader");
var entityLoader = require("../entity/loader");
var entitySaver = require("../entity/saver");
var actionsLoader = require("../entity/actionsLoader");
var log = require("../log");

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
 * Entity actions object
 *
 * @private
 * @type {netric.entity.actions.*}
 */
EntityController.prototype.actions_ = null;



/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
EntityController.prototype.onLoad = function(opt_callback) {

    var callbackWhenLoaded = opt_callback || null;

    this.actions_ = actionsLoader.get(this.props.objType);

    if (!this.props.objType) {
        throw "objType is a required property to load an entity";
    }

    // Create object to subscribe to events in the UI form
    this.eventsObj_ = {};

    // Add route to load entities
    this.addSubRoute(":objType/:eid",
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

    // Capture a save entity and handle the saving of the entity
    alib.events.listen(this.eventsObj_, "entitysave", function(evt) {
        this.saveEntity();
    }.bind(this));

    // Get the entity definition then call the loaded callback (if set)
    definitionLoader.get(this.props.objType, function(def){

        if (!def) {
            throw "Could not get entity definition for " + this.props.objType + " which is required";
        }

        this.entityDefinition_ = def;

        // Now load the entity if set
        if (this.props.eid) {

            // Load the entity and get a promised entity back
            this.entity_ = entityLoader.get(this.props.objType, this.props.eid, function(ent) {
                /*
                 * Set listener to call this.render when properties change.
                 * This is save because the load has already set all properties so
                 * it should only call render if a property changes post-load
                 */
                alib.events.listen(ent, "change", function(evt){
                    // Re-render
                    this.render();
                }.bind(this));

            }.bind(this));

            // Listen for initial load to re-render this entity
            alib.events.listen(this.entity_, "load", function(evt){
                // Re-render
                this.render();
            }.bind(this));

        } else {

            // Setup an empty entity
            this.entity_ = entityLoader.factory(this.props.objType);

            // Check if we have default data for the new entity
            if (this.props.entityData) {
                this._initEntityData(this.props.entityData);
            }

            // Set listener to call this.render when properties change
            alib.events.listen(this.entity_, "change", function(evt){
                // Re-render
                this.render();
            }.bind(this));
        }

        if (callbackWhenLoaded) {
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
        actionHandler: this.actions_,
        eventsObj: this.eventsObj_,
        entity: this.entity_,
        onNavBtnClick: function(evt) {
            this.close();
        }.bind(this),
        onSaveClick: function(evt) {
            this.saveEntity();
        }.bind(this),
        onCancelChanges: function(evt) {
            this.revertChanges();
        }.bind(this),
        onPerformAction: function(actionName) {
            this._performAction(actionName);
        }.bind(this)
    }

    // Load up the correct UIXML form based on the device size
    switch(netric.getApplication().device.size) {
        case netric.Device.sizes.small:
            data.form = this.entityDefinition_.forms.small;
            break;
        case netric.Device.sizes.medium:
            if (this.entityDefinition_.forms.medium) {
                data.form = this.entityDefinition_.forms.medium;
            } else {
                data.form = this.entityDefinition_.forms.large;
            }

            break;
        case netric.Device.sizes.large:
            data.form = this.entityDefinition_.forms.large;
            break;
        case netric.Device.sizes.xlarge:
            data.form = this.entityDefinition_.forms.xlarge;
            break;
        default:
            throw "Device size " + netric.getApplication().device.size + " not supported";
    }

    // Render component
    this.rootReactNode_ = ReactDOM.render(
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

/**
 * Save an entity
 */
EntityController.prototype.saveEntity = function() {

    // Save the entity
    entitySaver.save(this.entity_, function() {
        log.info("Entity saved");
    });

    if (this.props.onSave) {
        this.props.onSave(this.entity_);
    }
    
}

/**
 * Undo changes to an entity
 */
EntityController.prototype.revertChanges = function() {

    // TODO: save the entity
    //console.log("Undo changes");
    log.info("Undo changes");

    if (!this.entity_.id)
        this.close();
}

/**
 * Set the value of an object field
 *
 * @param {string} fname The name of the field
 */
EntityController.prototype.setObjectField = function(fname) {

    /*
     * We require it here to avoid a circular dependency where the
     * controller requires the view and the view requires the controller
     */
    var BrowserController = require("./EntityBrowserController");
    var browser = new BrowserController();
    browser.load({
        type: controller.types.DIALOG,
        title: "Select",
        objType: "note", // This is set statically for now
        onSelect: function(objType, oid, title) {
            this.entity_.setValue(fname, oid, title);
        }.bind(this)
    });
}

/**
 * Set entity values from a data array
 *
 * This is usually used when values are forwarded to the controller
 * from the calling function for setting default values.
 *
 * @param {Object} data
 * @private
 */
EntityController.prototype._initEntityData = function(data) {

    for (var prop in data) {
        var val = data[prop];
        if (val instanceof Array) {
            // TODO: Not yet implemented
            //this.entity_.addMultiValue(prop, val);
        } else if (val instanceof Object) {
            this.entity_.setValue(prop, val.key, val.value);
        } else {

            this.entity_.setValue(prop, val);
        }
    }
};

/**
 * Perform an action on this entity
 *
 * @private
 * @param {string} actionName
 */
EntityController.prototype._performAction = function(actionName) {

    var selected = [this.entity_.id];
    var objType = this.entity_.def.objType;

    var workingText = this.actions_.performAction(actionName, objType, selected, function(error, message) {

        if (error) {
            log.error(message);
        }

        // TODO: clear workingText notification

    }.bind(this));

    // TODO: display working notification(workingText) only if the function has not already finished
}


module.exports = EntityController;