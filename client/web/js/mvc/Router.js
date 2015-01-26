/**
* @fileOverview Main router for handling hashed URLS and routing them to views
*
* Views are a little like pages but stay within the DOM. The main advantage is 
* hash codes are used to navigate though a page. Using views allows you to 
* bind function calls to url hashes. Each view only handles one lovel in the url 
* but can have children so /my/url would be represented by views[my].views[url].show
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/

alib.declare("netric.mvc.Router");

alib.require("netric");
alib.require("netric.mvc");

/**
 * Creates an instance of AntViewsRouter
 *
 * @constructor
 * @param {netric.mvc.Route} parentRoute If set this is a sub-route router
 */
netric.mvc.Router = function(parentRoute) {
	
	/**
	 * Keep a record of the last route loaded
	 * 
	 * @private
	 * @type {string}
	 */
	this.lastLoaded = "";
	
	/**
	 * Default route name
	 *
	 * @public
	 * @type {string}
	 */
	this.defaultRoute = "";
	
	/**
	 * Additional free-form options
	 * 
	 * @type {Object}
	 */
	this.options = new Object();

	// Begin watching/pinging the hash of the document location
	var me = this;
	this.interval = window.setInterval(function(){ me.checkNav(); }, 50);
}

/** 
 * Query the navigation from either the history or hash
 * 
 * Currently this only supports using the hash, but in the 
 * future we may use the HTML history API to load the pages
 * without a hash tag.
 */
netric.mvc.Router.prototype.checkNav = function() {
	var load = "";
	if (document.location.hash)
	{
		var load = document.location.hash.substring(1);
	}
    
	if (load == "" && this.defaultRoute != "")
		load = this.defaultRoute;

	if (load != "" && load != this.lastLoaded)
	{
		this.lastLoaded = load;
		//ALib.m_debug = true;
		//ALib.trace(load);
		this.onchange(load);
	}
}

/**
 * Callback can be overridden and triggered when a hash changes in the URL
 */
netric.mvc.Router.prototype.onchange = function(path) {
}

/**
 * Add a route segment to the current level
 * 
 * @param {string} segmentName Can be a constant string or a variable with ":" prefixed which falls back to the previous route(?)
 * @param {Controller} controller The controller to load
 * @param {Object} data Optional data to pass to the controller when routed to
 * @param {ReactElement} opt_element Optional parent element to render a fragment into
 */
netric.mvc.Router.prototype.addRoute = function(segmentName, controller, data, opt_element) {

}
