/**
 * @fileoverview Entity browser
 */
netric.declare("netric.controller.EntityBrowserController");

netric.require("netric.controller.AbstractController");

/**
 * Controller that loads an entity browser
 */
netric.controller.EntityBrowserController = function() {
}

/**
 * Extend base controller class
 */
netric.inherits(netric.controller.EntityBrowserController, netric.controller.AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
netric.controller.EntityBrowserController.prototype.rootReactNode_ = null;

/**
 * A collection of entities
 *
 * @private
 * @type {netric.entity.Collection}
 */
netric.controller.EntityBrowserController.prototype.collection_ = null;


/**
 * Selected entities
 *
 * @private
 * @type {int[]}
 */
netric.controller.EntityBrowserController.prototype.selected_ = new Array();

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
netric.controller.EntityBrowserController.prototype.onLoad = function(opt_callback) {

	// By default just immediately execute the callback because nothing needs to be done
	if (opt_callback)
		opt_callback();
}

/**
 * Render this controller into the dom tree
 */
netric.controller.EntityBrowserController.prototype.render = function() { 
	// Set outer application container
	var domCon = this.domNode_;

	var data = {
		name: "Loading...",
        entities: new Array(),
        layout: (netric.getApplication().device.size === netric.Device.sizes.small)
            ? "compact" : "table",
        onEntityListClick: function(objType, oid) {
            this.onEntityListClick(objType, oid);
        }.bind(this),
        onEntityListSelect: function(oid) {
            this.toggleEntitySelect(oid);
        }.bind(this),
        onNavBtnClick: this.props.onNavBtnClick || null
	}

	// Render application component
	this.rootReactNode_ = React.render(
		React.createElement(netric.ui.EntityBrowser, data),
		domCon
	);

    // Load the entity list
    this.collection_ = new netric.entity.Collection(this.props.objType);
    alib.events.listen(this.collection_, "change", function() {
        this.onCollectionChange();
    }.bind(this));
    this.collection_.load();

	/*
	// Add route to compose a new entity
	this.addSubRoute("compose", 
		netric.controller.TestController, 
		{ type: netric.controller.types.FRAGMENT }, 
		this.rootReactNode_.refs.moduleMain.getDOMNode()
	);
	*/

	// Add route to compose a new entity
	this.addSubRoute(":oid",
		netric.controller.EntityController,
		{
            type: netric.controller.types.PAGE,
            objType: this.props.objType
        }
	);
}

/**
 * User clicked/touched an entity in the list
 */
netric.controller.EntityBrowserController.prototype.onEntityListClick = function(objType, oid) {
    if (objType && oid) {

        // Mark the entity as selected
        if (this.props.objType == objType) {
            this.selected_ = new Array();
            this.selected_.push(oid);
            this.rootReactNode_.setProps({selectedEntities: this.selected_});
        }

        var basePath = this.getRoutePath();
        netric.location.go(basePath + "/" + oid);
    }
}

/**
 * User clicked/touched an entity in the list
 */
netric.controller.EntityBrowserController.prototype.toggleEntitySelect = function(oid) {
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
 * User selected an alternate menu item in the left navigation
 */
netric.controller.EntityBrowserController.prototype.onCollectionChange = function() {
    var entities = this.collection_.getEntities();
    this.rootReactNode_.setProps({entities: entities});
}

/**
 * Called when this controller is paused and moved to the background
 */
netric.controller.EntityBrowserController.prototype.onPause = function() {

}

/**
 * Called when this function was paused but it has been resumed to the foreground
 */
netric.controller.EntityBrowserController.prototype.onResume = function() {
    /*
     * Clear selected because we do not want the last selected entity to still
     * be selected when a user closes the previously selected entity controller.
     */
    this.selected_ = new Array();
    this.rootReactNode_.setProps({selectedEntities: this.selected_});
}