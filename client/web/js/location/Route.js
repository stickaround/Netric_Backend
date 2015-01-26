/**
* @fileOverview Route represents a single route segment
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2015 Aereus Corporation. All rights reserved.
*/

alib.declare("netric.location.Route");

alib.require("netric");

/**
 * Make sure namespace exists
 */
netric.location = netric.location || {};

/**
 * Route segment
 *
 * @constructor
 * @param {netric.locateion.Router} parentRouter Instance of a parentRouter that will own this route
 * @param {string} segmentName Can be a constant string or a variable with ":" prefixed which falls back to the previous route(?)
 * @param {Controller} controller The controller to load
 * @param {Object} opt_data Optional data to pass to the controller when routed to
 * @param {ReactElement} opt_element Optional parent element to render a fragment into
 */
netric.location.Route = function(parentRouter, segmentName, controller, opt_data, opt_element) {

	/**
	 * Path of this route segment
	 * 
	 * @type {string}
	 */
	this.name_ = segmentName;

	/** 
	 * Set and cache number of segments represented in this route
	 *
	 * @private
	 * @type {int}
	 */
	this.numPathSegments_ = ("/" == segmentName) ? 1 : this.name_.split("/").length;

	/**
	 * Parent inbound router
	 *
	 * @private
	 * @type {netric.location.Router}
	 */
	this.parentRouter_ = parentRouter;

	/**
	 * Controller class that acts and the UI handler for this route
	 * 
	 * This is just the class name, it has not yet been instantiated
	 *
	 * @type {classname: netric.controller.AbstractController}
	 */
	this.controllerClass_ = controller;

	/**
	 * Cached instance of this.controllerClass_
	 *
	 * We are lazy with the loading of the controller to preserve resources
	 * until absolutely necessary.
	 *
	 * @type {netric.controller.AbstractController}
	 */
	this.controller_ = null;

	/**
	 * Data to pass to the controller once instantiated
	 *
	 * @private
	 * @type {Object}
	 */
	this.controllerData_ = opt_data || {};

	/**
	 * Outbound next-hop router
	 *
	 * @private
	 * @type {netric.location.Router}
	 */
	this.nexthopRouter_ = new netric.location.Router(this.parentRouter_);

	/**
	 * The domNode that we should render this route into
	 *
	 * @private
	 * @type {ReactElement|DomElement}
	 */
	this.domNode_ = opt_element;

}

/**
 * Called when the router moves to this route for the first time
 *
 * @param {Object} opt_params Optional URL params object
 * @param {function} opt_callback If set call this function when we are finished loading route
 */
netric.location.Route.prototype.enterRoute = function(opt_params, opt_callback) {

	var doneLoadingCB = opt_callback || null;

	// Instantiate the controller if not already done (lazy load)
	if (null == this.controller_) {
		this.controller_ = new this.controllerClass_;
	}

	// Load up the controller and pass the callback if set
	this.controller_.load(this.controllerData_, this.domNode_, this.getChildRouter(), doneLoadingCB);
}

/**
 * Called when the router moves away from this route to show an alternate route
 */
netric.location.Route.prototype.exitRoute = function() {
	// Exit all childen first
	if (this.getChildRouter().getActiveRoute()) {
		this.getChildRouter().getActiveRoute().exitRoute();
	}

	// Now unload the controller
	if (this.getController()) {

		this.getController().unload();

		// Delete the controller object
		this.controller_ = null;
	}
}

/**
 * Get this route segment name
 *
 * @return {string}
 */
netric.location.Route.prototype.getName = function() {
	return this.name_;
}

/**
 * Get the full path to this route
 *
 * @return {string} Full path leading up to and including this path
 */
netric.location.Route.prototype.getPath = function() {
	return this.parentRouter_.getActivePath();
}

/**
 * Get the router for the next hops
 */
netric.location.Route.prototype.getChildRouter = function() {
	return this.nexthopRouter_;
}

/**
 * Get the number of segments in this route path name
 * 
 * This is important paths like myroute/:varA/:varB
 * because we need to pull all three segmens from a path
 * in order to determine if the route matches any given path.
 *
 * @return {int} The number of segments this route handles
 */
netric.location.Route.prototype.getNumPathSegments = function() {
	return this.numPathSegments_;
}

/**
 * Test this route against a path to see if it matches
 *
 * @param {string} path The path to test
 * @return {Object|null} If a match is found it retuns an object with .params object and nextHopPath to continue route
 */
netric.location.Route.prototype.matchesPath = function(path) {

	// If this is a simple one to one then retun a basic match and save cycles
	if (path === this.name_ || ("" == path && this.name_ == "/")) {
		return { path:path, params:{}, nextHopPath:"" }
	}

	// Pull this.numPathSegments_ from the front of the path to test
	var pathReq = this.getPathSegments(path, this.numPathSegments_);
	if (pathReq != null) {
		// Now check for a match and parse params
		var params = netric.location.path.extractParams(this.name_, pathReq.target);

		// If params is null then the path does not match at all
		if (params !== null) {
			return {
				path: pathReq.target,
				params: params,
				nextHopPath: pathReq.remainder
			}
		}
	}

	// No match was found
	return null;
}

/**
 * Extract a number of segments from a path for matching
 *
 * @param {string} path
 * @param {int} numSegments
 * @return {Object} Format: {target:"math/with/my/num/segs", remainder:"any/trailing/path"}
 */
netric.location.Route.prototype.getPathSegments = function(path, numSegments) {

	var testTarget = "";

	var parts = path.split("/");

	// If the path does not have enough segments to match this route then return
	if (parts.length < numSegments)
		return null;

	// Set the targetPath for this route
	var targetPath = "";
	for (var i = 0; i < numSegments; i++) {
		if (targetPath.length > 0 || parts[i] == "") {
			targetPath += "/";
		}

		targetPath += parts[i];
	}

	// Get the remainder
	var rem = "";
	if (parts.length != numSegments) {
		// Step over "/" if exists
		var startPos = ("/" == path[targetPath.length]) ? targetPath.length + 1 : targetPath.length;
		rem = path.substring(startPos); 
	}

	return {target:targetPath, remainder:rem}

} 

/**
 * Get the controller instance for this route
 * 
 * @return {netric.contoller.AbstractController}
 */
netric.location.Route.prototype.getController = function() {
	return this.controller_;
}
