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
 * @fileoverview controller namespace
 */

alib.declare("netric.controller");

alib.require("netric");

/**
 * The MVC namespace where all MVC core functionality will liveß
 */
netric.controller = netric.controller || {};
/**
 * @fileOverview Define entity definition fields
 *
 * This class is a client side mirror of /lib/EntityDefinition/Field on the server side
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2013 Aereus Corporation. All rights reserved.
 */
alib.declare("netric.entity.definition.Field");

alib.require("netric");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Make sure entity definition namespace is initialized
 */
netric.entity.definition = netric.entity.definition || {};

/**
 * Creates an instance of netric.entity.definition.Field
 *
 * @param {Object} opt_data The definition data
 * @constructor
 */
netric.entity.definition.Field = function(opt_data) {

	var data = opt_data || new Object();

	/**
	 * Unique id if the field was loaded from a database
	 *
	 * @public
	 * @type {string}
	 */
	this.id = data.id || "";

	/**
	 * Field name (REQUIRED)
	 *
	 * No spaces or special characters allowed. Only alphanum up to 32 characters in length.
	 *
	 * @public
	 * @type {string}
	 */
	this.name = data.name || "";

	/**
	 * Human readable title
	 *
	 * If not set then $this->name will be used:
	 *
	 * @public
	 * @type {string}
	 */
	this.title = data.title || "";

	/**
	 * The type of field (REQUIRED)
	 *
	 * @public
	 * @type {string}
	 */
	this.type = data.type || "";

	/**
	 * The subtype
	 *
	 * @public
	 * @type {string}
	 */
	this.subtype = data.subtype || "";

	/**
	 * Optional mask for formatting value
	 *
	 * @public
	 * @type {string}
	 */
	this.mask = data.mask || "";

	/**
	 * Is this a required field?
	 *
	 * @public
	 * @var bool
	 */
	this.required = data.required || false;

	/**
	 * Is this a system defined field
	 *
	 * Only user fields can be deleted or edited
	 *
	 * @public
	 * @var bool
	 */
	this.system = data.system || false;

	/**
	 * If read only the user cannot set this value
	 *
	 * @public
	 * @var bool
	 */
	this.readonly = data.readonly || false;

	/**
	 * This field value must be unique across all objects
	 *
	 * @public
	 * @var bool
	 */
	this.unique = data.unique || false;

	/**
	 * Optional use_when condition will only display field when condition is met
	 *
	 * This is used for things like custom fields for posts where each feed will have special
	 * custom fields on a global object - posts.
	 *
	 * @public
	 * @type {string}
	 */
	this.useWhen = data.use_when || "";

	/**
	 * Default value to use with this field
	 *
	 * @public
	 * @var {array('on', 'value')}
	 */
	this.defaultVal = data.default_val || null;

	/**
	 * Optional values
	 *
	 * If an associative array then the id is the key, otherwise the value is used
	 *
	 * @public
	 * @var {Array}
	 */
	this.optionalValues = data.optional_values || null;

	/**
	 * Sometimes we need to automatically create foreign reference
	 *
	 * @public
	 * @type {bool}
	 */
	this.autocreate = data.autocreate || false;

	/**
	 * If autocreate then the base is used to define where to put the new referenced object
	 *
	 * @public
	 * @type {string}
	 */
	this.autocreatebase = data.autocreatebase || "";

	/**
	 * If autocreate then which field should we use for the name of the new object
	 *
	 * @public
	 * @type {string}
	 */
	this.autocreatename = data.autocreatename || "";

	/** 
	 * Add static types to a variable in 'this'
	 *
	 * @public
	 * @type {Object}
	 */
	this.types = netric.entity.definition.Field.types;
}

/**
 * Static definition of all field types
 */
netric.entity.definition.Field.types = {
	fkey : "fkey",
	fkeyMulti : "fkey_multi",
	object : "object",
	objectMulti : "object_multi",
	string : "string",
	bool : "bool",
}

/**
 * Get the default value for this vield
 *
 * @param {string} on The event to set default value on - default to null
 * @return {string}
 */
netric.entity.definition.Field.prototype.getDefault = function(on)
{
	if (!this.defaultVal)
		return "";

	if (this.defaultVal.on == on)
	{
		if (this.defaultVal.value)
			return this.defaultVal.value;
	}

	return "";
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

	request.send("svr/module/get", "GET", {name:moduleName});

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
/**
 * @fileoverview mvc namespace
 */

alib.declare("netric.mvc");

alib.require("netric");

/**
 * The MVC namespace where all MVC core functionality will liveß
 */
netric.mvc = netric.mvc || {};
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
 * @fileoverview View templates for the application in full desktop mode
 */
 alib.declare("netric.template.application.large");

 /**
  * Make sure tha namespace exists for template
  */

 /**
 * Make sure module namespace is initialized
 */
netric.template = netric.template || {};
netric.template.application = netric.template.application || {};

/**
 * Large and medium templates will use this same template
 *
 * @param {Object} data Used for rendering the template
 * @return {string|netric.mvc.ViewTemplate} Either returns a string or a ViewTemplate object
 */
netric.template.application.large = function(data) {
	
	/*
	<!-- application header -->
	<div id='appheader' class='header'>
		<!-- right actions -->
		<div id='headerActions'>
			<table border='0' cellpadding="0" cellspacing="0">
			<tr valign="middle">			
				<!-- notifications -->
				<td style='padding-right:10px'><div id='divAntNotifications'></div></td>

				<!-- chat -->
				<td style='padding-right:10px'><div id='divAntChat'></div></td>

				<!-- new object dropdown -->
				<td style='padding-right:10px'><div id='divNewObject'></div></td>

				<!-- settings -->
				<td style='padding-right:10px'>
					<a href="javascript:void(0);" class="headerLink" 
						onclick="document.location.hash = 'settings';" 
						title='Click to view system settings'>
							<img src='/images/icons/main_settings_24.png' />
					</a>
				</td>

				<!-- help -->
				<td style='padding-right:10px' id='mainHelpLink'>
					<a href='javascript:void(0);' title='Click to get help'><img src='/images/icons/help_24_gs.png' /></a>
				 </td>
				<td id='mainProfileLink'>
					<a href='javascript:void(0);' title='Logged in as <?php echo $USER->fullName; ?>'><img src="/files/userimages/current/0/24" style='height:24px;' /></a>
				</td>
			</tr>
			</table>
		</div>

		<!-- logo -->
		<div class='headerLogo'>
		<?php
			$header_image = $ANT->settingsGet("general/header_image");
			if ($header_image)
			{
				echo "<img src='/antfs/images/$header_image' />";
			}
			else
			{
				echo "<img src='/images/netric-logo-32.png' />";

			}
		?>
		</div> 
		<!-- end: logo -->
		
		<!-- middle search -->
		<div id='headerSearch'><div id='divAntSearch'></div></div>

		<div style="clear:both;"></div>
	</div>
	<!-- end: application header -->

	<!-- application tabs -->
	<div id='appnav'>
		<div class='topNavbarHr'></div>
		<div class='topNavbarBG' id='apptabs'></div>
		<div class='topNavbarShadow'></div>
	</div>
	<!-- end: application tabs -->

	<!-- application body - where the applications load -->
	<div id='appbody'>
	</div>
	<!-- end: application body -->

	<!-- welcome dialog -->
	<div id='tour-welcome' style='display:none;'>
		<div data-tour='apps/netric' data-tour-type='dialog'></div>
	</div>
	<!-- end: welcome dialog -->
	*/

	var vt = new netric.mvc.ViewTemplate();

	var header = alib.dom.createElement("div", null, null, {id:"app-header-large"});
	header.innerHTML = "Desktop Header";
	vt.addElement(header);
	vt.header = header; // Add for later reference

	vt.bodyCon = alib.dom.createElement("p");
	vt.bodyCon.innerHTML = "Put the app body here!";
	vt.addElement(vt.bodyCon);

	return vt;
}

/** 
 * @fileoverview View templates for the application in full desktop mode
 */
 alib.declare("netric.template.application.small");

 /**
  * Make sure tha namespace exists for template
  */

 /**
 * Make sure module namespace is initialized
 */
netric.template = netric.template || {};
netric.template.appication = netric.template.application || {};


/**
 * Large and medium views will use this same template
 *
 * @param {Object} data Used for rendering the template
 */
netric.template.application.small = function(data) {
	return "<div id='main'><div id='loading'>Loading...</div></div><div id='footerTabs'></div>";
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
/**
 * @fileoverview Device information class
 * 
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */

alib.declare("netric.Device");

alib.require("netric");

// Information about the current device
netric.Device = function() {
	// TODO: try to determine the size of the viewport of this device
}

/**
 * Static device sizes
 * 
 * @const
 * @public
 */
netric.Device.sizes = {
	// Phones and small devices
	small : 1,
	// Tablets
	medium : 3,
	// Desktops
	large : 5
};

/**
 * The size of the current device once loaded
 *
 * @type {netric.Device.sizes}
 */
netric.Device.prototype.size = netric.Device.sizes.large;
/**
 * @fileOverview Object represents the netric account user
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
alib.declare("netric.User");
alib.require("netric");

/**
 * User instance
 *
 * @param {Object} opt_data Optional data used to initialize the user
 */
netric.User = function(opt_data)
{
	// Initialize empty object if opt_data was not set
	var initData = opt_data || new Object();

	/**
	 * Unique id for this user
	 * 
	 * @public
	 * @type {string}
	 */
	this.id = initData.id || "";

	/**
	 * Unique username for this user
	 * 
	 * @public
	 * @type {string}
	 */
	this.name = initData.name || "";

	/**
	 * Full name is usually combiation of first and last name
	 * 
	 * @public
	 * @type {string}
	 */
	this.fullName = initData.fullName || "";
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
alib.require("netric.User");

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
	 * Organization name
	 * 
	 * @public
	 * @type {string}
	 */
	this.orgName = initData.orgName || "";

	/**
	 * Currently authenticated user
	 * 
	 * @public
	 * @type {netric.User}
	 */
	this.user = (initData.user) ? new netric.User(initData.user) : null;

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

	request.send("svr/account/get");

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
 * @fileOverview Handle defintion of entities.
 *
 * This class is a client side mirror of /lib/EntityDefinition
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
alib.declare("netric.entity.Definition");

alib.require("netric.entity.definition.Field");

alib.require("netric");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Creates an instance of EntityDefinition
 *
 * @constructor
 * @param {Object} opt_data The definition data
 */
netric.entity.Definition = function(opt_data) {

	var data = opt_data || new Object();

	/**
	 * The object type for this definition
	 *
	 * @public
	 * @type {string}
	 */
	this.objType = data.obj_type || "";;

	/**
	 * The object type title
	 *
	 * @public
	 * @type {string}
	 */
	this.title = data.title || "";

	/**
	 * Recurrence rules
	 *
	 * @public
	 * @type {string}
	 */
	this.recurRules = data.recur_rules || null;

	/**
	 * Unique id of this object type
	 *
	 * @public
	 * @type {string}
	 */
	this.id = data.id || "";

	/**
	 * The current schema revision
	 *
	 * @public
	 * @type {int}
	 */
	this.revision = data.revision || "";

	/**
	 * Determine if this object type is private
	 *
	 * @public
	 * @type {bool}
	 */
	this.isPrivate = data.is_private || false;

	/**
	 * If object is heirarchial then this is the field that will store a reference to the parent
	 *
	 * @public
	 * @type {string}
	 */
	this.parentField = data.parent_field || "";

	/**
	 * Default field used for printing the name/title of objects of this type
	 *
	 * @public
	 * @type {string}
	 */
	this.listTitle = data.list_title || "";

	/**
	 * The base icon name used for this object.
	 *
	 * This may be over-ridden by individual objects for more dynamic icons, but this serves
	 * as the base in case the individual object did not yet define an icon.
	 *
	 * @public
	 * @type {string}
	 */
	this.icon = data.icon || "";

	/**
	 * Browser mode for the current user
	 *
	 * @public
	 * @type {string}
	 */
	this.browserMode = data.browser_mode || "";

	/**
	 * Is this a system level object
	 *
	 * @public
	 * @type {bool}
	 */
	this.system = data.system || "";;

	/**
	 * Fields associated with this object type
	 *
	 * For definition see EntityDefinition_Field::toArray on backend
	 *
	 * @private
	 * @type {netric.entity.definition.Field[]}
	 */
	this.fields = new Array();

	/**
	 * Array of object views
	 *
	 * @private
	 * @type {AntObjectBrowserView[]}
	 */
	this.views = new Array();

	/**
	 * Browser list blank state content
	 *
	 * This is used when there are no objects
	 *
	 * @private
	 * @type {string}
	 */
	this.browserBlankContent = data.browser_blank_content || "";;

	/*
	 * Initialize fields if set in the data object
	 */
	if (data.fields) {
		for (var fname in data.fields) {
			var field = new netric.entity.definition.Field(data.fields[fname]);
			this.fields.push(field);
		}
	}

	/*
	 * Initialize views for this object definition
	 */
	if (data.views) {
		for (var i in data.views) {
			var view = new AntObjectBrowserView();
			view.fromData(data.views[i]);
			this.views.push(view);
		}
	}

}

/**
 * Get a field by name
 *
 * @public
 * @param {Object} data Initialize values of this defintion based on data
 */
netric.entity.Definition.prototype.getField = function(fname) {
	for (var i in this.fields)
	{
		if (this.fields[i].name == fname)
			return this.fields[i];
	}
	return false;
}

/**
 * Get fields
 *
 * @public
 * @return {netric.entity.Definition.Field[]}
 */
netric.entity.Definition.prototype.getFields = function() {
	return this.fields;
}

/**
 * Get views
 *
 * @public
 * @return {AntObjectBrowserView[]}
 */
netric.entity.Definition.prototype.getViews = function() {
	return this.views;
}

/**
 * Get browser blank state content
 *
 * @public
 * @return {string}
 */
netric.entity.Definition.prototype.getBrowserBlankContent = function() {
	return this.browserBlankContent;
}

/**
 * @fileOverview Base entity may be extended
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
alib.declare("netric.entity.Entity");

alib.require("netric");
alib.require("netric.entity.Definition");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Entity represents a netric object
 *
 * @constructor
 * @param {netric.entity.Definition} entityDef Required definition of this entity
 * @param {Object} opt_data Optional data to load into this object
 */
netric.entity.Entity = function(entityDef, opt_data) {

	/** 
	 * Unique id of this object entity
	 *
	 * @public
	 * @type {string}
	 */
	this.id = "";

	/** 
	 * The object type of this entity
	 *
	 * @public
	 * @type {string}
	 */
	this.objType = "";

	/**
	 * Entity definition
	 *
	 * @public
	 * @type {netric.entity.Definition}
	 */
	this.def = entityDef;

	/**
	 * Flag to indicate fieldValues_ have changed for this entity
	 *
	 * @private
	 * @type {bool}
	 */
	this.dirty_ = false;

	/**
	 * Field values
	 * 
	 * @private
	 * @type {Object}
	 */
	this.fieldValues_ = new Object();

	/**
	 * Security
	 * 
	 * @public
	 * @type {Object}
	 */
	this.security = {
		view : true,
		edit : true,
		del : true,
		childObject : new Array()
	};

	// If data has been passed then load it into this entity
	if (opt_data) {
		this.loadData(opt_data);
	}
}

/**
 * Load data from a data object in array form
 * 
 * If we are loading in array form that means that properties are not camel case
 * 
 * @param {Object} data
 */
netric.entity.Entity.prototype.loadData = function (data) {
	
	// Data is a required param and we should fail if called without it
	if (!data) {
		throw "'data' is a required param to loadData into an entity";
	}

	// Make sure that the data passed is valid data
	if (!data.id || !data.obj_type) {
		var err = "Data passed is not a valid entity";
		console.log(err + JSON.strigify(data));
		throw err;
	}

	// First set common public properties
	this.id = data.id.toString();
	this.objType = data.obj_type;

	// Now set all the values for this entity
}

/**
 * Set the value of a field of this entity
 *
 * @param {string} name The name of the field to set
 * @param {mixed} value The value to set the field to
 * @param {string} opt_valueName The label if setting an fkey/object value
 */
netric.entity.Entity.prototype.setValue = function(name, value, opt_valueName) {
    
    // Can't set a field without a name
    if(typeof name == "undefined")
        return;

	var valueName = valueName || null;

    var field = this.def.getField(name);
	if (!field)
		return;

	// Check if this is a multi-field
	if (field.type == field.types.fkeyMulti || field.type == field.types.objectMulti) {
		if (value instanceof Array) {
			for (var j in value) {
				this.setMultiValue(name, value[j]);
			}
		} else {
			this.setMultiValue(name, value, valueName);
		}

		return true;
	}

	// Handle bool conversion
	if (field.type == field.types.bool) {
		switch (value)
		{
		case 1:
		case 't':
		case 'true':
			value = true;
			break;
		case 0:
		case 'f':
		case 'false':
			value = false;
			break;
		}
	}
    
    // Referenced object fields cannot be updated
    if (name.indexOf(".")!=-1) {
        return;
    }

    // A value of this entity is about to change
    this.dirty_ = true;

    // Set the value and optional valueName label for foreign keys
    this.fieldValues_[name] = {
    	value: value,
    	valueName: (valueName) ? valueName : null
    }

    // Trigger onchange event to alert any observers that this value has changed
	alib.events.triggerEvent(this, "fieldchange", {fieldName: name, value:value, valueName:valueName});
    
}

/**
 * Get the value for an object entity field
 * 
 * @public
 * @param {string} name The unique name of the field to get the value for
 */
netric.entity.Entity.prototype.getValue = function(name) {
    if (!name)
        return null;

    // Get value from fieldValue
    if (this.fieldValues_[name]) {
    	return this.fieldValues_[name].value;
    }  
    
    return null;
}

/*************************************************************************
*    Function:    getValueName
*
*    Purpose:    If exists, get the value name (label) of a referenced field
*                Typically, get name from id in an fkey
**************************************************************************/
/**
 * Get the name/lable of a key value
 * 
 * @param {string} name The name of the field
 * @param {val} opt_val If querying *_multi type values the get the label for a specifc key
 * @reutrn {string} the textual representation of the key value
 */
netric.entity.Entity.prototype.getValueName = function(name, val) {
	// Get value from fieldValue
    if (this.fieldValues_[name]) {
    	if (opt_val && this.fieldValues_[name].valueName instanceof Array) {
    		for (var i in this.fieldValues_[name].valueName) {
    			if (this.fieldValues_[name].valueName[i].key == name) {
    				return this.fieldValues_[name].valueName[i].value;
    			}
    		}
    	} else {
    		return this.fieldValues_[name].valueName;    		
    	}
    }
	/*
    var field = this.getFieldByName(name);
    if (field && field.type == "alias")
    {
        if (!val)
            var val = this.getValue(name);
        return this.getValue(val); // Get aliased value
    }

    if (field.type == "object" || field.type == "fkey" || field.type == "object_multi" || field.type == "fkey_multi")
    {
        for (var i = 0; i < this.values.length; i++)
        {
            if (this.values[i][0] == name)
            {
                if (val) // multival
                {
                    for (var m = 0; m < this.values[i][1].length; m++)
                    {
                        if (this.values[i][1][m] == val && this.values[i][2])
                            return this.values[i][2][m];
                    }
                }
                else
                {
                    if (this.values[i][2]!=null && this.values[i][2]!="null")
                        return this.values[i][2];
                }
            }
        }
    }
	else if (field.optional_vals.length)
	{
		for (var i = 0 ; i < field.optional_vals.length; i++)
		{
			if (field.optional_vals[i][0] == this.getValue(name))
			{
				return field.optional_vals[i][1];
			}
		}
	}
    else
    {
        return this.getValue(name);
    }
    */
    
    return "";
}

/*************************************************************************
*    Function:    getValueStr
*
*    Purpose:    If a foreign reference, then return name, otherwise
*                return the value of the field.
**************************************************************************/
netric.entity.Entity.prototype.getValueStr = function(name)
{
    var val = this.getValueName(name);
    if (!val)
        val = this.getValue(name);
    
    return val;
}

/**
 * Get the human readable name of this object
 *
 * @return {string} The name of this object based on common name fields like 'name' 'title 'subject'
 */
netric.entity.Entity.prototype.getName = function()
{
    if (this.getValue("name")) {
        return this.getValue("name");
    } else if (this.getValue("title")) {
        return this.getValue("title");
    } else if (this.getValue("subject")) {
        return this.getValue("subject");
    } else if (this.getValue("first_name") || this.getValue("last_name")) {
    	return (this.getValue("first_name")) 
    		? this.getValue("first_name") + " " + this.getValue("last_name")
    		: this.getValue("last_name");
    } else if (this.getValue("id")) {
        return this.getValue("id");
    } else {
        return "";
    }
}
/**
* @fileOverview Definition loader
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.entity.definitionLoader");

alib.require("netric");

alib.require("netric.entity.Definition");
alib.require("netric.BackendRequest");

/**
 * Make sure entity namespace is initialized
 */
netric.entity = netric.entity || {};

/**
 * Entity definition loader
 *
 * @param {netric.Application} application Application instance
 */
netric.entity.definitionLoader = netric.entity.definitionLoader || {};

/**
 * Keep a reference to loaded definitions to reduce requests
 *
 * @private
 * @param {Array}
 */
netric.entity.definitionLoader.definitions_ = new Array();

/**
 * Static function used to load an entity definition
 *
 * If no callback is set then this function will try to return the definition
 * from cache. If it has not yet been loaded then it will force a non-async
 * request which will HANG THE UI so it should only be used as a last resort.
 *
 * @param {string} objType The object type we are loading a definition for
 * @param {function} cbLoaded Callback function once definition is loaded
 * @return {netric.entity.Definition|void} If no callback is provded then force a return
 */
netric.entity.definitionLoader.get = function(objType, cbLoaded) {
	
	// Return (or callback callback) cached definition if already loaded
	if (this.definitions_[objType] != null) {
		
		if (cbLoaded) {
			cbLoaded(this.definitions_[objType]);
		}

		return this.definitions_[objType];
	}

	var request = new netric.BackendRequest();

	if (cbLoaded) {
		alib.events.listen(request, "load", function(evt) {
			var def = netric.entity.definitionLoader.createFromData(this.getResponse());
			cbLoaded(def);
		});
	} else {
		// Set request to be synchronous if no callback is set	
		request.setAsync(false);
	}

	request.send("svr/entity/getDefinition", "GET", {obj_type:objType});

	// If no callback then construct netric.entity.Definition from request date (synchronous)
	if (!cbLoaded) {
		return this.createFromData(request.getResponse());
	}
}

/**
 * Map data to an entity definition object
 *
 * @param {Object} data The data to create the definition from
 */
netric.entity.definitionLoader.createFromData = function(data) {

	// Construct definition and initialize with data	
	var def = new netric.entity.Definition(data);
	
	// Cache it for future requests
	this.definitions_[def.objType] = def;

	return this.definitions_[def.objType];
}

/**
 * Get a pre-loaded / cached object definition
 *
 * @param {string} objType The uniqy name of the object entity type
 * @return {netric.entity.Definition} Entity defintion on success, null if not cached
 */
netric.entity.definitionLoader.getCached = function(objType) {
	if (this.definitions_[objType]) {
		return this.definitions_[objType];
	}

	return null;
}
/**
* @fileOverview Entity loader / identity mapper
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.entity.loader");

alib.require("netric");
alib.require("netric.entity.definitionLoader");

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
netric.entity.loader.entities_ = new Object();

/**
 * Static function used to load the entity
 *
 * @param {string} objType The object type to load
 * @param {string} entId The unique entity to load
 * @param {function} cbLoaded Callback function once entity is loaded
 * @param {bool} force If true then force the entity to reload even if cached
 */
netric.entity.loader.get = function(objType, entId, cbLoaded, force) {
	// Return (or callback callback) cached entity if already loaded
	var ent = this.getCached(objType, entId);
	if (ent && !force) {

		if (cbLoaded) {
			cbLoaded(ent);
		}

		return ent;
	}

	/*
	 * Load the entity data
	 */
	var request = new netric.BackendRequest();

	if (cbLoaded) {
		alib.events.listen(request, "load", function(evt) {
			var entity = netric.entity.loader.createFromData(this.getResponse());
			cbLoaded(entity);
		});
	} else {
		// Set request to be synchronous if no callback is set	
		request.setAsync(false);
	}

	// Create request data
	var requestData = {
		obj_type:objType, 
		id:entId
	}

	// Add definition if it is not loaded already.
	// This will cause the backend to include a .definition property in the resp
	if (netric.entity.definitionLoader.getCached(objType) == null) {
		requestData.loadDef = 1;
	}

	request.send("svr/entity/get", "GET", requestData);

	// If no callback then construct netric.entity.Entity from request date (synchronous)
	if (!cbLoaded) {
		return this.createFromData(request.getResponse());
	}
}

/**
 * Static function used to create a new object entity
 *
 * This function may need to get the definition from the server. If it is called
 * with no opt_cbCreated it will do a non-async request which could hang the entire UI
 * until the request returns so be careful in this instance because users don't much
 * like that. Try to include the callback param as much as possible. 
 *
 * @param {string} objType The object type to load
 * @param {function} opt_cbCreated Optional callback function once entity is initialized
 */
netric.entity.loader.factory = function(objType, opt_cbCreated) {

	var entDef = netric.entity.definitionLoader.getCached(data.obj_type);

	if (opt_cbCreated) {
		netric.entity.definitionLoader.get(objType, function(def) {
			var ent = new netric.entity.Entity(def);
			opt_cbCreated(ent);
		});
	} else {
		// Force a syncronous request with no second param (callback)
		var def = netric.entity.definitionLoader.get(objType);
		return new netric.entity.Entity(def);
	}
}

/** 
 * Map data to an entity object
 *
 * @param {Object} data The data to create an entity from
 */
netric.entity.loader.createFromData = function(data) {

	if (typeof data === 'undefined') {
		throw "data is a required param to create an object";
	}

	// Get cached object definition
	var entDef = netric.entity.definitionLoader.getCached(data.obj_type);
	// If cached definition is not found then the data object should include a .definition prop
	if (entDef == null && data.definition) {
		entDef = netric.entity.definitionLoader.createFromData(data.definition);
	}

	// If we don't have a definition to work with we should throw an error
	if (entDef == null) {
		throw "Could not load a definition for " + data.obj_type;
	}
	
	// Check to see if we have previously already loaded this object
	var ent = this.getCached(entDef.objType, data.id);
	if (ent != null) {
		ent.loadData(data);
	} else {
		ent = new netric.entity.Entity(entDef, data);

		// Make sure the name was set to something other than "" and place it in cache
		if (ent.id && ent.objType) {
			this.cacheEntity(ent);	
		}
	}
	
	return ent;
}

/**
 * Put an entity in the local cache for future quick loading
 *
 * @param {netric.entity.Entity} ent The entity to store
 */
netric.entity.loader.cacheEntity = function(ent) {

	if (!this.entities_[ent.objType]) {
		this.entities_[ent.objType] = new Object();	
	}

	this.entities_[ent.objType][ent.id] = ent;

}

/** 
 * Get an object entity from cache
 *
 * @param {string} objType The object type to load
 * @param {string} entId The unique entity to load
 * @return {netric.entity.Entity} or null if not cached
 */
netric.entity.loader.getCached = function(objType, entId) {

	// Check to see if the entity is already loaded and return it
	if (this.entities_[objType]) {
		if (this.entities_[objType][entId]) {
			return this.entities_[objType][entId];
		}
	}

	return null;
}
/**
 * @fileoverview Base controller for MVC
 *
 * TODO: this class is a concept and a work in progress
 */

alib.declare("netric.mvc.Controller");

alib.require("netric");
alib.require("netric.mvc");

/**
 * Make sure module namespace is initialized
 */
netric.mvc = netric.mvc || {};

/**
 * Controller constructor
 *
 * @param {string} name The name for this controller often used for unique routes
 * @param {netric.mvc.Controller} parentController Optional parent controller
 */
netric.mvc.Controller = function(name, parentController) {
	/** 
	 * Represents the full path of this contoller based on 'name'
	 *
	 * @type {string}
	 */
	this.path = "";

	/**
	 * The current action/view that is actively displayed
	 * 
	 * @type {string}
	 */
	this.currViewName = "";

	/**
	 * Default action
	 *
	 * @type {string}
	 */
	this.defaultAction = "main";
	
	/**
	 * Array of views associated with this controller and its actions
	 * 
	 * @type {netric.mvc.view[]}
	 */
	this.views = new Array();

	/**
	 * Only one action is visible at a time
	 *
	 * @type {bool}
	 */
	this.pageView = false;

	/**
	 * If a child controller is shown, then hide this view
	 *
	 * pageViewSingle means that if a child view shows, this view is hidden
	 *
	 * @type {bool}
	 */
	this.pageViewSingle = false;

	// Automatically convert any predefined actions for this contoller into views
	this.init();
}

/**
 * Render an action into the dom
 *
 * @param {string} actionName The name of the action which maps to 'name'Action function
 * @param {Object} params Optional object of params set to be passed to action
 * @param {string} postFix Optional trailing path to be loaded after this
 */
netric.mvc.Controller.prototype.loadAction = function(actionName, params, postFix) {
	
	// Use the default action if none has been set
	if (!actionName && this.defaultAction)
		actionName = this.defaultAction;

	var bFound = false;

	if (!postFix)
		var postFix = "";

	// Loop through child views, hide all but the action to be rendered
	for (var i = 0; i < this.views.length; i++)
	{
		// Find the view by name
		if (this.views[i].name == actionName)
		{
			//this.views[i].variable = params;

			// Flag that we found the view
			bFound = true;

			/*
			 * If we are a child view and the views are set to single pages only
			 * the last view in the list should be viewable and the parent will be hidden
			 */
			if (this.pageViewSingle && this.views[i].parentView)
				this.views[i].parentView.hide();

			if (postFix!="") // This is not the top level view - there are children to display in the path
			{
				/*
				 * Check to see if this view has been rendered 
				 * already - we only render the first time
				 * It is possible in a scenario where a deep url is loaded
				 * like /my/path to have 'my' never shown because we jump
				 * straight to 'path' but we still need to make sure it is rendered.
				 */
				if (this.views[i].isRendered == false)
				{
					this.views[i].render();
					this.views[i].isRendered = true;
				}

				/*
				 * As mentioned above, if we are in singleView mode then 
				 * don't show views before the last in the list
				 */
				if (!this.pageViewSingle)
					this.views[i].show();

				// Continue loading the remainder of the path - the child view(s)
				this.views[i].load(postFix);
			}
			else // This is a top-level view meaning there are no children
			{
				this.views[i].show(); // This will also render if the view has not yet been rendered
				this.views[i].hideChildren();
			}

			// Call load callbacks for view
			this.views[i].triggerEvents("load");
		}
		else if (this.pageView) // Hide this view if we are in pageView because it was not selected
		{
			/*
			 * pageView is often used for tab-like behavior where you toggle 
			 * through pages/views at the same level - not affecting parent views
			 */
			this.views[i].hide();
			this.views[i].hideChildren();
		}
	}

	return bFound;
}

/**
 * Called from the constructor to automatically add actions this.prototype.*Action
 */
netric.mvc.Controller.prototype.init = function() {
	// TODO: look for any functions in 'this' that are actions and automatically
	// add a view for that function
}

/**
 * Event called when the controller is shown
 */
netric.mvc.Controller.prototype.onShow = function() {
	
}

/**
 * Event called when the controller is hidden
 */
netric.mvc.Controller.prototype.onHide = function() {
	
}

/**
* @todo: This is a port from AntViewManager and a work in progress
*
* Add a new action and view to this controller
*
* @param {string} name The unique name (in this controller) of this view
* @param {object} optionsargs Object of optional params that populates this.options
* @param {object} con Contiaining lement. If passed, then a sub-con will automatically be created. 
* 							If not passed, then pure JS is assumed though utilizing the onshow 
* 							and onhide callbacks for this view			
* @param {object} parentView An optional reference to the parent view. 
* 							This is passed when the view.addView function is called to maintain heiarchy.		 
*
AntViewManager.prototype.addAction = function(name, optionargs, con, parentView)
{
	var pView = (parentView) ? parentView : null;
	var useCon = (con) ? con : null;

	// Make sure this view is unique
	for (var i = 0; i < this.views.length; i++)
	{
		if (this.views[i].nameMatch(name))
			return this.views[i];
	}

	var view = new AntView(name, this, pView);
	view.options = optionargs;
	if (useCon)
	{
		view.conOuter = useCon;
	}
	else if (parentView)
	{
		if (parentView.conOuter)
			view.conOuter = parentView.conOuter;
	}
	if (this.isMobile)
	{
		var contentCon = document.getElementById(view.getPath()+"_con");
		if (!contentCon)
		{
			var path = view.getPath();
			var pageCon = alib.dom.createElement("div", document.getElementById("main"));
			pageCon.style.display="none";
			pageCon.style.position="absolute";
			pageCon.style.top="0px";
			pageCon.style.width="100%";
			pageCon.id = path;

			// Main header container
			var headerCon = alib.dom.createElement("div", pageCon);
			alib.dom.styleSetClass(headerCon, "header");

			// Right button container
			var rightButton = alib.dom.createElement("button", headerCon);
			alib.dom.styleSetClass(rightButton, "right");

			// Left button container
			if (view.hasback())
			{
				var leftButton = alib.dom.createElement("button", headerCon, "Back");
				alib.dom.styleSetClass(leftButton, "left arrow");
				leftButton.view = view;
				leftButton.onclick = function() { view.goup(); }
			}

			// Title container
			var title = alib.dom.createElement("h1", headerCon);

			if (typeof Ant != "undefined")
				title.innerHTML = view.getTitle();
				//title.innerHTML = Ant.account.companyName;

			// Sky Stebnicki: I believe this may be depriacted but needs to be verified
			var conAppTitle = alib.dom.createElement("div", headerCon);
			
			var contentCon = alib.dom.createElement("div", pageCon);
			contentCon.id = path+"_con";
			alib.dom.styleSetClass(contentCon, "viewBody");

			// Used by the AntApp class to set the title of the application
			view.conAppTitle = conAppTitle;
		}
		
		view.con = contentCon;
	}
	else
	{
		view.con = (view.conOuter) ? alib.dom.createElement("div", view.conOuter) : null;
		if (view.con)
			view.con.style.display = 'none';
	}

	this.views[this.views.length] = view;
	return view;
}
*/

/**
 * Resize the active view and it's children
 *
AntViewManager.prototype.resizeActiveView = function()
{
	if (this.currViewName)
	{
		var actView = this.getView(this.currViewName);
		if (actView)
			actView.resize();
	}

}
*/

/**
* Load a view by converting a path to a name
*
* @param {string} path path like my/app/name will load "my" view of this viewManager
*
AntViewManager.prototype.load = function(path)
{
	this.path = path;
	var postFix = "";
	var nextView = "";

	if (this.path.indexOf("/")!=-1)
	{
		var parts = this.path.split("/");
		this.currViewName = parts[0];
		if (parts.length > 1)
		{
			for (var i = 1; i < parts.length; i++) // Skip of first which is current view
			{
				if (postFix != "")
					postFix += "/";
				postFix += parts[i];
			}
		}
	}
	else
		this.currViewName = path;

	var variable = "";
	var parts = this.currViewName.split(":");
	if (parts.length > 1)
	{
		this.currViewName = parts[0];
		variable = parts[1];
	}

	return this.loadView(this.currViewName, variable, postFix);
}
*/

/**
* Get a view by name
*
* @param {string} name unique name of the view to load
*
AntViewManager.prototype.getView = function(name)
{
	for (var i = 0; i < this.views.length; i++)
	{
		// Find the view by name
		if (this.views[i].name == name)
			return this.views[i];
	}

	return null
}
*/


/**
* Change fToggle flag. If true, then only one view is visible at a time. If one is shown, then all other views are looped through and hidden. This is great for tabs.
*
* @param {boolean} fToggle toggle view; default: true
*
AntViewManager.prototype.setViewsToggle = function(fToggle)
{
	this.pageView = fToggle;
}
*/

/**
* Change pageViewSingle flag. If true, then only one view is visible at a time and the parent view is hidden. This setting is per ViewManager and isolated to one level so you can have: 
* viewRoot (pageView - tabs) -> viewNext (will leave root alone) 
* viewApp (single will hide/replace viewNext)
*
* @param {boolean} fToggle toggle view; default: true
*
AntViewManager.prototype.setViewsSingle = function(fToggle)
{
	this.pageViewSingle = fToggle;
}
*/

/**
 * Get active views at this manager level only
 *
 * @public
 * @return {AntViews[]}
 *
AntViewManager.prototype.getActiveViews = function()
{
	var ret = new Array();

	for (var i in this.views)
	{
		if (this.views[i].isActive())
			ret.push(this.views[i]);
	}

	return ret;
}
*/

/*
 * Usage
 * 
 * Can either extend the controller or build it inline

netric.controller.MyController(args...) {
	// Call parent class constructor
	netric.mvc.Controller.call(this, args...);
}

// Set base class
alib.extends(netric.controller.MyController, netric.mvc.Controller);

// Default action
netric.controller.MyController.prototype.actionIndex = function(view) {
	// This is basically the new render function

	// Build UI elements here using view.con

	// Can add sub-controllers to the route by initializing in the aciton
	// and retuning the controller. This controller will link the subcontroller into
	// the automatic routing system so that childController.mainAction will load
	// by default if it exists
	vat con = alib.dom.createElement("div", view.con); // Chilld of view container
	var ctlr = new netric.controller.ObjectBrowserController(con);
	return ctlr;
}
*/

/*
// Old way
var view = this.view.addSubView("appname");
view.render = function() {

}

// New way which calls view.subcontroller.addAction in the view class
var controllerAction = this.view.addSubAction("open/:id", function(view, params) {

});
*/
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
 */
netric.mvc.Router = function() {
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
* @fileOverview netric.mvc.View(s) allow dom elements to be treated like pages and mapped to URL
*
* Each view has a parent manager (reposible for showing and hiding it) then  
* a child manager to handle sub-views. These are basically simple routers.
*
* Views enable a single-page application (no refresh) to have multi-level views
* and deep-linking through the use of a hash url.
*
* Example:
* <code>
* 	parentView.setViewsSingle(true); // Only display one view at a time - children hide parent view
*
* 	var viewItem = parentView.addView("viewname", {});
*
*	viewItem.options.param = "value to forward"; // options is a placeholder object for passing vars to callbacks
*
*	viewItem.render = function() // called only the first time the view is shown
*	{ 
*		this.con.innerHTML = "print my form here"; // this.con is automatically created
*	} 
*
*	viewItem.onshow = function()  // draws in onshow so that it redraws every time the view is displayed
*	{ 
*	};
*
*	viewItem.onhide = function()  // is called every time the view is hidden
*	{ 
*	};
* </code>
*
* @author: 	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2011 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.mvc.View");

alib.require("netric");
alib.require("netric.mvc");

/**
* Create a view instance of netric.mvc.View and place in in an array in viewman
*
* @constructor
* @param {string} name Required unique string name of this view
* @param {object} viewman Required reference to netric.mvc.ViewManager object
* @param {object} parentView reference to a parent netric.mvc.View object
*/
netric.mvc.View = function(name, viewman, parentView)
{

	this.parentViewManager = viewman;
	this.viewManager = null;
	this.parentView = (parentView) ? parentView : null;
	this.activeChildView = null;
	this.isActive = false;
	this.isRendered = false;
	this.options = new Object();
	this.conOuter = null; 		// container passed to place view in
	this.con = null;	 		// child container of conOuter that holds this rendered view
	this.conChildren = null; 	// child container of conOuter that holds all rendered children
	this.variable = ""; 		// variables can exist in url part. Example: view p:pnum would render view p with variable num
	this.pathRelative = ""; 	// path relative to this view
	this.title = "";			// optional human readable title
	this.defaultView = "";
	this.setOnTitleChange = new Array();
    this.fromClone = false;     // determines if the object was being cloned
    
	var parts = name.split(":");
	this.name = parts[0];
	if (parts.length > 1)
	{
		// Possible save the variable name if we are going to use multiple variables
	}
}

/**
 * Call all bound callback functions because this view just loaded
 *
 * @param {string} evname The name of the event that was fired
 */
netric.mvc.View.prototype.triggerEvents = function(evname)
{
	alib.events.trigger(this, evname);
	/*
	for (var i = 0; i < this.boundEvents.length; i++)
	{
		if (this.boundEvents[i].name == evname)
		{
			if ("load" == evname) // add extra param
				this.boundEvents[i].cb(this.boundEvents[i].opts, this.pathRelative);
			else
				this.boundEvents[i].cb(this.boundEvents[i].opts);
		}
	}
	*/
}

/**
 * Call all bound callback functions because this view just loaded
 */
netric.mvc.View.prototype.setDefaultView = function(viewname)
{
	this.defaultView = viewname;
}

/**
 * Call this function to fire all resize events
 *
 * @param {bool} resizeChildren If set to true, all active children will be resized
 */
netric.mvc.View.prototype.resize = function(resizeChildren)
{
	var resizeCh = (resizeChildren) ? true : false;

	if (resizeCh)
		this.viewManager.resizeActiveView();

	this.onresize();
}

/**
 * Internal function to show this view. Will call render on the first time. redner shold be overridden.
 */
netric.mvc.View.prototype.show = function()
{
	//ALib.m_debug = true;
	//ALib.trace("Show: " + this.getPath());

	if (!this.isRendered)
	{
		this.isRendered = true;
		this.render();
	}

	if (this.con)
	{
		//alib.fx.fadeIn(this.con, function() { alib.dom.styleSet(this, "display", "block");  });
		alib.dom.styleSet(this.con, "display", "block");
	}

	if (this.parentViewManager.isMobile)
	{
		var isBack = (window.avChangePageback) ? true : false;
		changePage(this.getPath(true), isBack); // The true param excludes vars to make change to containers rather than specific ids
		window.avChangePageback = false; // Reset flag for next time
	}

	if (this.defaultView && !this.hasChildrenVisible())
		this.navigate(this.defaultView);
	
	this.triggerEvents("show");
	this.onshow();
	this.onresize();
	this.isActive = true;
}

/**
* Internal function to hide this view.
*/
netric.mvc.View.prototype.hide = function(exclChild)
{
	if (!this.isActive)
		return false;

	if (this.con)
	{
		this.con.style.display = "none";
		//alib.fx.fadeOut(this.con, function() { alib.dom.styleSet(this, "display", "none"); });
	}

	this.isActive = false;

	if (this.isRendered)
	{
		this.triggerEvents("hide");
		this.onhide();
	}
}

/**
* Pass-through to this.parentViewManager.addView with this as parent
* See netric.mvc.ViewManager::addView
*/
netric.mvc.View.prototype.addView = function(name, optionargs, con)
{
	var usecon = (con) ? con : null;

	if (this.viewManager == null)
	{
		this.viewManager = new netric.mvc.ViewManager();
		if (this.parentViewManager)
			this.viewManager.isMobile = this.parentViewManager.isMobile;
	}

	//ALib.m_debug = true;
	//ALib.trace("Adding View: " + this.getPath() + "/" + name);

	return this.viewManager.addView(name, optionargs, usecon, this);
}

/**
* Get a child view by name
*
* @param {string} name unique name of the view to load
* @return {netric.mvc.View} View if found by name, null if no child view exists
*/
netric.mvc.View.prototype.getView = function(name)
{
	if (this.viewManager)
	{
		return this.viewManager.getView(name);
	}

	return null
}

/**
* Pass-through to this.parentViewManager.setViewsToggle
* See netric.mvc.ViewManager::addView
*/
netric.mvc.View.prototype.setViewsToggle = function(fToggle)
{
	if (this.viewManager == null)
	{
		this.viewManager = new netric.mvc.ViewManager();

		if (this.parentViewManager)
			this.viewManager.isMobile = this.parentViewManager.isMobile;
	}
	
	this.viewManager.setViewsToggle(fToggle);
}

/**
* Pass-through to this.parentViewManager.setViewsToggle. When a child shows then hide this view - used for heiarch
* See netric.mvc.ViewManager::setViewsSingle
*/
netric.mvc.View.prototype.setViewsSingle = function(fToggle)
{
	if (this.viewManager == null)
	{
		this.viewManager = new netric.mvc.ViewManager();

		if (this.parentViewManager)
			this.viewManager.isMobile = this.parentViewManager.isMobile;
	}
	
	this.viewManager.setViewsSingle(fToggle);
}

/**
 * Get the parent view if set
 *
 * @return {netric.mvc.View|bool} View if parent is set, false if there is no parent
 */
netric.mvc.View.prototype.getParentView = function()
{
	return (this.parentView) ? this.parentView : false;
}

/**
* Traverse views and get the full path of this view:
* view('app').view('customers') = 'app/customers'
*
* @param bool excludevars If set to true, then vars will not be included in the returned path
*/
netric.mvc.View.prototype.getPath = function(excludevars)
{
	var name = this.name;
	var doNotPrintVar = (typeof excludevars != "undefined") ? excludevars : false;
    
	// Make sure the variable in included
	if (this.variable && doNotPrintVar == false)
		name += ":" + this.variable;
        
	if (this.parentView)
		var path = this.parentView.getPath() + "/" + name;
	else
		var path = name;

	return path;
}

/**
* Get a numan readable title. If not set, then create one.
*
* @this {netric.mvc.View}
* @public
* @param {DOMElement} el An optional dom element to bind 'onchange' event to. When title of view changes, the innerHTML of el will change
* @return {string} The title of this view
*/
netric.mvc.View.prototype.getTitle = function(el)
{
	if (this.title)
	{
		var title = this.title;
	}
	else
	{
		// replace dash with space
		var title = this.name.replace('-', ' ');
		// replace underline with space
		var title = this.name.replace('_', ' ');
		// ucword
		title = title.replace(/^([a-z])|\s+([a-z])/g, function ($1) { return $1.toUpperCase(); });
	}

	if (typeof el != "undefined")
	{
		el.innerHTML = title;
		this.setOnTitleChange[this.setOnTitleChange.length] = el;
	}
	else
	{
		return title;
	}
}

/**
* Set a human readable title
*/
netric.mvc.View.prototype.setTitle = function(title)
{
	this.title = title;
	for (var i = 0; i < this.setOnTitleChange.length; i++)
	{
		try
		{
			this.setOnTitleChange[i].innerHTML = title;
		}
		catch(e) {}
	}
}

/**
* Check url part to see if the name matches this view
*/
netric.mvc.View.prototype.nameMatch = function(name)
{
	if (typeof name == "undefined")
	{
		throw "No view name was passed to netric.mvc.View::nameMatch for " + this.getPath();
	}

	var parts = name.split(":");
	name = parts[0];

	return (name == this.name) ? true : false;
}

/**
* Set the hash and load a view programatically. 
*/
netric.mvc.View.prototype.navigate = function(viewname)
{
	document.location.hash = "#" + this.getPath() + "/" + viewname;
}

/**
* Check if going back a view is an option (are we on first level).
*
* This does not rely entirely on the history object because if 
* we are at the root view (home), then we don't want to go back.
*/ 
netric.mvc.View.prototype.hasback = function()
{
	var ret = false; // Assume we are on the root
	var path = this.getPath();

	if (path.indexOf("/")!=-1)
	{
		if (history.length > 1)
			ret = true;
	}

	return ret;
}

/**
* Navigate up to parent view
*/
netric.mvc.View.prototype.goup = function(depth)
{
	if (this.parentView)
	{
		document.location.hash = "#" + this.parentView.getPath();
	}
	else
	{
		history.go(-1);
	}

	// global avChangePageback is used in mobile to determine transition direction
	window.avChangePageback = true;
}

/**
* Check if child views are being shown = check for deep linking
*
* @return {bool} True if the hash path has child views visible, otherwise false
*/
netric.mvc.View.prototype.hasChildrenVisible = function()
{
	if (document.location.hash == "#" + this.getPath() || document.location.hash == "") // last assumes default
		return false;
	else
		return true;
}

/**
* Go back
*/
netric.mvc.View.prototype.goback = function(depth)
{
	history.go(-1);

	// global avChangePageback is used in mobile to determine transition direction
	window.avChangePageback = true;
}

/**
* Pass-through to this.parentViewManager.load
* See netric.mvc.ViewManager::load
*/
netric.mvc.View.prototype.load = function(path)
{
	this.pathRelative = path; // path relative to this view

	if (this.viewManager != null)
	{
		if (!this.viewManager.load(path))
		{
			this.m_tmpLoadPath = path; // If it failed to load, cache for later just in case views are still loading
		}
	}
	else
	{
		this.m_tmpLoadPath = path; // If it failed to load, cache for later just in case views are still loading
	}
}

/**
* Clear loading flag that will cause all subsequent load calls to be queued until setViewsLoaded is called.
*/
netric.mvc.View.prototype.setViewsLoaded = function()
{
	//ALib.m_debug = true;
	//ALib.trace("View ["+this.name+"] finished loading ");

	if (this.m_tmpLoadPath)
	{
		//ALib.m_debug = true;
		//ALib.trace("Found delayed load " + this.m_tmpLoadPath);
		this.load(this.m_tmpLoadPath);
		this.m_tmpLoadPath = "";
	}

	if (this.defaultView && !this.hasChildrenVisible())
		this.navigate(this.defaultView);

	// Call load callbacks for view
	this.triggerEvents("load");
}

/**
* Find out if this view had children views
*/
netric.mvc.View.prototype.hideChildren = function()
{
	if (this.viewManager)
	{
		for (var i = 0; i < this.viewManager.views.length; i++)
		{
			this.viewManager.views[i].hide();
			this.viewManager.views[i].hideChildren();
		}
	}

	this.pathRelative = ""; // Reset relative path
}

/**
 * Gets the object id from hash url string
 *
 * @public
 * @this {netric.mvc.View}
 * @param {string} objName      Object Name to be checked
 */
netric.mvc.View.prototype.getHashObjectId = function(objName)
{
    if(this.name == objName)
        return this.variable;
        
    if (this.parentView)
        var objId = this.parentView.getHashObjectId(objName);
    
    if(objId)
        return objId;
    else
        return false;
}

/**
* Used to draw view and should be overriden. 
*
* If a containing element was passed on new netric.mvc.View then this.con 
* will be populated with a div that can be manipulated with contents. 
* this.options is also available for any processing.
*/
netric.mvc.View.prototype.render = function()
{
}

/**
* Can be overridden. Fires once a view is shown.
*/
netric.mvc.View.prototype.onshow = function()
{
}

/**
* Can be overridden. Fires once a view is hidden.
*/
netric.mvc.View.prototype.onhide = function()
{
}

/**
* Can be overridden. Fires once a view is shown for resizing.
*/
netric.mvc.View.prototype.onresize = function()
{
	//alib.m_debug = true;
	//alib.trace("Resize: " + this.name);
}

/**
* @fileOverview Load instance of netric application
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2011 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.mvc.ViewTemplate");

alib.require("netric.mvc");

/**
 * Application instance
 *
 * @param {netric.Account} account The current account netric is running under
 */
netric.mvc.ViewTemplate = function(opt_html) {
	/**
	 * Raw HTML for this template
	 * 
	 * 
	 * @private
	 * @type {string}
	 */
	this.html_ = opt_html || "";

	/**
	 * Dom elements for template
	 * 
	 * @type {DOMElement[]}
	 */
	this.domElements_ = null;

	/**
	 * Associateve array of dom elements that can be referenced later by name
	 *
	 * @private
	 * @type {Array()}
	 */
	this.domExports_ = null;
}

/**
 * Get the dom element for this template
 * 
 * @public
 * @return {DOMElement}
 */
netric.mvc.ViewTemplate.prototype.getDom = function() {
	if (this.domElements_ != null) {
		return this.domElements_;
	}

	// TODO: render this.html_ to dom and return
}

/**
 * Add a dom element to the template
 * 
 * @param {DOMElement} domEl The element to add to this template
 * @param {string} opt_exportName Optional name to export for reference by this.<name>
 */
netric.mvc.ViewTemplate.prototype.addElement = function(domEl, opt_exportName) {

	if (this.domElements_ == null) {
		this.domElements_ = new Array();
	}

	// Add the element to the template
	this.domElements_.push(domEl);

	if (opt_exportName) {
		this[opt_exportName] = domEl;
	}
}

/**
 * Render the template into a dom element
 * 
 * @param {DOMElement}
 */
netric.mvc.ViewTemplate.prototype.render = function(domCon) {
	if (this.domElements_ != null) {
		for (var i in this.domElements_) {
			domCon.appendChild(this.domElements_[i]);
		}

	} else {
		domCon.innerHTML = this.html_;
	}
}
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
/**
* @fileOverview Manage single layer of views in an array.
*
* Each view has a parent manager (reposible for showing and hiding it) then  
* a child manager to handle sub-views. These are basically simple routers.
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2012 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.mvc.ViewManager");

alib.require("netric");
alib.require("netric.mvc");
alib.require("netric.mvc.ViewTemplate")

/**
 * Creates an instance of netric.mvc.ViewManager
 *
 * @constructor
 */
netric.mvc.ViewManager = function()
{
	this.path = "";
	this.currViewName = "";
	this.views = new Array();
	this.pageView = false; 			// Pageview means only one view is avaiable at a time
	this.pageViewSingle = false; 	// pageViewSingle means that if a child view shows, this view is hidden
	this.isMobile = false;			// Handle creating things differently
}

/**
* Add a new view
*
* @param {string} name The unique name (in this viewmanager) of this view
* @param {object} optionsargs Object of optional params that populates this.options
* @param {object} con Contiaining lement. If passed, then a sub-con will automatically be created. 
* 							If not passed, then pure JS is assumed though utilizing the onshow 
* 							and onhide callbacks for this view			
* @param {object} parentView An optional reference to the parent view. 
* 							This is passed when the view.addView function is called to maintain heiarchy.		 
*/
netric.mvc.ViewManager.prototype.addView = function(name, optionargs, con, parentView)
{
	var pView = parentView || null;
	var useCon = con || null;

	// Make sure this view is unique
	for (var i = 0; i < this.views.length; i++)
	{
		// If a view by this name already exists, then return it
		if (this.views[i].nameMatch(name))
			return this.views[i];
	}

	// Create new view
	var view = new netric.mvc.View(name, this, pView);
	view.options = optionargs;
	if (useCon)
	{
		view.conOuter = useCon;
	}
	else if (parentView)
	{
		if (parentView.conOuter)
			view.conOuter = parentView.conOuter;
	}

	if (this.isMobile)
	{
		var contentCon = document.getElementById(view.getPath()+"_con");
		if (!contentCon)
		{
			var path = view.getPath();
			var pageCon = alib.dom.createElement("div", document.getElementById("main"));
			pageCon.style.display="none";
			pageCon.style.position="absolute";
			pageCon.style.top="0px";
			pageCon.style.width="100%";
			pageCon.id = path;

			// Main header container
			var headerCon = alib.dom.createElement("div", pageCon);
			alib.dom.styleSetClass(headerCon, "header");

			// Right button container
			var rightButton = alib.dom.createElement("button", headerCon);
			alib.dom.styleSetClass(rightButton, "right");

			// Left button container
			if (view.hasback())
			{
				var leftButton = alib.dom.createElement("button", headerCon, "Back");
				alib.dom.styleSetClass(leftButton, "left arrow");
				leftButton.view = view;
				leftButton.onclick = function() { view.goup(); }
				/*
				var goback = alib.dom.createElement("img", leftButton);
				goback.src = '/images/icons/arrow_back_mobile_24.png';
				goback.view = view;
				goback.onclick = function() { view.goup(); }
				*/
			}

			// Title container
			var title = alib.dom.createElement("h1", headerCon);

			if (typeof Ant != "undefined")
				title.innerHTML = view.getTitle();
				//title.innerHTML = Ant.account.companyName;

			// Sky Stebnicki: I believe this may be depriacted but needs to be verified
			var conAppTitle = alib.dom.createElement("div", headerCon);
			
			var contentCon = alib.dom.createElement("div", pageCon);
			contentCon.id = path+"_con";
			alib.dom.styleSetClass(contentCon, "viewBody");

			// Used by the AntApp class to set the title of the application
			view.conAppTitle = conAppTitle;
		}
		
		view.con = contentCon;
	}
	else
	{
		view.con = (view.conOuter) ? alib.dom.createElement("div", view.conOuter) : null;
		if (view.con)
			view.con.style.display = 'none';
	}

	this.views[this.views.length] = view;
	return view;
}

/**
 * Resize the active view and it's children
 */
netric.mvc.ViewManager.prototype.resizeActiveView = function()
{
	if (this.currViewName)
	{
		var actView = this.getView(this.currViewName);
		if (actView)
			actView.resize();
	}

}

/**
* Load a view by converting a path to a name
*
* @param {string} path path like my/app/name will load "my" view of this viewManager
*/
netric.mvc.ViewManager.prototype.load = function(path)
{
	this.path = path;
	var postFix = "";
	var nextView = "";

	if (this.path.indexOf("/")!=-1)
	{
		var parts = this.path.split("/");
		this.currViewName = parts[0];
		if (parts.length > 1)
		{
			for (var i = 1; i < parts.length; i++) // Skip of first which is current view
			{
				if (postFix != "")
					postFix += "/";
				postFix += parts[i];
			}
		}
	}
	else
		this.currViewName = path;

	var variable = "";
	var parts = this.currViewName.split(":");
	if (parts.length > 1)
	{
		this.currViewName = parts[0];
		variable = parts[1];
	}

	return this.loadView(this.currViewName, variable, postFix);
}

/**
* Even fires when all views have finished loading
*/
netric.mvc.ViewManager.prototype.onload = function()
{
}

/**
* Get a view by name
*
* @param {string} name unique name of the view to load
*/
netric.mvc.ViewManager.prototype.getView = function(name)
{
	for (var i = 0; i < this.views.length; i++)
	{
		// Find the view by name
		if (this.views[i].name == name)
			return this.views[i];
	}

	return null
}

/**
* Load a view by name
*
* @param {string} name unique name of the view to load
* @param {string}  variable if view has a nane like id:[number] then a variable of number would be passed
* @param {string} postFix  traling URL hash my/app would translate to name = "my" and postFix = "app"
*/
netric.mvc.ViewManager.prototype.loadView = function(name, variable, postFix)
{
	var bFound = false;

	if (!postFix)
		var postFix = "";

	// Loop through child views, hide all but the {name} field
	for (var i = 0; i < this.views.length; i++)
	{
		// Find the view by name
		if (this.views[i].name == name)
		{
			this.views[i].variable = variable;

			// Flag that we found the view
			bFound = true;

			/*
			* If we are a child view and the views are set to single pages only
			* the last view in the list should be viewable and the parent will be hidden
			*/
			if (this.pageViewSingle && this.views[i].parentView)
				this.views[i].parentView.hide();

			if (postFix!="") // This is not the top level view - there are children to display in the path
			{
				/*
				* Check to see if this view has been rendered 
				* already - we only render the first time
				* It is possible in a scenario where a deep url is loaded
				* like /my/path to have 'my' never shown because we jump
				* straight to 'path' but we still need to make sure it is rendered.
				*/
				if (this.views[i].isRendered == false)
				{
					this.views[i].render();
					this.views[i].isRendered = true;
				}

				/*
				* As mentioned above, if we are in singleView mode then 
				* don't show views before the last in the list
				*/
				if (!this.pageViewSingle)
					this.views[i].show();

				// Continue loading the remainder of the path - the child view(s)
				this.views[i].load(postFix);
			}
			else // This is a top-level view meaning there are no children
			{
				this.views[i].show(); // This will also render if the view has not yet been rendered
				this.views[i].hideChildren();
			}

			// Call load callbacks for view
			this.views[i].triggerEvents("load");
		}
		else if (this.pageView) // Hide this view if we are in pageView because it was not selected
		{
			/*
			 * pageView is often used for tab-like behavior where you toggle 
			 * through pages/views at the same level - not affecting parent views
			 */
			this.views[i].hide();
			this.views[i].hideChildren();
		}
	}

	//ALib.m_debug = true;
	//ALib.trace("Showing: " + name + " - " + bFound);
	return bFound;
}

/**
* Change fToggle flag. If true, then only one view is visible at a time. If one is shown, then all other views are looped through and hidden. This is great for tabs.
*
* @param {boolean} fToggle toggle view; default: true
*/
netric.mvc.ViewManager.prototype.setViewsToggle = function(fToggle)
{
	this.pageView = fToggle;
}

/**
* Change pageViewSingle flag. If true, then only one view is visible at a time and the parent view is hidden. This setting is per ViewManager and isolated to one level so you can have: 
* viewRoot (pageView - tabs) -> viewNext (will leave root alone) 
* viewApp (single will hide/replace viewNext)
*
* @param {boolean} fToggle toggle view; default: true
*/
netric.mvc.ViewManager.prototype.setViewsSingle = function(fToggle)
{
	this.pageViewSingle = fToggle;
}

/**
 * Get active views at this manager level only
 *
 * @public
 * @return {AntViews[]}
 */
netric.mvc.ViewManager.prototype.getActiveViews = function()
{
	var ret = new Array();

	for (var i in this.views)
	{
		if (this.views[i].isActive())
			ret.push(this.views[i]);
	}

	return ret;
}

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
	//view.setTemplate(netric.template.application.small);
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
	return netric.template.application.large();
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
alib.require("netric.mvc.Router");
alib.require("netric.Device");
alib.require("netric.ui.ApplicationView");

/**
 * Application instance
 *
 * @param {netric.Account} account The current account netric is running under
 */
netric.Application = function(account) {
	/**
	 * Represents the actual netric account
	 *
	 * @public
	 * @var {netric.Application.Account}
	 */
	this.account = account;

	/**
	 * Device information class
	 *
	 * @public
	 * @var {netric.Device}
	 */
	this.device = new netric.Device();
};

/**
 * Static function used to load the application
 *
 * @param {function} cbFunction Callback function once app is loaded
 */
netric.Application.load = function(cbFunction) {

	/*
	 * The first thing we need to do is load the current account so
	 * we can inject it as a dependency to the application instance.
	 */
	netric.account.loader.get(function(acct){

		// Create appliation instance for loaded account
		var app = new netric.Application(acct);

		// Set global reference to application to enable netric.getApplication();
		netric.application_ = app;  

		// Callback passing initialized application
		if (cbFunction) {
			cbFunction(app);	
		}
	});
}

/**
 * Get the current account
 *
 * @return {netric.Account}
 */
netric.Application.prototype.getAccount = function() {
	return this.account;
}

/**
 * Run the loaded application
 *
 * @param {DOMElement} domCon Container to render applicaiton into
 */
netric.Application.prototype.run = function(domCon) {

	// Create root application view
	var appView = new netric.ui.ApplicationView(this);

	/*
	 * Setup the router so that any change to the URL will route through
	 * the redner action for the front contoller which will propogate the new
	 * url path down through all children contollers as well.
	 */
	var router = new netric.mvc.Router();
	//router.options.viewManager = new AntViewManager();
	//router.options.viewManager.setViewsToggle(true); // Only view one view at a time at the root level
	router.onchange = function(path) {
		appView.load(path);
	}

	// Render application
	appView.render(domCon);
}