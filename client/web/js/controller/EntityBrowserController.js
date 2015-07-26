/**
 * @fileoverview Entity browser
 */
'use strict';

var React = require('react');
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var EntityController = require("./EntityController");
var UiEntityBrowser = require("../ui/EntityBrowser.jsx");
var EntityCollection = require("../entity/Collection");
var actionsLoader = require("../entity/actionsLoader");

/**
 * Controller that loads an entity browser
 */
var EntityBrowserController = function() {}

/**
 * Extend base controller class
 */
netric.inherits(EntityBrowserController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
EntityBrowserController.prototype.rootReactNode_ = null;

/**
 * A collection of entities
 *
 * @private
 * @type {netric.entity.Collection}
 */
EntityBrowserController.prototype.collection_ = null;

/**
 * Set if the user searched for something
 *
 * @private
 * @type {string}
 */
EntityBrowserController.prototype.userSearchString_ = null;


/**
 * Selected entities
 *
 * @private
 * @type {int[]}
 */
EntityBrowserController.prototype.selected_ = new Array();

/**
 * Entity actions object
 *
 * @private
 * @type {netric.entity.actions.*}
 */
EntityBrowserController.prototype.actions_ = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
EntityBrowserController.prototype.onLoad = function(opt_callback) {

    this.actions_ = actionsLoader.get(this.props.objType);

	// By default just immediately execute the callback because nothing needs to be done
	if (opt_callback)
		opt_callback();
}

/**
 * Render this controller into the dom tree
 */
EntityBrowserController.prototype.render = function() { 
	// Set outer application container
	var domCon = this.domNode_;

    // Define the data
	var data = {
		title: this.props.title,
        entities: new Array(),
        deviceSize: netric.getApplication().device.size,
        layout: (netric.getApplication().device.size === netric.Device.sizes.small)
            ? "compact" : "table",
        actionHandler: this.actions_,
        onEntityListClick: function(objType, oid) {
            this.onEntityListClick(objType, oid);
        }.bind(this),
        onEntityListSelect: function(oid) {
            if (oid) {
                this.toggleEntitySelect(oid);
            } else {
                this.toggleSelectAll(false);
            }
        }.bind(this),
        onSearchChange: function(fullText, conditions) {
            this.onSearchChange(fullText, conditions);
        }.bind(this),
        onPerformAction: function(actionName) {
            this.performActionOnSelected(actionName);
        }.bind(this),
        onNavBtnClick: this.props.onNavBtnClick || null
	}

	// Render application component
	this.rootReactNode_ = React.render(
		React.createElement(UiEntityBrowser, data),
		domCon
	);

    // Load the entity list
    this.collection_ = new EntityCollection(this.props.objType);
    alib.events.listen(this.collection_, "change", function() {
        this.onCollectionChange();
    }.bind(this));

    // Load the colleciton
    this.loadCollection();

    // Add route to browseby
    this.addSubRoute("browse/:browseby/:browseval",
        EntityBrowserController, {
            type: controller.types.PAGE,
            title: this.props.title,
            objType: this.props.objType,
            onNavBtnClick: this.props.onNavBtnClick || null
        }
    );

	// Add route to compose a new entity
	this.addSubRoute(":eid",
		EntityController, {
            type: controller.types.PAGE,
            objType: this.props.objType
        }
	);
}

/**
 * User clicked/touched an entity in the list
 */
EntityBrowserController.prototype.onEntityListClick = function(objType, oid) {
    if (objType && oid) {
        // Mark the entity as selected
        if (this.props.objType == objType) {
            this.selected_ = new Array();
            this.selected_.push(oid);
            this.rootReactNode_.setProps({selectedEntities: this.selected_});
        }

        // Check to see if we have an onEntityClick callback registered
        if (this.props.onEntityClick) {
            this.props.onEntityClick(objType, oid);
        } else if (this.getRoutePath()) {
            netric.location.go(this.getRoutePath() + "/" + oid);
        }
    }
}

/**
 * Fired if the user changes search conditions in the UI
 * 
 * @param {string} fullText Search string
 * @param {netric/entity/Where[]} opt_conditions Array of filter conditions
 */
EntityBrowserController.prototype.onSearchChange = function(fullText, opt_conditions) {
    var conditions = opt_conditions || null;
    console.log("Filter the collection with:", fullText);

    this.userSearchString_ = fullText;

    this.loadCollection();

}

/**
 * Fill the collection for this browser
 */
EntityBrowserController.prototype.loadCollection = function() {

    // Clear out conditions to remove stale wheres
    this.collection_.clearConditions();

    // Check filter conditions
    if (this.props.browseby && this.props.browseval) {
        this.collection_.where(this.props.browseby).equalTo(this.props.browseval);
    }

    // Check if the user entered a full-text search condition
    if (this.userSearchString_) {
        this.collection_.where("*").equalTo(this.userSearchString_);
    }

    // Load (we depend on 'onload' events for triggering UI rendering in this.render)
    this.collection_.load();
}

/**
 * User clicked/touched an entity in the list
 */
EntityBrowserController.prototype.toggleEntitySelect = function(oid) {
    if (oid) {
        var selectedAt = this.selected_.indexOf(oid);

        if (selectedAt == -1) {
            this.selected_.push(oid);
        } else {
            this.selected_.splice(selectedAt, 1);
        }

        this.rootReactNode_.setProps({selectedEntities: this.selected_});
    }
}

/**
 * Select or deselect all
 *
 * @param {bool} selected If true select all, else deselect all
 */
EntityBrowserController.prototype.toggleSelectAll = function(selected) {
    if (typeof selected == "undefined") {
        var selected = false;
    }

    if (selected) {
        // TODO: slect all or set a flag to do so
    } else {
        // Clear all selected
        this.selected_ = new Array();
    }

    this.rootReactNode_.setProps({selectedEntities: this.selected_});
}

/**
 * Perform an action on selected messages
 *
 * @param {string} actionName
 */
EntityBrowserController.prototype.performActionOnSelected = function(actionName) {

    var workingText = this.actions_.performAction(actionName, this.selected_, function(error, message) {
        
        if (error) {
            console.error(message);
        } else {
            console.log(message);
        }

        // TODO: clear workingText notification

        // Refresh the collection to display the changes
        this.collection_.refresh();
    
    }.bind(this));

    // TODO: display working notification(workingText) only if the function has not already finished
}

/**
 * User selected an alternate menu item in the left navigation
 */
EntityBrowserController.prototype.onCollectionChange = function() {
    var entities = this.collection_.getEntities();
    this.rootReactNode_.setProps({entities: entities});
}

/**
 * Called when this controller is paused and moved to the background
 */
EntityBrowserController.prototype.onPause = function() {

}

/**
 * Called when this function was paused but it has been resumed to the foreground
 */
EntityBrowserController.prototype.onResume = function() {
    /*
     * Clear selected because we do not want the last selected entity to still
     * be selected when a user closes the previously selected entity controller.
     */
    this.selected_ = new Array();
    this.rootReactNode_.setProps({selectedEntities: this.selected_});
}

module.exports = EntityBrowserController;
