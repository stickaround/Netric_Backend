/**
 * @fileoverview Advanced Search
 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom");
var netric = require("../base");
var controller = require("./controller");
var events = require("../util/events");
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

AdvancedSearchController.prototype._eventsObj = {};

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
AdvancedSearchController.prototype.onLoad = function (opt_callback) {

    var callbackWhenLoaded = opt_callback || null;

    this._browserView = this.props.browserView;

    if (this.getType() == controller.types.DIALOG) {
        this.props.dialogActions = [];

        // Dialog Action for applying the advance search
        this.props.dialogActions.push({
            text: 'Apply',
            onClick: function () {

                // Trigger the event that will apply the advanced search
                events.triggerEvent(this._eventsObj, 'advancedSearchAction', {actionType: 'applySearch'});
            }.bind(this)
        });

        // Dialog Action for saving the view as new view
        this.props.dialogActions.push({
            text: 'Save as New View',
            onClick: function () {

                // Function that will trigger the display of save view dialog and set the create new as true
                this._displaySaveViewDialogAction(true);
            }.bind(this)
        });

        // If this browserView has an id, then let's display the button that can set the browserView as the default view
        if (this._browserView.id) {

            // Dialog Action for setting the current view as the default view
            this.props.dialogActions.push({
                text: 'Set as Default',
                onClick: function () {
                    browserViewSaver.setDefaultView(this._browserView);
                }.bind(this)
            });

            // If this browserView is not system generated, then let's display a button that can save the changes made by the user
            if (!this._browserView.system) {
                this.props.dialogActions.push({
                    text: 'Save Changes',
                    onClick: function () {

                        // Function that will trigger the display of save view dialog and set the create new as false
                        this._displaySaveViewDialogAction(false);
                    }.bind(this)
                });
            }
        }

        this.props.dialogActions.push({
            text: 'Cancel',
            onClick: function () {
                this.close();
            }.bind(this)
        });
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

    // Define the data
    var data = {
        title: this.props.title || "Advanced Search",
        objType: this.props.objType,
        browserView: this._browserView,
        eventsObj: this._eventsObj,
        onApplySearch: function (browserView) {
            this.props.onApplySearch(browserView);
        }.bind(this),
        onSaveView: function (browserView, browserViewdata) {
            this._saveView(browserView, browserViewdata);
        }.bind(this),
        showAppBar: showAppBar,
        onNavBackBtnClick: this.props.onNavBackBtnClick || null
    }

    // Render browser component
    this._rootReactNode = ReactDOM.render(
        React.createElement(UiAdvancedSearch, data),
        domCon
    );
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

/**
 * Display the Save View form of Advanced Search in the dialog window
 *
 * @param createNew Flag that will determine if we are creating a new custom view.
 *                  Set to false, if we are just saving the changes for the current view
 * @private
 */
AdvancedSearchController.prototype._displaySaveViewDialogAction = function (createNew) {

    var prevProps = [],
        newProps = [];

    // Set the current props.dialogActions as our previous props.dialogActions
    prevProps.dialogActions = this.props.dialogActions;

    // Setup the new dialog action buttons for Save View Dialog
    newProps.dialogActions = [
        {
            text: 'Save',
            onClick: function () {

                // Trigger the event that will save the view
                events.triggerEvent(this._eventsObj, 'saveView', {});

                this.setProps(prevProps);
            }.bind(this)
        },
        {
            text: 'Cancel',
            onClick: function () {
                this.setProps(prevProps);
            }.bind(this)
        }
    ];

    // Set the newProps to display the new dialog action buttons for Save View Dialog
    this.setProps(newProps);

    // Trigger the event that we will display the save view dialog
    events.triggerEvent(this._eventsObj, 'advancedSearchAction',
        {
            actionType: 'displaySaveView',
            createNew: createNew
        });
}

module.exports = AdvancedSearchController;

