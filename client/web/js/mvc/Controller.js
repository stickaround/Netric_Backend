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
	 * Type determins how this controller is loaded into the document
	 *
	 * @private
	 */
	this.type_ = netric.mvc.Controller.types.page;
}

/**
 * Define the controller types to determine how they are rendered
 *
 * @public
 */
netric.mvc.Controller.types = {
	
	// Page will hide parent when loaded
	page : 1,
	
	// A fragmen is loaded inline in the context of a page
	fragment : 3,

	// Modals are opened in a dialog
	modal : 5
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