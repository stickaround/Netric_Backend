/**
 * @fileoverview Advance Search
 */
'use strict';

var React = require('react');
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var UiAdvanceSearch = require("../ui/AdvanceSearch.jsx");
var actionsLoader = require("../entity/actionsLoader");
var definitionLoader = require("../entity/definitionLoader");

/**
 * Controller that loads an Advance Search
 */
var AdvanceSearchController = function() {}

/**
 * Extend base controller class
 */
netric.inherits(AdvanceSearchController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
AdvanceSearchController.prototype.rootReactNode_ = null;

/**
 * A collection of field definition
 *
 * @private
 */
AdvanceSearchController.prototype.entityFields_ = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
AdvanceSearchController.prototype.onLoad = function(opt_callback) {
	
	this.actions_ = actionsLoader.get(this.props.objType);

    if (this.props.objType) {
        // Get the default view from the object definition
        definitionLoader.get(this.props.objType, function(def){
            this.entityFields_ = def.fields;
            if (opt_callback) {
                opt_callback();
            }
        }.bind(this));
    } else if (opt_callback) {
        // By default just immediately execute the callback because nothing needs to be done
        opt_callback();
    }

}

/**
 * Render this controller into the dom tree
 */
AdvanceSearchController.prototype.render = function() {
	// Set outer application container
	var domCon = this.domNode_;
	
    // Define the data
	var data = {
		title: this.props.browsebytitle ||this.props.title,
		entityFields: this.entityFields_,
        objType: this.props.objType,
        deviceSize: netric.getApplication().device.size,
        layout: (netric.getApplication().device.size === netric.Device.sizes.small)
            ? "compact" : "table",
	}

	// Render browser component
	this.rootReactNode_ = React.render(
		React.createElement(UiAdvanceSearch, data),
		domCon
	);
}


module.exports = AdvanceSearchController;

