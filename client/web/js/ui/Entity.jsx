/**
 * Render an entity
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var UiXmlElement = require("./entity/UiXmlElement.jsx");
var Loading = require("./Loading.jsx");
var Chamel = require('chamel');
var AppBar = Chamel.AppBar;
var IconButton = Chamel.IconButton;
var Dialog = Chamel.Dialog;
var netric = require('../base');
var actionModes = require("../entity/actions/actionModes");

/**
 * Module shell
 */
var Entity = React.createClass({

    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        onSaveClick: React.PropTypes.func,
        onPerformAction: React.PropTypes.func,
        onCancelChanges: React.PropTypes.func,
    },

    /**
     * Set initial state for the entity
     */
    getInitialState: function() {
        return { editMode: (this.props.entity.id) ? false : true };
    },

    /**
     * Notify the application if we have changed modes
     */
    componentDidMount: function() {
        // If we are working with a device that supports status bar color, then set
        if (typeof cordova != "undefined" && typeof StatusBar != "undefined") {
            if (cordova.platformId == 'android') {
                // StatusBar.backgroundColorByHexString("#fff");
            }
        }
    },

    render: function() {

        var rightIcons = [];
        var actions = null;

        if (this.state.editMode) {
            actions = this.props.actionHandler.getActions(actionModes.EDIT, [this.props.entity.id]);
        } else {
            actions = this.props.actionHandler.getActions(actionModes.VIEW, [this.props.entity.id]);
        }

        for (var i in actions) {
            rightIcons.push(
                <IconButton
                    iconClassName={actions[i].iconClassName}
                    onClick={this.handleActionClick_.bind(this, actions[i].name)}>
                </IconButton>
            );
        }
        
        var appBar = "";
        var appBarClassName = (this.state.editMode) ? "edit" : "detail";

        // We are just keeping the z-depth flat/0 for now
        //var appBarZDepth = (this.state.editMode) ? 0 : 1;
        var appBarZDepth = 0;

        var appBarTitle = (netric.getApplication().device.size <= netric.Device.sizes.small) ?
            null : this.props.entity.def.title.toUpperCase() + "-" + this.props.entity.id;

        if (this.props.onNavBtnClick) {
            appBar = (
                <AppBar
                    title={appBarTitle}
                    className={appBarClassName}
                    iconClassNameLeft="fa fa-times"
                    zDepth={appBarZDepth}
                    onNavBtnClick={this.navigationClick_}>
                    {rightIcons}
                </AppBar>
            );
        } else {
            appBar = (
                <AppBar
                    title={appBarTitle}
                    zDepth={appBarZDepth}
                    className={appBarClassName}>
                    {rightIcons}
                </AppBar>
            );
        }

        // Get the form
        var xmlData = '<form>' + this.props.form + '</form>';

        // http://api.jquery.com/jQuery.parseXML/
        var xmlDoc = jQuery.parseXML(xmlData);
        var rootFormNode = xmlDoc.documentElement;

        // If the zDepth is 0 then add an hr
        var hr = (appBarZDepth == 0) ? <hr /> : null;

        // Show loading indicator if the entity is not yet loaded for the first time
        var body = null;
        if (this.props.entity.isLoading) {
            body = <Loading />;
        } else {
            // render the UIXML form
            body = (<UiXmlElement
                xmlNode={rootFormNode}
                eventsObj={this.props.eventsObj}
                entity={this.props.entity}
                editMode={this.state.editMode} />);
        }

        // Add confirmation dialog for undoing changes
        var confirmActions = [
          { text: 'Cancel' },
          { text: 'Continue', onClick: this.undoChangesClick_ }
        ];

        return (
            <div>
                <div>
                    {appBar}
                </div>
                {hr}
                <div>
                    {body}
                </div>
                <Dialog 
                    ref='confirm' 
                    title="Cancel Changes" 
                    actions={confirmActions} 
                    modal={true}>
                  This will undo any changes you made.
                </Dialog>
            </div>
        );
    },

    /**
     * The navigation button was clicked
     *
     * @param {Event} evt Event fired
     */
    navigationClick_: function(evt) {

        if (this.state.editMode) {
            // Prompt user to make sure they want to undo their changes
            this.refs.confirm.show();
        }
        else if (this.props.onNavBtnClick) {
            this.props.onNavBtnClick(evt);
        }
    },

    /**
     * Edit mode toggle was clicked
     *
     * @param {Event} evt Event fired
     */
    editModeClick_: function(evt) {
        // Toggle state
        this.setState({
            editMode: (this.state.editMode) ? false : true
        });
    },

    /**
     * The user clicked confirm to undo changes
     *
     * @param {Event} evt Event fired
     */
    undoChangesClick_: function(evt) {
        
        // Hide the dialog
        this.refs.confirm.dismiss();

        // Go back to view mode if we are editing an existing entity
        if (this.props.entity.id)
            this.editModeClick_(null);

        // Notify the parent (probably a controller)
        if (this.props.onCancelChanges) {
            this.props.onCancelChanges();
        }
    },

    /**
     * Save was clicked
     *
     * @param {Event} evt Event fired
     */
    saveClick_: function(evt) {
        // Toggle state
        this.setState({
            editMode: false
        });

        if (this.props.onSaveClick) {
            this.props.onSaveClick();
        }
    },

    /**
     * Handle when an action is clicked from the app/tool bar
     *
     * @param actionName
     * @private
     */
    handleActionClick_: function(actionName) {

        switch (actionName) {
            /*
             * If the action is to move into edit mode then there's no need call
             * the controller since it's only a UI change.
             */
            case "edit":
                this.editModeClick_(null);
                break;
            case "save":
                this.saveClick_(null);
                break;
            default:
                this.props.onPerformAction(actionName);
                break;
        }
    }

});

module.exports = Entity;