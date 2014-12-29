/**
* @fileOverview UI presentation layer for Application
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.ui.ApplicationView");

alib.require("netric");
alib.require("netric.mvc.ViewManager");
alib.require("netric.template.application.small");
alib.require("netric.template.application.large");

/**
 * Make sure the ui namespace exists
 */
netric.ui = netric.ui || {};

/**
 * Application instance
 *
 * @param {netric.mvc.ViewManager} root level viewmanager*
 * @param {netric.Application} application Model of application
 */
netric.ui.ApplicationView = function(application) {

	/**
	 * Application model instance
	 * 
	 * @public
	 * @type {netric.Application}
	 */
	this.application = application;

	/**
	 * Base application view manager
	 *
	 * @public
	 * @type {netric.mvc.ViewManager}
	 */
	this.viewManager = new netric.mvc.ViewManager();

	/**
	 * Outer container - usually the body
	 * 
	 * @private
	 * @type {DOMElement}
	 */
	this.outerCon_ = null;

}

/**
 * Render the application into the DOM
 *
 * @param {DOMElement} domCon Container to place the application into
 */
netric.ui.ApplicationView.prototype.render = function(domCon) {

	// Set outer application container
	this.outerCon_ = domCon;

	// Clear the continer
	this.outerCon_.innterHTML = "";

	// Get a view template for rendering
	var template = null;
	switch (this.application.device.size)
	{
	case netric.Device.sizes.small:
		template = this.renderSmall_();
		break;
	case netric.Device.sizes.medium:
		template = this.renderMedium_();
		break;
	case netric.Device.sizes.large:
		template = this.renderLarge_();
		break;
	}

	// Render the template into this.outerCon_;
	if (template) {
		template.render(this.outerCon_);
	}
}

/**
 * Called when the main application window resizes
 */
netric.ui.ApplicationView.prototype.resize = function() {
	this.viewManager.resizeActiveView();
}

/**
 * Load child views by path
 *
 * @param {string} path The full path to load inlcuding all children
 */
netric.ui.ApplicationView.prototype.load = function(path) {
	return this.viewManager.load(path);
}

/**
 * Render application shell for small device
 * 
 * @private
 */
netric.ui.ApplicationView.prototype.renderSmall_ = function() {
	return netric.template.application.small();
}

/**
 * Render application shell for medium device
 * 
 * @private
 */
netric.ui.ApplicationView.prototype.renderMedium_ = function() {
	// Currently large is responsive enough to work on tablets
	return this.renderLarge_();
}

/**
 * Render application shell for large device
 * 
 * @private
 */
netric.ui.ApplicationView.prototype.renderLarge_ = function() {
	var data = {
		logoSrc : "img/netric-logo-32.png"
	}
	
	return netric.template.application.large(data);
}