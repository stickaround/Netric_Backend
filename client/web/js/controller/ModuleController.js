/**
 * @fileoverview Main application controller
 */
netric.declare("netric.controller.ModuleController");

netric.require("netric.controller.AbstractController");

/**
 * Controller that loads modules into the applicatino
 */
netric.controller.ModuleController = function() {
}

/**
 * Extend base controller class
 */
netric.inherits(netric.controller.ModuleController, netric.controller.AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
netric.controller.ModuleController.prototype.rootReactNode_ = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
netric.controller.ModuleController.prototype.onLoad = function(opt_callback) {

	// Change the type based on the device size
	switch (netric.getApplication().device.size)
	{
	case netric.Device.sizes.small:
	case netric.Device.sizes.medium:
		this.type_ = netric.controller.types.PAGE;
		break;
	case netric.Device.sizes.large:
		this.type_ = netric.controller.types.FRAGMENT;
		break;
	}

	// By default just immediately execute the callback because nothing needs to be done
	if (opt_callback)
		opt_callback();
}

/**
 * Render this controller into the dom tree
 */
netric.controller.ModuleController.prototype.render = function() { 
	// Set outer application container
	var domCon = this.domNode_;

	var data = {
		name: "Loading...",
		leftNavDocked: (netric.getApplication().device.size == netric.Device.sizes.small) ? false : true,
		leftNavItems: [
			{name: "Create New Entity", "route": "compose"},
			{name: "Browse Entity", "route": "browse"},
			{name: "Third Menu Entry"}
		],
		onLeftNavChange: this.onLeftNavChange_.bind(this)
	}

	// Render application component
	this.rootReactNode_ = React.render(
		React.createElement(netric.ui.Module, data),
		domCon
	);

	// Add route to compose a new entity
	this.addSubRoute("compose", 
		netric.controller.TestController, 
		{ type: netric.controller.types.FRAGMENT }, 
		this.rootReactNode_.refs.moduleMain.getDOMNode()
	);

	// Add route to compose a new entity
	this.addSubRoute("browse", 
		netric.controller.EntityBrowserController, 
		{ 
			type: netric.controller.types.FRAGMENT,
			onNavBtnClick: function(e) { this.rootReactNode_.refs.leftNav.toggle(); }.bind(this) 
		}, 
		this.rootReactNode_.refs.moduleMain.getDOMNode()
	);

	/* 
	 * Add listener to update leftnav state when a child route changes
	 */
	if (this.getChildRouter() && this.rootReactNode_.refs.leftNav) {
		alib.events.listen(this.getChildRouter(), "routechange", function(evt) {
			this.rootReactNode_.refs.leftNav.setState({ selected: evt.data.path });
		}.bind(this));
	}

	// Set a default route to messages
	//this.getChildRouter().setDefaultRoute("browse");
}

/**
 * User selected an alternate menu item in the left navigation
 */
netric.controller.ModuleController.prototype.onLeftNavChange_ = function(evt, index, payload) {
	if (payload && payload.route) {
		var basePath = this.getRoutePath();
		netric.location.go(basePath + "/" + payload.route);
	}
}
