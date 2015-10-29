/**
 * @fileoverview Advanced Search
 */
'use strict';

var React = require('react');
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var UiAdvancedSearch = require("../ui/AdvancedSearch.jsx");
var definitionLoader = require("../entity/definitionLoader");
var browserViewSaver = require("../entity/browserViewSaver");

/**
 * Controller that loads an Advanced Search
 */
var AdvancedSearchController = function() {}

/**
 * Extend base controller class
 */
netric.inherits(AdvancedSearchController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
AdvancedSearchController.prototype.rootReactNode_ = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
AdvancedSearchController.prototype.onLoad = function(opt_callback) {
    
    var callbackWhenLoaded = opt_callback || null;
    
    if (callbackWhenLoaded) {
        callbackWhenLoaded();
    } else {
        this.render();
    }
}

/**
 * Render this controller into the dom tree
 */
AdvancedSearchController.prototype.render = function() {
	// Set outer application container
	var domCon = this.domNode_;
	var entities = new Array();
	var entityFields = new Array();
	
    // Define the data
	var data = {
	        title: this.props.title || "Advanced Search",
	        objType: this.props.objType,
	        entityDefinition: this.props.entityDefinition,
	        browserView: this.props.browserView,
	        onApplySearch: this.props.onApplySearch,
	        onSaveView: this._saveView,
	        onChangeTitle: this.props.onChangeTitle,
	}
	
	// Render browser component
    this.rootReactNode_ = React.render(
            React.createElement(UiAdvancedSearch, data),
            domCon
    );
}

/**
 * Save the browser view
 * 
 * @param {netric\entity\BrowserView} browserView   The browser view to save
 */
AdvancedSearchController.prototype._saveView = function(browserView) {
    browserViewSaver.save(browserView, function() {
        console.log("View saved");
    });
}

module.exports = AdvancedSearchController;

