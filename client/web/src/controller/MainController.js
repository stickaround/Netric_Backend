/**
 * @fileoverview This is the main controller used for the base application
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var netric = require("../base");
var AbstractController = require("./AbstractController");
var ModuleController = require("./ModuleController");
var UiAppLarge = require("../ui/application/Large.jsx");
var UiAppSmall = require("../ui/application/Small.jsx");

/**
 * Main application controller
 */
var MainController = function() {

	this.application = netric.getApplication();

}

/**
 * Extend base controller class
 */
netric.inherits(MainController, AbstractController);

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
MainController.prototype.onLoad = function(opt_callback) {

	// By default just immediately execute the callback because nothing needs to be done
	if (opt_callback)
		opt_callback();
}

/**
 * Render this controller into the dom tree
 */
MainController.prototype.render = function() { 

	// Set outer application container
	var domCon = this.domNode_;

	// Get a view component for rendering
	switch (netric.getApplication().device.size)
	{
	case netric.Device.sizes.small:
		this.appComponent_ = UiAppSmall;
		break;
	case netric.Device.sizes.medium:
		this.appComponent_ = UiAppSmall;
		break;
	case netric.Device.sizes.large:
	case netric.Device.sizes.xlarge:
		this.appComponent_ = UiAppLarge;
		break;
	default:
		throw "Device size it not supported with a view";
		break;
	}

	// Setup application data
	var data = {
		orgName : netric.getApplication().getAccount().orgName,
		module : netric.getApplication().getAccount().defaultModule,
		logoSrc : "img/netric-logo-32.png",
		basePath : this.getParentRouter().getActivePath()
	}

	// Render application component
	var view = ReactDOM.render(
		React.createElement(this.appComponent_, data),
		domCon
	);

	// Add dynamic route to the module
	this.addSubRoute(":module", 
		ModuleController, {},
		ReactDOM.findDOMNode(view.refs.appMain)
	);

	// Set a default route
	this.getChildRouter().setDefaultRoute(netric.getApplication().getAccount().defaultModule);
}

module.exports = MainController;
