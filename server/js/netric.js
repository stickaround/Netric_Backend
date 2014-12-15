
/**
* @fileOverview Base namespace for netric
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/

alib.declare("netric");

/**
 * @define {boolean} Overridden to true by the compiler when --closure_pass
 *     or --mark_as_compiled is specified.
 */
var COMPILED = false;

/**
 * The root namespace for all netric code
 */
var netric = netric || {};

/**
 * Set version
 *
 * @public
 * @type {string}
 */
netric.version = "2.0.1";

/**
 * Connection status used to indicate if we are able to query the server
 *
 * Example"
 * <code>
 *	if (netric.online)
 *		server.getData();
 *	else
 * 		localStore.getData();
 * </code>
 *
 * @public
 * @var {bool}
 */
netric.online = false;

/**
 * Private reference to initialized applicaiton
 *
 * This will be set in netric.Application.load and should be used
 * with caution making sure all supporting code is called after the
 * main applicaiton has been initialized.
 *
 * @private
 * @var {netric.Application}
 */
 netric.application_ = null;

 /**
  * Get account base uri for building links
  * 
  * We need to do this because accounts are represented with
  * third level domains, like aereus.netric.com, where 'aereus'
  * is the name of the account.
  * 
  * @public
  * @return {string} URI
  */
netric.getBaseUri = function()
{
	var uri = window.location.protocol+'//'+window.location.hostname+(window.location.port 
		? ':' + window.location.port
		: '');
	return uri;
}

/**
 * Get initailized application
 *
 * @throws {Exception} If application has not yet been loaded
 * @return {netric.Application|bool}
 */
netric.getApplication = function() {
	if (this.application_ === null) {
		throw new Error("An instance of netric.Application has not yet been loaded.");
	}

	return this.application_;
}
/**
* @fileOverview Base entity may be extended
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.entity.Entity");

alib.require("netric");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Entity represents a netric object
 *
 * @constructor
 * @param {string} objType Name of object type
 */
netric.entity.Entity = function(objType, sObjId) {
}
/**
* @fileOverview Entity loader / identity mapper
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.entity.loader");

alib.require("netric");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Global entity loader namespace
 */
netric.entity.loader = netric.entity.loader || {};

/**
 * Array of already loaded entities
 *
 * @private
 * @var {Array}
 */
netric.entity.loader.entities_ = new Array();

/**
 * Entity represents a netric object
 *
 * @constructor
 * @param {string} objType Name of object type
 */
netric.entity.loader = function(objType, sObjId) {
}
/**
* @fileOverview Modules are sub-applications within the application framework
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.module.Module");

alib.require("netric");

/**
 * Make sure module namespace is initialized
 */
netric.module = netric.module || {};

/**
 * Application module instance
 *
 * @param {Object} opt_data Optional data for loading the module
 */
netric.module.Module = function(opt_data) {
	var data = opt_data || new Object();

	/**
	 * Unique name for this module
	 * 
	 * @public
	 * @type {string}
	 */
	this.name = data.name || "";

	/**
	 * Human readable title
	 * 
	 * @public
	 * @type {string}
	 */
	this.title = data.title || "";
}

/**
 * Static function used to load the module
 *
 * @param {function} opt_cbFunction Optional callback function once module is loaded
 */
netric.module.Module.load = function(opt_cbFunction) {
	// TODO: load module definition
}

/**
 * Run the loaded module
 *
 * @param {DOMElement} domCon Container to render module into
 */
netric.module.Module.prototype.run = function(domCon) {
	// TODO: render module into domCon
}
/**
* @fileOverview Module loader
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.module.loader");

alib.require("netric");
alib.require("netric.module.Module")

/**
 * Make sure module namespace is initialized
 */
netric.module = netric.module || {};

/**
 * Global module loader
 *
 * @param {netric.Application} application Application instance
 */
netric.module.loader = netric.module.loader || {};

/**
 * Loaded applications
 *
 * @private
 * @param {Array}
 */
netric.module.loader.loadedModules_ = new Array();

/**
 * Static function used to load the module
 *
 * @param {string} moduleName The name of the module to load
 * @param {function} cbLoaded Callback function once module is loaded
 */
netric.module.loader.get = function(moduleName, cbLoaded)
{
	// Return (or callback callback) cached module if already loaded
	if (this.loadedModules_[moduleName]) {
		
		if (cbLoaded) {
			cbLoaded(this.loadedModules_[moduleName]);
		}

		return this.loadedModules_[moduleName];
	}

	var request = new netric.BackendRequest();

	if (cbLoaded) {
		alib.events.listen(request, "load", function(evt) {
			var module = netric.module.loader.createModuleFromData(this.getResponse());
			cbLoaded(module);
		});
	} else {
		// Set request to be synchronous if no callback is set	
		request.setAsync(false);
	}

	request.send("svr/Module/get", "GET", {name:moduleName});

	// If no callback then construct netric.module.Module from request date (synchronous)
	if (!cbLoaded) {
		return this.createModuleFromData(request.getResponse());
	}
}

/** 
 * Map data to an module object
 *
 * @param {Object} data The data to create an module from
 */
netric.module.loader.createModuleFromData = function(data) {
	
	var module = new netric.module.Module(data);

	// Make sure the name was set to something other than ""
	if (module.name.length) {
		this.loadedModules_[module.name] = module;		
	}

	return module;
}

/** 
 * Preload/cache modules from data
 *
 * Use data to preload or cache modules by name
 *
 * @param {Object[]} modulesData
 */
netric.module.loader.preloadFromData = function(modulesData) {
	for (var i in modulesData) {
		this.createModuleFromData(modulesData[i]);
	}
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function notUsed()
{
    // This function is not used
}
/**
* @fileOverview Server settings object
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/

alib.declare("netric.server");

alib.require("netric");

/**
 * Create global namespace for server settings
 */
netric.server = netric.server || {};

/**
 * Server host
 * 
 * If = "" then assume server is hosted from the same origin
 * as the client, as in from the web server.
 *
 * If this is set, then make sure the auth token has been
 * negotiated and set.
 *
 * @public
 * @var {string}
 */
netric.server.host = "";
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
alib.declare("subfunction");

function subfunction()
{
    return "This is test from the subfunction!";
}
/**
* @fileOverview Base DataMapper to be used throughout netric for loading server data into objects
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.AbstractDataMapper");

alib.require("netric");

/**
 * Abstract base datamapper used for all datamappers throughout netric
 */
netric.AbstractDataMapper = function() {
	// All datamappers should first try to load locally
	//	If it exists then start an update sync if we are connected and one is not running
	//  If it does not exist, then try to connect
}

/**
 * Get data from a local or remote store
 *
 * Example:
 * <code>
 *	dm.get("entity/email_message/100001");
 *	dm.get("objdefs/email_message");
 * </code>
 *
 * @private
 * @param {string} sourcePath The unique path of the data to get
 */
netric.AbstractDataMapper.prototype.open = function(sourcePath) {
}

/**
 * Save data
 * 
 * Example:
 * <code>
 *	var data = {amount:"100", name: "Text Name"};
 *	dm.save("entity/customer/100", data);
 * </code>
 *
 * @private
 * @param {string} sourcePath The unique path of the data to get
 */
netric.AbstractDataMapper.prototype.save = function(sourcePath, data) {

}

/**
 * Query a list of data
 *
 * @private
 * @param {string} sourcePath The path to the list to query
 * @param {int} offset What page to start on
 * @param {int} limit The maximum number of items to return
 * @param {Object} conditions QueryDSL(?) conditions object
 */
netric.AbstractDataMapper.prototype.query = function(sourcePath, offset, limit, conditions) {

}
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
*	var ret = request.send("/controller/Object/getDefinition", "POST", {obj_type:this.objType});
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
/**
 * @fileoverview Base DataMapper for loading accounts
 */
alib.declare("netric.account.AbstractDataMapper");

alib.require("netric");

/**
 * Make sure account namespace is initialized
 */
netric.account = netric.account || {};

netric.account.AbstractDataMapper = function() 
{
	
}
/**
* @fileOverview Object represents the netric account object
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.account.Account");
alib.require("netric");
alib.require("netric.module.loader");

/**
 * Make sure account namespace is initialized
 */
netric.account = netric.account || {};

/**
 * Account instance
 *
 * @param {Object} opt_data Optional data used to initialize the account
 */
netric.account.Account = function(opt_data)
{
	// Initialize empty object if opt_data was not set
	var initData = opt_data || new Object();

	/**
	 * Account ID
	 *
	 * @public
	 * @type {string}
	 */
	this.id = initData.id || "";

	/**
	 * Unique account name
	 *
	 * @public
	 * @type {string}
	 */
	this.name = initData.name || "";

	/**
	 * Company name
	 * 
	 * @public
	 * @type {string}
	 */
	this.companyName = initData.companyName || "";

	/**
	 * If modules have been pre-loaded in the application data then set
	 */
	if (initData.modules)
		netric.module.loader.preloadFromData(initData.modules);
}
/**
 * @fileoverview DataMapper for loading accounts from the local store
 */
alib.declare("netric.account.DataMapperLocal");

alib.require("netric");

/**
 * Make sure account namespace is initialized
 */
netric.account = netric.account || {};

/**
 * Local DataMapper constructor
 *
 * @constructor
 */
netric.account.DataMapperLocal = function() {
	
}

/**
 * Get account from the data store
 *
 * @return {netric.account.Account|bool} Account on sucess, false if not found
 */
netric.account.DataMapperLocal.prototype.load = function() {

}

/**
 * Save account to the data store
 *
 * @param {netric.account.Account} acct The account to save data for
 * @reutrn {bool} True on success, false on failure
 */
netric.account.DataMapperLocal.prototype.save = function(acct) {
	//  TODO: save the actt
}
/**
 * @fileoverview DataMapper for loading accounts from the server
 */
alib.declare("netric.account.DataMapperServer");
alib.require("netric");
/**
 * Make sure account namespace is initialized
 */
netric.account = netric.account || {};

/**
 * Server DataMapper constructor
 *
 * @constructor
 */
netric.account.DataMapperServer = function() {
	
}

/**
 * Get account from the data store
 *
 * @return {netric.account.Account|bool} Account on sucess, false if not found
 */
netric.account.DataMapperServer.prototype.load = function(cbLoadedFunction) {

}

/**
 * Save account to the data store
 *
 * @param {netric.account.Account} acct The account to save data for
 * @reutrn {bool} True on success, false on failure
 */
netric.account.DataMapperServer.prototype.save = function(cbSavedFunction) {
	//  TODO: save the actt
}
/**
* @fileOverview Account loader
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.account.loader");

alib.require("netric");

alib.require("netric.account.Account");
alib.require("netric.BackendRequest");

/**
 * Make sure account namespace is initialized
 */
netric.account = netric.account || {};

/**
 * Global module loader
 *
 * @param {netric.Application} application Application instance
 */
netric.account.loader = netric.account.loader || {};

/**
 * Keep a reference to last loaded application to reduce requests
 *
 * @private
 * @param {Array}
 */
netric.account.loader.accountCache_ = null;

/**
 * Static function used to load the module
 *
 * If no callback is set then this function will try to return the account
 * from cache. If it has not yet been loaded then it will force a non-async
 * request which will HANG THE UI so it should only be used as a last resort.
 *
 * @param {function} cbLoaded Callback function once account is loaded
 * @return {netric.account.Account|void} If no callback is provded then force a return
 */
netric.account.loader.get = function(cbLoaded) {
	
	// Return (or callback callback) cached account if already loaded
	if (this.accountCache_ != null) {
		
		if (cbLoaded) {
			cbLoaded(this.accountCache_);
		}

		return this.accountCache_;
	}

	var request = new netric.BackendRequest();

	if (cbLoaded) {
		alib.events.listen(request, "load", function(evt) {
			var account = netric.account.loader.createAccountFromData(this.getResponse());
			cbLoaded(account);
		});
	} else {
		// Set request to be synchronous if no callback is set	
		request.setAsync(false);
	}

	request.send("svr/Account/get");

	// If no callback then construct netric.account.Account from request date (synchronous)
	if (!cbLoaded) {
		return this.createAccountFromData(request.getResponse());
	}
}

/**
 * Map data to an account object
 *
 * @param {Object} data The data to create an account from
 */
netric.account.loader.createAccountFromData = function(data) {

	// Construct account and initialize with data	
	var account = new netric.account.Account(data);
	
	// Cache it for future requests
	this.accountCache_ = account;

	return this.accountCache_;
}
/**
* @fileOverview Load instance of netric application
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2011 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.Application");

alib.require("netric");
alib.require("netric.account.loader");

/**
 * Application instance
 *
 * @param {netric.Account} account The current account netric is running under
 */
netric.Application = function(account) {
	/**
	 * Handle to the current account
	 *
	 * @public
	 * @var {netric.Application.Account}
	 */
	this.account = account;
}

/**
 * Static function used to load the application
 *
 * @param {function} cbFunction Callback function once app is loaded
 */
netric.Application.load = function(cbFunction) {

	netric.account.loader.get(function(acct){

		// Create appliation instance for loaded account
		var app = new netric.Application(acct);

		// Callback passing initialized application
		if (cbFunction) {
			cbFunction(app);	
		}
	});
}

/**
 * Run the loaded application
 *
 * @param {DOMElement} domCon Container to render applicaiton into
 */
netric.Application.prototype.run = function(domCon) {
	// TODO: render application into domCon
}