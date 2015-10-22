/**
 * @fileoverview Advanced Search
 */
'use strict';

var React = require('react');
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var UiAdvancedSearch = require("../ui/AdvancedSearch.jsx");
var entityLoader = require("../entity/loader");

/**
 * Controller that loads an Advanced Search
 */
var AdvancedSearchController = function() {}

/**
 * Extend base controller class
 */
netric.inherits(AdvancedSearchController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
AdvancedSearchController.prototype.rootReactNode_ = null;

/**
 * The entity that will be used to get object field data
 *
 * @private
 * @type {netric.entity.Entity}
 */
AdvancedSearchController.prototype.entity_ = null;

/**
 * Object used for handling custom events through the advance search
 *
 * @public
 * @type {Object}
 */
AdvancedSearchController.prototype.eventsObj = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
AdvancedSearchController.prototype.onLoad = function(opt_callback) {
    
    var callbackWhenLoaded = opt_callback || null;
    
    // Create object to subscribe to events in the UI form if its not yet created
    if(this.eventsObj == null) {
        this.eventsObj = {};
    }
    
    // Setup an empty entity
    this.entity_ = entityLoader.factory(this.props.objType);

    // Set listener to call this.render when properties change
    alib.events.listen(this.entity_, "change", function(evt){
        // Re-render
        this.render();
    }.bind(this));
    
    if(callbackWhenLoaded) {
        callbackWhenLoaded();
    }
}

/**
 * Render this controller into the dom tree
 */
AdvancedSearchController.prototype.render = function() {
	// Set outer application container
	var domCon = this.domNode_;
	var entities = new Array();
	var entityFields = new Array();
	
    // Define the data
	var data = {
	        eventsObj: this.eventsObj,
	        title: this.props.title || "Advanced Search",
	        entity: this.entity_,
	        objType: this.props.objType,
	        deviceSize: netric.getApplication().device.size,
	        layout: (netric.getApplication().device.size === netric.Device.sizes.small)
	        ? "compact" : "table",
	}
	
	// Render browser component
    this.rootReactNode_ = React.render(
            React.createElement(UiAdvancedSearch, data),
            domCon
    );
}

module.exports = AdvancedSearchController;

