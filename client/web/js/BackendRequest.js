/**
* @fileOverview Backend request object used to direct requests local or to remote server
*
* Example:
* <code>
* 	var request = new netric.BackendRequest();
*	
*	// Setup callback for successful load
*	alib.events.listen(request, "load", function(evt) { 
* 		var data = this.getResponse();
*		alert(evt.data.passVarToEvtData); // Prompts "MyData"
*	}, {passVarToEvtData:"MyData"});
*
*	// Set callback on error
*	alib.events.listen(request, "error", function(evt) { } );
*
*	var ret = request.send("/controller/object/getDefinition", "POST", {obj_type:this.objType});
* </code>
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.BackendRequest");

alib.require("netric");
alib.require("netric.server");

/**
 * Class for handling XMLHttpRequests
 *
 * @constructor
 */
netric.BackendRequest = function() {
	/**
	 * Handle to server xhr to use when connected
	 *
	 * @private
	 * @type {alib.net.Xhr}
	 */
	this.netXhr_ = new alib.net.Xhr();
}

/**
 * What kind of data is being returned
 *
 * Can be xml, json, script, text, or html
 *
 * @private
 * @type {string}
 */
netric.BackendRequest.prototype.returnType_ = "json";

/**
 * Determine whether or not we will send async or hang the UI until request returns (yikes)
 *
 * @private
 * @type {bool}
 */
netric.BackendRequest.prototype.isAsync_ = true;

/**
 * Number of seconds before the request times out
 *
 * 0 means no timeout
 *
 * @private
 * @type {int}
 */
netric.BackendRequest.prototype.timeoutInterval_ = 0;

/**
 * Buffer for response
 *
 * @private
 * @type {bool}
 */
netric.BackendRequest.prototype.response_ = null;

/**
 * Static send that creates a short lived instance.
 *
 * @param {string} url Uri to make request to.
 * @param {Function=} opt_callback Callback function for when request is complete.
 * @param {string=} opt_method Send method, default: GET.
 * @param {Object|Array} opt_content Body data if POST.
 * @param {number=} opt_timeoutInterval Number of milliseconds after which an
 *     incomplete request will be aborted; 0 means no timeout is set.
 */
netric.BackendRequest.send = function(url, opt_callback, opt_method, opt_content, opt_timeoutInterval) 
{
	// Set defaults
	if (typeof opt_method == "undefined")
		opt_method = "GET";
	if (typeof opt_content == "undefined")
		opt_content = null;

	// Crete new Xhr instance and send
	var request = new netric.BackendRequest();
	if (opt_callback) {
		alib.events.listen(request, "load", function(evt) { 
			evt.data.cb(this.getResponse); 
		}, {cb:opt_callback});
	}
	
	if (opt_timeoutInterval) {
		request.setTimeoutInterval(opt_timeoutInterval);
	}

	request.send(url, opt_method, opt_content);
	return request;
};

/**
 * Instance send that actually makes a server call.
 *
 * @param {string|goog.Uri} urlPath Uri to make request to.
 * @param {string=} opt_method Send method, default: GET.
 * @param {Array|Object|string=} opt_content Body data.
 */
netric.BackendRequest.prototype.send = function(urlPath, opt_method, opt_content) 
{
	var method = opt_method || "GET";
	var data = opt_content || null;

	// Check if we need to put a prefix on the request
	if (netric.server.host != "") {
		alib.net.prefixHttp = netric.server.host;
	}

	// Set local variable for closure
	var xhr = this.netXhr_;
	var request = this;
	
	// Fire load event
	alib.events.listen(xhr, "load", function(evt){
		alib.events.triggerEvent(request, "load");
	});

	// Fire error event
	alib.events.listen(xhr, "error", function(evt){
		alib.events.triggerEvent(request, "error");
	});

	xhr.send(urlPath, method, data);
}

/**
 * Set what kind of data is being returned
 *
 * @param {string} type Can be "xml", "json", "script", "text", or "html"
 */
netric.BackendRequest.prototype.setReturnType = function(type)
{
	this.returnType_ = type;
}

/**
 * Sets whether or not this request will be made asynchronously
 *
 * Warning: if set to false the UI will hang until the request completes which is annoying
 *
 * @param {bool} asyc If true then set request to async
 */
netric.BackendRequest.prototype.setAsync = function(async)
{
	this.netXhr_.setAsync(async);
}

/**
 * Sets the number of seconds before timeout
 *
 * @param {int} seconds Number of seconds
 */
netric.BackendRequest.prototype.setTimeoutInterval = function(seconds) {
	this.netXhr_.setTimeoutInterval(seconds);
}

/**
 * Abort the request
 */
netric.BackendRequest.prototype.abort = function() {
	if (this.netXhr_)
		this.netXhr_.abort();
}

/**
 * Check if a request is in progress
 *
 * @return bool True if a request is in progress
 */
netric.BackendRequest.prototype.isInProgress = function() {
	return this.netXhr_.isInProgress();
}

/**
 * Get response text from xhr object
 */
netric.BackendRequest.prototype.getResponseText = function() {
	return this.netXhr_.getResponseText();
}

/**
 * Get response text from xhr object
 */
netric.BackendRequest.prototype.getResponseXML = function() {
	return this.netXhr_.getResponseXML();
}

/**
 * Get the parsed response
 */
netric.BackendRequest.prototype.getResponse = function() {
	return this.netXhr_.getResponse();
}