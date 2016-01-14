/**
 * Object reference value and browse/select dialog
 *
 * @jsx React.DOM
 * @author Sky Stebnicki
 */
'use strict';

var React = require('react');
var Chamel = require("chamel");
var Dialog = Chamel.Dialog;
var FlatButton = Chamel.FlatButton;
var IconButton = Chamel.IconButton;
var controller = require("../../controller/controller");
var definitionLoader = require("../../entity/definitionLoader");


/**
 * Component that allows a user to select an object/entity
 */
var ObjectSelect = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        // Callback to be fired as soon as a grouping is selected
        onChange: React.PropTypes.func,
        // The object type we are working with
        objType: React.PropTypes.string.isRequired,
        // The grouping field name
        fieldName: React.PropTypes.string.isRequired,
        // The grouping field that we are working on
        field: React.PropTypes.object,
        // The subtype of the field. This is usually set if props.field.subtype is empty.
        subtype: React.PropTypes.string,
        // The current value
        value: React.PropTypes.string,
        // The current label - entity title
        label: React.PropTypes.string,
        // Boolean to indicate an object MUST be selected
        required: React.PropTypes.bool,
    },

    /**
     * Set defaults
     */
    getDefaultProps: function() {
        return {
            label: 'None',
            value: null,
            required: false,
            field: null,
            subType: null
        };
    },

    /**
     * Get the initial state of this componenet
     */
    getInitialState: function() {
        return {
            value: this.props.value,
            label: this.props.label,
        }
    },

    /**
     * Render the component
     */
    render: function () {
        var label = this.state.label || "None";

        // Add clear button
        var clearValue = null;
        if (this.state.value && !this.props.required) {
            clearValue = (
              <IconButton onClick={this._clearValue} tooltip="Clear Value" className="cfi cfi-close" />
            );
        }

        return (
            <span>
                <FlatButton onClick={this._handleBrowseClick}>{label}</FlatButton>
                {clearValue}
            </span>);
    },

    /**
     * The user has clicked browse to select an entity
     *
     * @param {DOMEvent} evt
     * @private
     */
    _handleBrowseClick: function(evt) {

        // If field is already provided in props, then we dont need to get the definition from the loader using the props.fieldName
        if(this.props.field) {
            this._displayBrowser(this.props.field);
        } else {

            // Get the field from the defition
            definitionLoader.get(this.props.objType, function(def) {
                var field = def.getField(this.props.fieldName);
                this._displayBrowser(field);
            }.bind(this));
        }
    },

    /**
     * Display entity browser for selection
     */
    _displayBrowser: function(field) {

        if (!field) {
            throw "Could not load field: " + this.props.fieldName;
        }

        // Get the field subtype
        var subtype = field.subtype;
        if(!subtype) {
            subtype = this.props.subtype;
        }

        // Make sure the field is an object, otherwise fail
        if (field.type != field.types.object && subtype) {
            throw "Field " + field.name + " is not an object/entity reference";
        }

        /*
         * We require it here to avoid a circular dependency where the
         * controller requires the view and the view requires the controller
         */
        var BrowserController = require("../../controller/EntityBrowserController");
        var browser = new BrowserController();
        browser.load({
            type: controller.types.DIALOG,
            title: "Select " + field.title,
            objType: subtype,
            onSelect: function(objType, oid, title) {
                this._handleChange(oid, title);
            }.bind(this)
        });
    },

    /**
     * Trigger a value change
     *
     * @param {int} oid The unique id of the entity selected
     * @param {string} title The human readable title of the entity selected
     * @private
     */
    _handleChange: function(oid, title) {

        this.setState({value: oid, label: title});

        if (this.props.onChange) {
            this.props.onChange(oid, title);
        }
    },

    /**
     * Trigger a value change
     *
     * @param {int} oid The unique id of the entity selected
     * @param {string} title The human readable title of the entity selected
     * @private
     */
    _clearValue: function(oid, title) {

        this.setState({value: null, label: ""});
        this._handleChange(null, null);
    }
});

module.exports = ObjectSelect;