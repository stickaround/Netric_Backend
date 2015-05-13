/**
 * Render an entity
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var AppBar = require("./AppBar.jsx");
var UiXmlElement = require("./entity/UiXmlElement.jsx");
var IconButton = require("./IconButton.jsx");
var Dialog = require("./Dialog.jsx");

/**
 * Module shell
 */
var Entity = React.createClass({

    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        onSaveClick: React.PropTypes.func,
        onCancelChanges: React.PropTypes.func,
    },

    /**
     * Set initial state for the entity
     */
    getInitialState: function() {
        return { editMode: (this.props.entity.id) ? false : true };
    },

    render: function() {

        var rightIcons = [];

        if (this.state.editMode) {
            rightIcons.push(
                <IconButton iconClassName="fa fa-check" onTouchTap={this.saveClick_}></IconButton>
            );
        } else {
            rightIcons.push(
                <IconButton iconClassName="fa fa-pencil" onTouchTap={this.editModeClick_}></IconButton>
            );
        }
        
        var appBar = "";
        var appBarClassName = (this.state.editMode) ? "edit" : "detail";
        var appBarZDepth = (this.state.editMode) ? 1 : 0;

        if (this.props.onNavBtnClick) {
            appBar = (<AppBar
                className={appBarClassName}
                iconClassNameLeft="fa fa-times"
                zDepth={appBarZDepth}
                onNavBtnClick={this.navigationClick_}>
                {rightIcons}
            </AppBar>);
        } else {
            appBar = (<AppBar zDepth={appBarZDepth} className={appBarClassName}>{rightIcons}</AppBar>);
        }

        // Get the form
        var xmlData = '<form>' + this.props.form + '</form>';

        // http://api.jquery.com/jQuery.parseXML/
        var xmlDoc = jQuery.parseXML(xmlData);
        var rootFormNode = xmlDoc.documentElement;

        // If the zDepth is 0 then add an hr
        var hr = (appBarZDepth == 0) ? <hr /> : null;

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
                    <UiXmlElement 
                        xmlNode={rootFormNode} 
                        eventsObj={this.props.eventsObj} 
                        entity={this.props.entity}
                        editMode={this.state.editMode} />
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

            // Cromt user to make sure they want to undo their changes
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

        // Go back to view mode
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
    }

});

module.exports = Entity;