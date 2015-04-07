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

        var appBar = "";


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
        

        if (this.props.onNavBtnClick) {
            appBar = (<AppBar
                iconClassNameLeft="fa fa-times"
                onNavBtnClick={this.navigationClick_}>
                {rightIcons}
            </AppBar>);
        } else {
            appBar = (<AppBar>{rightIcons}</AppBar>);
        }

        // Get the form
        var xmlData = '<form>' + this.props.form + '</form>';

        // http://api.jquery.com/jQuery.parseXML/
        var xmlDoc = jQuery.parseXML(xmlData);
        var rootFormNode = xmlDoc.documentElement;

        return (
            <div>
                <div>
                    {appBar}
                </div>
                <div>
                    <UiXmlElement 
                        xmlNode={rootFormNode} 
                        eventsObj={this.props.eventsObj} 
                        entity={this.props.entity}
                        editMode={this.state.editMode}
                    />
                </div>
            </div>
        );
    },

    // The navigation button was clicked
    navigationClick_: function(evt) {
        if (this.props.onNavBtnClick)
            this.props.onNavBtnClick(evt);
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