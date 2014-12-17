/**
 * @fileoverview Main application controller
 */
alib.declare("netric.controller.ModuleController");

alib.require("netric.mvc.Controller");
alib.require("netric.controller");

// Include views
alib.require("netric.template.application.small");
alib.require("netric.template.application.large");

/**
 * Make sure the netric controller namespace exists
 */
netric.controller = netric.controller || {};

netric.controller.ModuleController = function(domCon) {
	// Case base class constructor
	netric.mvc.Controller.call(this, domCon);
}

/**
 * Extend base controller class
 */
alib.inherits(netric.controller.ModuleController, netric.mvc.Controller);

/**
 * Default action will be called if action was specified
 *
 * @param {netric.mvc.View}
 */
netric.controller.ModuleController.prototype.mainAction = function(view) {

	/*
	switch (netric.getApplication().device.size)
	{
	case netric.Device.sizes.small:
		view.setTemplate(netric.view.application.small);
		break;
	case netric.Device.sizes.medium:
	case netric.Device.sizes.large:
		view.setTemplate(netric.view.application.large);
		break;
	}
	*/

	// TODO: add actions for each object type in the navigation

}

/**
 * Default action will be called if action was specified
 *
 * @param {netric.mvc.View}
 */
netric.controller.ModuleController.prototype.browseAction = function(view) {
	
	// Component model
	var entityBrowser = new netric.ui.entity.Browser('customer', view);
	// TODO: set all conditions here
	entityBrowser.render(view.con);

	/* Concept for creating an entity browser
	// MVC model
	var brwsr = new netric.controller.EntityBrowser(view.con, this);
	brwsr.renderAction('main', {objType:'customer'});
	*/

}