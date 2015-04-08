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

/**
 * Module shell
 */
var Entity = React.createClass({

    /*
    propTypes: {
        errorText: React.PropTypes.string,
        floatingLabelText: React.PropTypes.string,
        hintText: React.PropTypes.string,
        id: React.PropTypes.string,
        multiLine: React.PropTypes.bool,
        onBlur: React.PropTypes.func,
        onChange: React.PropTypes.func,
        onFocus: React.PropTypes.func,
        onKeyDown: React.PropTypes.func,
        onEnterKeyDown: React.PropTypes.func,
        type: React.PropTypes.string
    },
    */

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
                <IconButton iconClassName="fa fa-check" onTouchTap={this.editModeClick_}></IconButton>
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
            </div>
        );
    },

    // The navigation button was clicked
    navigationClick_: function(evt) {
        if (this.state.editMode) {
            this.editModeClick_(null);
        }
        else if (this.props.onNavBtnClick) {
            this.props.onNavBtnClick(evt);
        }
    },

    /**
     * Edit mode toggle was clicked
     */
    editModeClick_: function(evt) {
        // Toggle state
        this.setState({
            editMode: (this.state.editMode) ? false : true
        });
    }

});

module.exports = Entity;