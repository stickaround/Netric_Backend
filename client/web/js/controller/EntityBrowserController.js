/**
 * @fileoverview Entity browser
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var EntityController = require("./EntityController");
var UiEntityBrowser = require("../ui/EntityBrowser.jsx");
var EntityCollection = require("../entity/Collection");
var actionsLoader = require("../entity/actionsLoader");
var definitionLoader = require("../entity/definitionLoader");
var Where = require("../entity/Where");

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
 * Entities that will be displayed
 *
 * @private
 * @type {Array}
 */
EntityBrowserController.prototype.entities_ = new Array();

/**
 * Determine if the entity browser is still loading the entities
 *
 * @private
 * @type {bool}
 */
EntityBrowserController.prototype.collectionLoading_ = true;

/**
 * Entity actions object
 *
 * @private
 * @type {netric.entity.actions.*}
 */
EntityBrowserController.prototype.actions_ = null;

/**
 * View being used to filter and order the collection
 *
 * @type {BrowserView}
 * @private
 */
EntityBrowserController.prototype.browserView_ = null;

/**
 * Contains the entity definition of current object type.
 *
 * @public
 * @type {Array}
 */
EntityBrowserController.prototype.entityDefinition_ = null;


/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
EntityBrowserController.prototype.onLoad = function(opt_callback) {

    this.actions_ = actionsLoader.get(this.props.objType);

    if (this.props.objType) {
        // Get the default view from the object definition
        definitionLoader.get(this.props.objType, function(def){
            this.entityDefinition_ = def;
            this.browserView_ = def.getDefaultView();

            // make sure we have set the objType of the browserView
            this.browserView_.setObjType(this.props.objType);

            // Setup the controller
            this._setupCollection();

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
EntityBrowserController.prototype.render = function() {

    // Render the react components
    this.reactRender_();

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

/**
 * Render the react UI
 * This function will be called everytime we need to send new props or update props into react
 *
 * @private
 */
EntityBrowserController.prototype.reactRender_ = function() {

    // Set outer application container
    var domCon = this.domNode_;

    var layout = (netric.getApplication().device.size === netric.Device.sizes.small)
        ? "compact" : "table";

    // Set custom layouts for different types
    switch (this.entityDefinition_.objType) {
        case 'activity':
        case 'comment':
            layout = "compact";
            break
    }

    // Unhide toolbars if we are in a page mode
    var hideToolbar = this.props.hideToolbar || false;
    if (this.getType() === controller.types.PAGE) {
        hideToolbar = false;
    }

    // Define the data
    var data = {
        title: this.props.browsebytitle ||this.props.title,
        deviceSize: netric.getApplication().device.size,
        layout: layout,
        actionHandler: this.actions_,
        browserView:this.browserView_,
        hideToolbar: hideToolbar,
        onEntityListClick: function(objType, oid, title) {
            this.onEntityListClick(objType, oid, title);
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
        onAdvancedSearch: function() {
            this._displayAdvancedSearch();
        }.bind(this),
        onPerformAction: function(actionName) {
            this.performActionOnSelected(actionName);
        }.bind(this),
        onNavBtnClick: this.props.onNavBtnClick || null,
        onNavBackBtnClick: this.props.onNavBackBtnClick || null,
        selectedEntities: this.selected_,
        entities: this.entities_,
        collectionLoading: this.collectionLoading_
    }

    // Render browser component
    this.rootReactNode_ = ReactDOM.render(
        React.createElement(UiEntityBrowser, data),
        domCon
    );
}

/**
 * User clicked/touched an entity in the list
 *
 * @param {string} objType
 * @param {string} oid
 * @param {string} title The textual name or title of the entity
 */
EntityBrowserController.prototype.onEntityListClick = function(objType, oid, title) {
    if (objType && oid) {
        // Mark the entity as selected
        if (this.props.objType == objType) {
            this.selected_ = [];
            this.selected_.push(oid);

            // Need to re-render to display changes
            this.reactRender_();
        }

        // Check to see if we have an onEntityClick callback registered
        if (this.props.onEntityClick) {
            this.props.onEntityClick(objType, oid);
        } else if (this.props.onSelect) {
            // Check to see if we are running in a browser select mode (like select user)
            this.props.onSelect(objType, oid, title);
            this.unload();
        } else if (this.getRoutePath()) {
            netric.location.go(this.getRoutePath() + "/" + oid);
        } else {
            console.error("User clicked on an entity but there are no handlers");
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

    this.userSearchString_ = fullText;

    this.loadCollection();
}

/**
 * Fill the collection for this browser
 */
EntityBrowserController.prototype.loadCollection = function() {

    // Setup the controller in case it was not setup before
    this._setupCollection();

    // Clear out conditions to remove stale wheres
    this.collection_.clearConditions();
    this.collection_.clearOrderBy();

    // Check filter conditions
    if (this.props.browseby && this.props.browseval) {
        this.collection_.where(this.props.browseby).equalTo(this.props.browseval);
    }

    // Check if the user entered a full-text search condition
    if (this.userSearchString_) {
        this.collection_.where("*").equalTo(this.userSearchString_);
    }

    // Set Sort Order
    var orderBy = this.browserView_.getOrderBy();
    if(orderBy) {
        for (var idx in orderBy) {
            this.collection_.setOrderBy(orderBy[idx].field, orderBy[idx].direction);
        }
    }

    // Sets the conditions for this collection. The conditions are from the browserView conditions and/or from the filters (if they are set)
    this.setCollectionConditions(this.props.filters)

    // Load (we depend on 'onload' events for triggering UI rendering in this.reactRender_)
    this.collection_.load();
}

/**
 * Set the conditions for the collection
 *
 * @param {array} filters   These are the conditions that will limit what this browser can search
 */
EntityBrowserController.prototype.setCollectionConditions = function(filters) {

    // Removes the conditions set to this collection
    this.collection_.clearConditions();

    // Set Conditions
    var conditions = this.browserView_.getConditions();

    // If there is a condition set, then we will push the where clase to the collection
    if(conditions) {
        for (var i in conditions) {
            this.collection_.addWhere(conditions[i]);
        }
    }

    if(filters) {
        for(var idx in filters) {
            this.collection_.addWhere(filters[idx]);
        }
    }
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

        // Need to re-render to display selected entity
        this.reactRender_();
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
        // TODO: select all or set a flag to do so
    } else {
        // Clear all selected
        this.selected_ = new Array();
    }

    // Need to re-render to display the toggle
    this.reactRender_();
}

/**
 * Perform an action on selected messages
 *
 * @param {string} actionName
 */
EntityBrowserController.prototype.performActionOnSelected = function(actionName) {

    var workingText = this.actions_.performAction(actionName, this.props.objType, this.selected_, function(error, message) {

        if (error) {
            console.error(message);
            // TODO: we should probably log this
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

    // Need to re-render to display the entities
    this.entities_ = this.collection_.getEntities();
    this.reactRender_();
}

/**
 * The collection is attempting to get results from the backend
 */
EntityBrowserController.prototype.onCollectionLoading = function() {

    // Need to re-render to display the loading gif
    this.collectionLoading_ = true;
    this.reactRender_();
}

/**
 * The collection has finished requesting results from the backend
 */
EntityBrowserController.prototype.onCollectionLoaded = function() {

    // Need to re-render to hide the loading gif
    this.collectionLoading_ = false;
    this.reactRender_();
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

    // Need to re-render to display changes
    this.reactRender_();
}

/**
 * Refresh public interface refreshes the colleciton
 */
EntityBrowserController.prototype.refresh = function() {
    this.collection_.refresh();
}

/**
 * The collection is updated with new limits to display
 *
 * @param {int} limitIncrease	Optional of entities to increment the limit by. Default is 50.
 */
EntityBrowserController.prototype.getMoreEntities = function(limitIncrease) {

    // set new limit plus 50 if not set
    if(typeof limitIncrease === 'undefined')
        limitIncrease = 50;

    var limit = this.collection_.getLimit();
    var newLimit = limit + limitIncrease; // increase the limit
    var totalNum = this.collection_.getTotalNum();

    // Check if maxed out already so no more actions needed
    if(limit < totalNum) {
        this.collection_.setLimit(newLimit);
        this.collection_.refresh();
    }
}

/**
 * Apply the advanced search
 *
 * @param {object} browserView   View that was cloned and used in Advanced Search
 */
EntityBrowserController.prototype._applyAdvancedSearch = function(browserView) {
    this.browserView_ = browserView;
    this.loadCollection();
}

/**
 * Display Advance search
 *
 */
EntityBrowserController.prototype._displayAdvancedSearch = function() {

    /*
     * We require it here to avoid a circular dependency where the
     * controller requires the view and the view requires the controller
     */
    var AdvancedSearchController = require("./AdvancedSearchController");
    var advancedSearch = new AdvancedSearchController();

    advancedSearch.load({
        type: controller.types.DIALOG,
        title: "Advanced Search",
        objType: this.props.objType,
        entityDefinition: this.entityDefinition_,
        browserView: Object.create(this.browserView_),
        onApplySearch: function(browserView) {
            this._applyAdvancedSearch(browserView)
        }.bind(this),
    });
}

/**
 * Make sure the entity collection is setup for this browser
 *
 * @private
 */
EntityBrowserController.prototype._setupCollection = function() {

    // Only setup the entity controller the first time
    if (this.collection_) {
        return;
    }

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
}

/**
 * Update the filters and refresh the result based on the new conditions that were set
 *
 * @param {array} filters   These are the conditions that will limit what this browser can search
 */
EntityBrowserController.prototype.updateFilters = function(filters) {

    this.setCollectionConditions(filters);
    this.collection_.refresh();
}

module.exports = EntityBrowserController;
