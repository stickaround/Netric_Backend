/**
 * @fileoverview Advanced Search
 */
'use strict';

var React = require('react');
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var UiAdvanceSearch = require("../ui/AdvanceSearch.jsx");
var EntityCollection = require("../entity/Collection");
var definitionLoader = require("../entity/definitionLoader");

/**
 * Controller that loads an Advanced Search
 */
var AdvanceSearchController = function() {}

/**
 * Extend base controller class
 */
netric.inherits(AdvanceSearchController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
AdvanceSearchController.prototype.rootReactNode_ = null;

/**
 * A collection of entities
 *
 * @public
 * @type {netric.entity.Collection}
 */
AdvanceSearchController.prototype.collection = null;

/**
 * Object used for handling custom events through the advance search
 *
 * @private
 * @type {Object}
 */
AdvanceSearchController.prototype.eventsObj_ = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
AdvanceSearchController.prototype.onLoad = function(opt_callback) {
    
    var callbackWhenLoaded = opt_callback || null;
    
    // Create object to subscribe to events in the UI form
    this.eventsObj_ = {};

    // Capture if in advance search the condition field type is an object
    alib.events.listen(this.eventsObj_, "set_object_field", function(evt) {
        this.setObjectField(evt.data.fieldName);
    }.bind(this));
    
    
    if(callbackWhenLoaded) {
        callbackWhenLoaded();
    }
}

/**
 * Render this controller into the dom tree
 */
AdvanceSearchController.prototype.render = function() {
	// Set outer application container
	var domCon = this.domNode_;
	var entities = new Array();
	var entityFields = new Array();
	
	// If collection is already loaded, then we just need to get the entities and the fields
	if(this.collection != null) {
	    entities = this.collection.getEntities();
	    entityFields = this.collection.getEntityFields();
	}
	
    // Define the data
	var data = {
	        eventsObj: this.eventsObj_,
	        title: this.props.title || "Advanced Search",
	        entities: entities,
	        entityFields: entityFields,
	        objType: this.props.objType,
	        deviceSize: netric.getApplication().device.size,
	        layout: (netric.getApplication().device.size === netric.Device.sizes.small)
	        ? "compact" : "table",
	}
	
	// Render browser component
    this.rootReactNode_ = React.render(
            React.createElement(UiAdvanceSearch, data),
            domCon
    );
	
	// Check if collection is null or is already loaded from parent call
	if(this.collection == null) {
	    // Load the entity list
	    this.collection = new EntityCollection(this.props.objType);
	    
	    alib.events.listen(this.collection, "loading", function() {
	        this.onCollectionLoading();
	    }.bind(this));

	    alib.events.listen(this.collection, "loaded", function() {
	        this.onCollectionLoaded();
	    }.bind(this));
	    
	    // Load the colleciton
	    this.collection.load();
	}
}

/**
 * The collection is attempting to get results from the backend
 * 
 */
AdvanceSearchController.prototype.onCollectionLoading = function() {
    this.rootReactNode_.setProps({collectionLoading: true});
}

/**
 * The collection has finished requesting results from the backend
 * 
 */
AdvanceSearchController.prototype.onCollectionLoaded = function() {
    var entities = this.collection.getEntities();
    this.rootReactNode_.setProps({collectionLoading: false, entities: entities});
}

/**
 * Set the value of an object field
 *
 * @param {string} fname The name of the field
 */
AdvanceSearchController.prototype.setObjectField = function(fname) {

    /*
     * We require it here to avoid a circular dependency where the
     * controller requires the view and the view requires the controller
     */
    var BrowserController = require("./EntityBrowserController");
    var browser = new BrowserController();
    browser.load({
        type: controller.types.DIALOG,
        title: "Select",
        objType: fname, // This is set statically for now
    });
}


module.exports = AdvanceSearchController;

