/**
 * @fileoverview Main application controller
 */
'use strict';

var React = require('react');
var netric = require("../base");
var controller = require("./controller")
var AbstractController = require("./AbstractController");
var TestController = require("./TestController");
var EntityBrowserController = require("./EntityBrowserController");
var UiModule = require("../ui/Module.jsx");
var moduleLoader = require("../module/loader");

/**
 * Controller that loads modules into the applicatino
 */
var ModuleController = function() {
}

/**
 * Extend base controller class
 */
netric.inherits(ModuleController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
ModuleController.prototype.rootReactNode_ = null;

/**
 * Loaded module definition
 *
 * @type {netric.module.Module}
 */
ModuleController.prototype.module_ = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
ModuleController.prototype.onLoad = function(opt_callback) {

	// Change the type based on the device size
	switch (netric.getApplication().device.size)
	{
	case netric.Device.sizes.small:
		this.type_ = controller.types.PAGE;
		break;
    case netric.Device.sizes.medium:
	case netric.Device.sizes.large:
		this.type_ = controller.types.FRAGMENT;
		break;
	}

	// By default just immediately execute the callback because nothing needs to be done
	if (opt_callback) {
        moduleLoader.get(this.props.module, function(module) {
            this.module_ = module;
            opt_callback();
        }.bind(this));
    } else {
        opt_callback();
    }
}

/**
 * Render this controller into the dom tree
 */
ModuleController.prototype.render = function() { 

	// Set outer application container
	var domCon = this.domNode_;

    // Initialize properties to send to the netric.ui.Module view
	var data = {
		name: this.module_.name,
        title: this.module_.title,
		leftNavDocked: (netric.getApplication().device.size == netric.Device.sizes.large) ? true : false,
		leftNavItems: [
			{name: "Create New Entity", "route": "compose"},
			{name: "Browse Entity", "route": "browse"},
			{name: "Third Menu Entry"}
		],
        modules: moduleLoader.getModules(),
		onLeftNavChange: this.onLeftNavChange_.bind(this)
	}

	// Render application component
	this.rootReactNode_ = React.render(
		React.createElement(UiModule, data),
		domCon
	);

	// Add route to compose a new entity
	this.addSubRoute("compose", 
		TestController, 
		{ type: controller.types.FRAGMENT }, 
		this.rootReactNode_.refs.moduleMain.getDOMNode()
	);

	// Add route to compose a new entity
	this.addSubRoute("browse", 
		EntityBrowserController,
        {
			type: controller.types.FRAGMENT,
            objType: "note",
			onNavBtnClick: (netric.getApplication().device.size == netric.Device.sizes.large) ?
                null : function(e) { this.rootReactNode_.refs.leftNav.toggle(); }.bind(this)
		}, 
		this.rootReactNode_.refs.moduleMain.getDOMNode()
	);

	// Add listener to update leftnav state when a child route changes
	if (this.getChildRouter() && this.rootReactNode_.refs.leftNav) {
		alib.events.listen(this.getChildRouter(), "routechange", function(evt) {
			this.rootReactNode_.refs.leftNav.setState({ selected: evt.data.path });
		}.bind(this));
	}

	// Set a default route to messages
	this.getChildRouter().setDefaultRoute("browse");
}

/**
 * User selected an alternate menu item in the left navigation
 */
ModuleController.prototype.onLeftNavChange_ = function(evt, index, payload) {
	if (payload && payload.route) {
		var basePath = this.getRoutePath();
		netric.location.go(basePath + "/" + payload.route);
	}
}

module.exports = ModuleController;