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
var AdvancedSearchController = function () {
};

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
AdvancedSearchController.prototype._rootReactNode = null;

/**
 * The instance of the current entity/browserView
 *
 * @private
 * @type {entity/BrowserView}
 */
AdvancedSearchController.prototype._browserView = null;

/**
 * Flag that will determine if we are showing the save view
 *
 * @private
 * @type {boolean}
 */
AdvancedSearchController.prototype._showSaveView = false;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
AdvancedSearchController.prototype.onLoad = function (opt_callback) {

    var callbackWhenLoaded = opt_callback || null;

    if (this.getType() == controller.types.DIALOG) {
        this.props.dialogActions = [
            {
                text: 'Apply',
                onClick: function() {
                    this.props.onApplySearch(this._browserView);
                }.bind(this)
            },
            {
                text: 'Save as New',
                onClick: function() {
                    this._showSaveView = true;
                    this.render();
                }.bind(this)
            },
            {
                text: 'Set as Default',
                onClick: function() {
                    browserViewSaver.setDefaultView(this._browserView);
                }.bind(this)
            },
            {
                text: 'Cancel',
                onClick: function() { this.close(); }.bind(this)
            }
        ];
    }

    if (callbackWhenLoaded) {
        callbackWhenLoaded();
    } else {
        this.render();
    }
}

/**
 * Render this controller into the dom tree
 */
AdvancedSearchController.prototype.render = function () {
    // Set outer application container
    var domCon = this.domNode_;
    var entities = new Array();
    var entityFields = new Array();
    let showAppBar = (this.getType() == controller.types.PAGE);

    this._browserView = this.props.browserView;

    // Define the data
    var data = {
        title: this.props.title || "Advanced Search",
        objType: this.props.objType,
        browserView: this._browserView,
        showSaveView: this._showSaveView,
        currentDialogAction: this._currentDialogAction,
        onApplySearch: function (browserView) {
            this.props.onApplySearch(browserView);
        }.bind(this),
        onSaveView: function (browserView, browserViewdata) {
            this._saveView(browserView, browserViewdata);
        }.bind(this),
        onSetDefaultView: function (browserView) {
            browserViewSaver.setDefaultView(browserView);
        }.bind(this),
        showAppBar: showAppBar,
        onNavBackBtnClick: this.props.onNavBackBtnClick || null
    }

    // Render browser component
    this._rootReactNode = ReactDOM.render(
        React.createElement(UiAdvancedSearch, data),
        domCon
    );

    // Reset the showSaveView to false
    this._showSaveView = false;
}

/**
 * Save the browser view
 *
 * @param {entity/BrowserView} browserView The browser view to save
 * @param {object} data Contains the user input details for additional browser view information
 */
AdvancedSearchController.prototype._saveView = function (browserView, data) {

    browserView.setId(data.id);
    browserView.name = data.name;
    browserView.description = data.description;
    browserView.default = data.default;


    // Check if the browserView is system generated, then we will set the id to null to generate a new id for the custom view
    if (browserView.system) {

        // Set the browserView.id to null so we can set a new id value after saving.
        browserView.setId(null);
    }

    // When saving the view, make sure that we set the system to false
    browserView.system = false;

    // Setup the callback function
    var callbackFunc = function (browserView) {
        if (this.props.onSave) {
            this.props.onSave(browserView);
        }
    }.bind(this);

    // After saving the browserView, then let's re-render so it will
    browserViewSaver.save(browserView, callbackFunc);
}

module.exports = AdvancedSearchController;

