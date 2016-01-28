/**
 * @fileoverview Entity Members
 *
 * Manages the membership of an entity
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var netric = require("../base");
var controller = require("./controller");
var AbstractController = require("./AbstractController");
var UiMembers = require("../ui/Members.jsx");

/**
 * Controller that loads a File Upload Component
 */
var EntityMembersController = function () {
}

/**
 * Extend base controller class
 */
netric.inherits(EntityMembersController, AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
EntityMembersController.prototype._rootReactNode = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
EntityMembersController.prototype.onLoad = function (opt_callback) {

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
EntityMembersController.prototype.render = function () {

    // Set outer application container
    var domCon = this.domNode_;

    // Define the data
    var data = {
    }

    // Render browser component
    this._rootReactNode = ReactDOM.render(
        React.createElement(UiFileUpload, data),
        domCon
    );
}


module.exports = EntityMembersController;

