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
 * A collection of advanced search criteria that contains data for conditions, sort by and column view that were already set
 *
 * @public
 * @type {Object}
 */
AdvancedSearchController.prototype.savedCriteria = null;

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
	
	// If saved criteria is not set, then we need to get the initial values from the browser view
	if(this.savedCriteria == null && this.browserView) {
	    this.savedCriteria = [];
	    
	    // UPDATET THIS CODE!!! - marl
	    
	    // Get the Conditions from browser view
	    var conditions = this.browserView.getConditions();
	    if(conditions) {
	        this.savedCriteria['conditions'] = [];
	        for (var idx in conditions) {
	            this.savedCriteria['conditions'].push(conditions[idx]);
	        }
	    }
	    
	    // Get the order by from the browser view
        var sortOrder = this.browserView.getOrderBy()
        if(sortOrder) {
            this.savedCriteria['sortOrder'] = [];
            for (var idx in sortOrder) {
                this.savedCriteria['sortOrder'].push({
                                                        fieldName: sortOrder[idx].field,
                                                        direction: sortOrder[idx].direction,
                                                    });
            }
        }
	    
	    // Get the columns from the browser view
	    var columns = this.browserView.getTableColumns()
	    if(columns) {
	        this.savedCriteria['columnView'] = [];
            for (var idx in columns) {
                this.savedCriteria['columnView'].push({fieldName: columns[idx]});
            }
	    }
	}
	
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

