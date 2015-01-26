/**
 * @fileoverview This is the main controller used for the base application
 */
netric.declare("netric.controller.MainController");

netric.require("netric.controller");

/**
 * Main application controller
 */
netric.controller.MainController = function() {

	this.application = netric.getApplication();

}

/**
 * Extend base controller class
 */
netric.inherits(netric.controller.MainController, netric.controller.AbstractController);

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
netric.controller.MainController.prototype.onLoad = function(opt_callback) {

	// By default just immediately execute the callback because nothing needs to be done
	if (opt_callback)
		opt_callback();
}

/**
 * Render this controller into the dom tree
 */
netric.controller.MainController.prototype.render = function() { 
	// Set outer application container
	var domCon = this.domNode_;

	// Get a view component for rendering
	switch (netric.getApplication().device.size)
	{
	case netric.Device.sizes.small:
		this.appComponent_ = netric.ui.application.Small;
		break;
	case netric.Device.sizes.medium:
		this.appComponent_ = netric.ui.application.Large;
		break;
	case netric.Device.sizes.large:
		this.appComponent_ = netric.ui.application.Large;
		break;
	}

	// Setup application data
	var data = {
		orgName : netric.getApplication().getAccount().orgName,
		module : "messages",
		logoSrc : "img/netric-logo-32.png",
		basePath : this.getParentRouter().getActivePath()
	}

	// Render application component
	var view = React.render(
		React.createElement(this.appComponent_, data),
		domCon
	);

	// Add dynamic route to the module
	this.addSubRoute(":module", 
		netric.controller.ModuleController, 
		{}, 
		view.refs.appMain.getDOMNode()
	);

	// Set a default route to messages
	this.getChildRouter().setDefaultRoute("messages");
}
