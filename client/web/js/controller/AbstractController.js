/**
 * @fileoverview This is the bast controller that all other controllers should extend
 *
 * All instances of this class should call in their constructor:
 * 	AbstractController.call(this, args...);
 *
 * The lifecycle of a controller is:
 * ::load -> onLoad is the first function called when the controllers is first loaded
 * ::render is called after the controller is loaded and any time params change
 * ::unload -> onUnload when the controller is removed from the document - cleanup!
 * ::pause - onPause Is called if the controller gets moved to the background
 * ::resume - onResume Is called if the controller was paused in the background but gets moved to the foreground again
 *
 * And immediately after the constructor definition call:
 * netric.inherits(<thiscontrollername>, AbstractController);
 */
'use strict';

 var controller = require("./controller.js");

/**
 * Abstract controller
 *
 * @constructor
 */
var AbstractController = function() {

	/*
	 * We try not to include too much in the base constructor because there is
	 * no way we can assure that all inherited classes call AbstractController.call
	 */

	// Call base class constructor
	//AbstractController.call(this, domCon);

}

/** 
 * Define properties forwarded to this controller
 * 
 * @protected
 * @type {Object}
 */
AbstractController.prototype.props = {};

/**
 * DOM node to render everything into
 *
 * @private
 * @type {RactElement|DOMElement}
 */
AbstractController.prototype.domNode_ = null;

/**
 * The type of controller this is.
 * 
 * @see comments on netric.location.controller.types property
 */
AbstractController.prototype.type_ = null;

/**
 * Handle to the parent router of this controller
 * 
 * @type {netric.location.Router}
 */
AbstractController.prototype.router_ = null;

/** 
 * Flag to indicate if it is paused
 *
 * @private 
 * @type {bool}
 */
AbstractController.prototype.isPaused_ = false;

/**
 * All child classes should extend this base class with:
 */
//netric.inherits(ModuleController, AbstractController);


/**
 * Handle loading and setting up this controller but not yet rendering it
 *
 * @param {Object} data Optional data to pass to the controller including data.params for URL params
 * @param {ReactElement|DomElement} opt_domNode Optional parent node to render controller into
 * @param {netric.location.Router} opt_router The parent router of this controller
 * @param {function} opt_callback If set call this function when we are finished loading
 */
AbstractController.prototype.load = function(data, opt_domNode, opt_router, opt_callback) {

	this.domNode_ = null;

	// Local variables passed to this controller
	this.props = data;

	// Setup the type
	this.type_ = data.type || controller.types.PAGE;

	// Set reference to the parent router
	this.router_ = opt_router || null;

	// Parent DOM node to render into
	var parentDomNode = opt_domNode || null;

	// onLoad may be over-ridden by child classes for additional processing
	this.onLoad(function(){
		
		// Set the root dom node for this controller
		this.setupDomNode_(parentDomNode)

		// Pause parent controller (if a page)
		if (this.getParentController() && this.type_ == controller.types.PAGE) {
			this.getParentController().pause();
		}

		// Render the controller
		this.render();

		if (opt_callback) {
			opt_callback();
		}

	}.bind(this));
}

/**
 * Unload the controller
 *
 * This is where we will cleanup
 */
AbstractController.prototype.unload = function() {
	// The onUnload callback for child classes needs to be called first
	this.onUnload();

	// Remove the elements from the page
	if (this.domNode_) {
		if (this.domNode_.parentElement) {
			this.domNode_.parentElement.removeChild(this.domNode_);
		} else {
			this.domNode_.innerHTML = "";
		}
	}

	// Resume the parent controller if it has been paused
	if (this.getParentController()) {
		if (this.getParentController().isPaused()) {
			this.getParentController().resume();
		}
	}
}

/**
 * Resume this controller and move it back to the foreground
 */
AbstractController.prototype.resume = function() {
	// If this controller is of type PAGE then hide the parent (if exists)
	if (this.isPaused()) {

		// unhide me
		if (this.domNode_) {
			alib.dom.styleSet(this.domNode_, "display", "block");
		}

		// Set paused flag for resuming later
		this.isPaused_ = false;

		this.onResume();
	}
}

/**
 * Pause this controller and move it into the background
 */
AbstractController.prototype.pause = function() {

    // Hide me
    if (this.domNode_) {
        alib.dom.styleSet(this.domNode_, "display", "none");
    }

    // Set paused flag for resuming later
    this.isPaused_ = true;

    // Get the parent controller of this controller
    if (this.getType() == controller.types.PAGE) {
        var parentController = this.getParentController();
        if (parentController && parentController.getType() == controller.types.PAGE) {
            // Pause/hide parent controller before we render this controller
            parentController.pause();
        }
    }

    this.onPause();
}

/**
 * Add a subroute to the nexthop router if it exists
 *
 * @param {string} path The path pattern
 * @param {AbstractController} controllerClass The ctrl class to load
 * @param {Object} data Any data to pass to the controller
 * @param {DOMElement} domNode The node to load this controller into
 * @return {bool} true if route added, false if it failed
 */
AbstractController.prototype.addSubRoute = function(path, controllerClass, data, domNode) {
	if (this.getChildRouter()) {
		this.getChildRouter().addRoute(path, controllerClass, data, domNode);
		return true;
	} else {
		// TODO: use dialog?
		return false;
	}
}

/**
 * Get my parent controller
 *
 * @return {netric.location.Router} Router than owns the route tha rendered this controller
 */
AbstractController.prototype.getParentController = function() {

	// Get the parent router of this controller
	var parentRouter = this.getParentRouter();
	
	if (parentRouter) {
		// Get the parent router to my parent
		var grandparentRouter = parentRouter.getParentRouter();
		// Find out if my parent router is the child of another router
		if (grandparentRouter) {
			// This should always return a route, but never assume anything!
			var activeRoute = grandparentRouter.getActiveRoute();
			if (activeRoute) {
				return activeRoute.getController();
			} else {
				throw "Problem! Could not find an active route from withing a controller.";
			}	
		}
	}

	return null;
}

/**
 * Set the root dom node to render this controller into
 *
 * @param {DOMElement} opt_domNode Optional DOM node. Usually only used for fragments but also for custom root node.
 */
AbstractController.prototype.setupDomNode_ = function(opt_domNode) {

	var parentNode = null;

    switch (this.type_) {
    	/*
		 * If this is of type page then we need to walk up the tree of
		 * controllers to get the top page controller's dom parent because
		 * pages will hide their parents so a child page cannot be a child dom
		 * element.
		 */
    	case controller.types.PAGE:
    		
    		/* 
    		 * We can set a default root node to use if no parent nodes exists.
    		 * If no default is defined and there are no parent pages the new controller
    		 * pages will be rendered into document.body.
    		 */
    		var defaultRootNode = opt_domNode || null;
    		parentNode = this.getTopPageNode(defaultRootNode);
    		break;

    	/*
    	 * A fragment is a controller that loads in a child DOM of another controler.
    	 * It is unique in that it cannot hide its parent so the contianing controller
    	 * will always be visible.
    	 */
    	case controller.types.FRAGMENT:
    		if (opt_domNode) {
    			parentNode = opt_domNode;	
    		} else {
    			throw "Cannot render a fragment controller without passing a valid DOM element";
    		}
    		break;

    	/*
		 * If this is a dialog then render a new dialog into the dom and get the inner container to render controller
		 */
    	case controller.types.DIALOG:
    		// TODO: create dialog
    		break;
    }
	

    var id = (this.getParentRouter()) ? this.getParentRouter().getActiveRoute().getPath() : null;
	this.domNode_ = alib.dom.createElement("div", parentNode, null, {id:id});
}

/**
 * Get the topmost page node for rendering child pages
 * 
 * This is important because child pages can hide their parent
 * so child controllers of type PAGE cannot be in a child in the DOM tree
 * or they will disappear along with the parent when we pause/hide the parent.
 *
 * @public
 * @param {DOMElement} opt_rootDomNode An optional default root in case none is found (like this is a root conroller)
 * @return {DOMElement} The parent of the topmost page in this tree (will stop at a fragment or top)
 */
AbstractController.prototype.getTopPageNode = function(opt_rootDomNode) {
	
	if (this.getParentController()) {
		if (this.getParentController().getType() == controller.types.PAGE) {
			return this.getParentController().getTopPageNode();
		} else if (this.getParentController().getDOMNode().parentNode) {
            return this.getParentController().getDOMNode().parentNode;
        }
	}

	// No parent pages were found so simply return my parent node
	if (this.domNode_) {
		if (this.domNode_.parentNode) {
			return this.domNode_.parentNode;
		}
	}

	// This must be a new root page controller because we cound't find any parent DOM elements
	if (opt_rootDomNode) {
		return opt_rootDomNode;
	} else {
		return document.body;
	}

}

/**
 * Get the router that owns this controller
 *
 * @return {netric.location.Router} Router than owns the route tha rendered this controller
 */
AbstractController.prototype.getParentRouter = function() {

	if (this.router_) {
		return this.router_.getParentRouter();
	}

	return null;
}

/**
 * Get the router assoicated with next-hops
 *
 * @return {netric.location.Router} Handle to the router for child routes
 */
AbstractController.prototype.getChildRouter = function() {
	return this.router_;
}

/**
 * Get the current route path to this controller
 *
 * If this is a dialog or an inline contoller with no route then
 * it will simply return null.
 *
 * @return {stirng} Absolute path of the current controller.
 */
AbstractController.prototype.getRoutePath = function() {
	if (this.getParentRouter()) {
		return this.getParentRouter().getActiveRoute().getPath();
	}

	// This controller does not appear to be part of a route which
	// means it is either a dialog or inline.
	return null;
}

/**
 * Get the type of controller
 *
 * @return {controller.types}
 */
AbstractController.prototype.getType = function() {
	return this.type_;
}


/**
 * Get the dom node this controller is rendered into
 *
 * @returns {RactElement|DOMElement}
 */
AbstractController.prototype.getDOMNode = function() {
    return this.domNode_;
}

/**
 * Detect if this controller was previously paused
 *
 * @return {bool} true if the controller was paused, false if not
 */
AbstractController.prototype.isPaused = function() {
	return this.isPaused_;
}

/**
 * Render is called to enter the controller into the Dom
 * 
 * @abstract
 * @param {ReactElement|DomElement} ele The element to render into
 * @param {Object} data Optiona forwarded data
 */
AbstractController.prototype.render = function() {}

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * This can be over-ridden by child classes to extend what gets done while loading the controller.
 * One common use is to setup runtiem sub-routes based on some asyncrhonously loaded data.
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
AbstractController.prototype.onLoad = function(opt_callback) {
	// By default just immediately execute the callback because nothing needs to be done
	if (opt_callback)
		opt_callback();
}

/**
 * Called when the controller is unloaded from the page
 */
AbstractController.prototype.onUnload = function() {}

/**
 * Called when this controller is paused and moved to the background
 */
AbstractController.prototype.onPause = function() {}

/**
 * Called when this function was paused but it has been resumed to the foreground
 */
AbstractController.prototype.onResume = function() {}

module.exports = AbstractController;
