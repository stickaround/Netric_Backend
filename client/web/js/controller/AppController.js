/**
 * @fileoverview Main application controller
 */
alib.declare("netric.controller.AppController");

alib.require("netric.mvc.Controller");
alib.require("netric.controller");

// Include views
alib.require("netric.template.application.small");
alib.require("netric.template.application.large");

/**
 * TMake sure the netric controller namespace exists
 */
netric.controller = netric.controller || {};

netric.controller.AppController = function(domCon) {
	// Case base class constructor
	netric.mvc.Controller.call(this, domCon);
}

/**
 * Extend base controller class
 */
alib.inherits(netric.controller.AppController, netric.mvc.Controller);

/**
 * Default action will be called if action was specified
 *
 * @param {netric.mvc.View}
 */
netric.controller.AppController.prototype.mainAction = function(view) {

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

	// Add modules controller
	
}