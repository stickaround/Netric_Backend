/**
 * @fileoverview Advance Search
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
var definitionLoader = require("../entity/definitionLoader");
var definitionLoader = require("../entity/groupingLoader");

/**
 * Controller that loads an Advance Search
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
AdvanceSearchController.prototype._rootReactNode = null;

/**
 * A collection of entities
 *
 * @private
 * @type {netric.entity.Collection}
 */
AdvanceSearchController.prototype._fields = null;


/**
 * Entity actions object
 *
 * @private
 * @type {netric.entity.actions.*}
 */
AdvanceSearchController.prototype._actions = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
AdvanceSearchController.prototype.onLoad = function(opt_callback) {

    this.actions_ = actionsLoader.get(this.props.objType);

    if (this.props.objType) {
        // Get the default view from the object definition
        definitionLoader.get(this.props.objType, function(def){
            this.browserView_ = def.getDefaultView();
            if (opt_callback) {
                opt_callback();
            }
        }.bind(this));
    } else if (opt_callback) {
        // By default just immediately execute the callback because nothing needs to be done
        opt_callback();
    }

}

/**
 * Render this controller into the dom tree
 */
AdvanceSearchController.prototype.render = function() {

	// Set outer application container
	var domCon = this.domNode_;

    // Define the data
	var data = {
		title: this.props.browsebytitle ||this.props.title,
        entities: new Array(),
        entityDefinition: new Array(),
        deviceSize: netric.getApplication().device.size,
        layout: (netric.getApplication().device.size === netric.Device.sizes.small)
            ? "compact" : "table",
        actionHandler: this.actions_,
        browserView:this.browserView_,
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
        onLoadMoreEntities: function(limitIncrease){
        	return this.getMoreEntities(limitIncrease);
        }.bind(this),
        onSearchChange: function(fullText, conditions) {
            this.onSearchChange(fullText, conditions);
        }.bind(this),
        onPerformAction: function(actionName) {
            this.performActionOnSelected(actionName);
        }.bind(this),
        onNavBtnClick: this.props.onNavBtnClick || null
	}

	// Render browser component
	this.rootReactNode_ = React.render(
		React.createElement(UiEntityBrowser, data),
		domCon
	);

    // Load the entity list
    this.collection_ = new EntityCollection(this.props.objType);
    alib.events.listen(this.collection_, "change", function() {
        this.onCollectionChange();
    }.bind(this));

    alib.events.listen(this.collection_, "loading", function() {
        this.onCollectionLoading();
    }.bind(this));

    alib.events.listen(this.collection_, "loaded", function() {
        this.onCollectionLoaded();
    }.bind(this));

    // Load the colleciton
    this.loadCollection();
    
    // Add route to browseby
    this.addSubRoute("browse/:browseby/:browseval/:browsebytitle",
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




