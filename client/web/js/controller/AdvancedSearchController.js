/**
 * @fileoverview Advanced Search
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
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
    this.rootReactNode_ = ReactDOM.render(
        React.createElement(UiAdvancedSearch, data),
        domCon
    );
}

/**
 * Save the browser view
 * 
 * @param {netric\entity\BrowserView} browserView   The browser view to save
 * @param {object} data  Contains the user input details for additional browser view information
 */
AdvancedSearchController.prototype._saveView = function(browserView, data) {
    
    browserView.name = data.name;
    browserView.description = data.description;
    browserView.default = data.default;

    // Check if the browserView is system generated, then we will set the id to null to generate a new id for the custom view
    if(browserView.system) {

        // Set the browserView.id to null so we can set a new id value after saving.
        browserView.setId(null);
    }

    browserViewSaver.save(browserView);
}

module.exports = AdvancedSearchController;

