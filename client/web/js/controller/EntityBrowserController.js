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
		onNavBtnClick: this.props.onNavBtnClick || null
	}

	// Render application component
	this.rootReactNode_ = React.render(
		React.createElement(netric.ui.EntityBrowser, data),
		domCon
	);

	/*
	// Add route to compose a new entity
	this.addSubRoute("compose", 
		netric.controller.TestController, 
		{ type: netric.controller.types.FRAGMENT }, 
		this.rootReactNode_.refs.moduleMain.getDOMNode()
	);

	// Add route to compose a new entity
	this.addSubRoute("browse", 
		netric.controller.TestController, 
		{ type: netric.controller.types.FRAGMENT }, 
		this.rootReactNode_.refs.moduleMain.getDOMNode()
	);
	*/
}

/**
 * User selected an alternate menu item in the left navigation
 */
netric.controller.EntityBrowserController.prototype.onLeftNavChange_ = function(evt, index, payload) {
	if (payload && payload.route) {
		var basePath = this.getRoutePath();
		netric.location.go(basePath + "/" + payload.route);
	}
}
