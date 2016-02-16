/**
 * @fileoverview Plugin Controller
 *
 * Manages the displaying of plugins outside of entity form
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var netric = require("../base");
var controller = require("./controller");
var Plugin = require("../entity/Plugin");
var AbstractController = require("./AbstractController");

/**
 * Controller that loads a File Upload Component
 */
var PluginController = function () {
}

/**
 * Extend base controller class
 */
netric.inherits(PluginController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
PluginController.prototype._rootReactNode = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
PluginController.prototype.onLoad = function (opt_callback) {

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
PluginController.prototype.render = function () {

    if(!this.props.pluginName) {
        throw "Plugin name is required.";
    }

    // Set outer application container
    var domCon = this.domNode_;

    // Unhide toolbars if we are in a page mode
    var hideToolbar = this.props.hideToolbar || true;
    if (this.getType() === controller.types.PAGE) {
        hideToolbar = false;
    }

    // Define the data
    var data = {
        title: this.props.title || "",
    }

    var uiPlugin = netric.getObjectByName(this.props.pluginName, null, Plugin.List);

    // Render browser component
    try {
        this._rootReactNode = ReactDOM.render(
            React.createElement(uiPlugin, data),
            domCon
        );
    } catch (e) {
        console.error("Could not create plugin component: " + this.props.pluginName + ":" + e);
    }
}

module.exports = PluginController;

