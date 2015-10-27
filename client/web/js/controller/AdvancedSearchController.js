/**
 * @fileoverview Advanced Search
 */
'use strict';

var React = require('react');
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var UiAdvancedSearch = require("../ui/AdvancedSearch.jsx");
var definitionLoader = require("../entity/definitionLoader");

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
 * Object used for handling custom events through the advance search
 *
 * @public
 * @type {Object}
 */
AdvancedSearchController.prototype.eventsObj = null;

/**
 * View being used to filter and order the collection.
 * This will be used to get the initial data for Advanced Search if there is no search view set.
 *
 * @type {BrowserView}
 * @public
 */
AdvancedSearchController.prototype.browserView = null;

/**
 * Contains the entity definition of current object type. This will be used to display the fields for conditions, sorty order and column view.
 *
 * @public
 * @type {Array}
 */
AdvancedSearchController.prototype.entityDefinition = null;

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
    
    // Capture an advance search save
    alib.events.listen(this.eventsObj, "save_advance_search", function(evt) {
        this.saveAdvancedSearch(evt.data);
    }.bind(this));
    
    if (callbackWhenLoaded) {
        callbackWhenLoaded();
    } else {
        this.render();
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
	        title: this.props.title || "Advanced Search",
	        eventsObj: this.eventsObj,
	        objType: this.props.objType,
	        browserView: this.browserView,
	        entityDefinition: this.entityDefinition,
	}
	
	// Render browser component
    this.rootReactNode_ = React.render(
            React.createElement(UiAdvancedSearch, data),
            domCon
    );
}

/**
 * TODO
 * Save the current advanced search settings
 */
AdvancedSearchController.prototype.saveAdvancedSearch = function(searchData) {
    
    var data = [['obj_type', this.props.objType], ['name', searchData.name], ['description', searchData.description]];
    
    for(var criteria in searchData.criteria) {
        var criteriaData = searchData.criteria[idx];
        
        switch(criteria) {
            case 'conditions':
                break;
            case 'sortOrder':
                break;
            case 'columnView':
                break;
        }
    }
}

module.exports = AdvancedSearchController;

