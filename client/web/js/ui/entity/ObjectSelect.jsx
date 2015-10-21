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
        // The current value
        value: React.PropTypes.string,
        // The current label - entity title
        label: React.PropTypes.string
    },

    /**
     * Set defaults
     */
    getDefaultProps: function() {
        return {
            label: 'None',
            value: null,
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
        return (<div onClick={this._handleBrowseClick}>{label}</div>);
    },

    /**
     * The user has clicked browse to select an entity
     *
     * @param {DOMEvent} evt
     * @private
     */
    _handleBrowseClick: function(evt) {

        // Get the field from the defition
        definitionLoader.get(this.props.objType, function(def) {
            var field = def.getField(this.props.fieldName);
            this._displayBrowser(field);
        }.bind(this));

    },

    /**
     * Display entity browser for selection
     */
    _displayBrowser: function(field) {

        if (!field) {
            throw "Could not load field: " + this.props.fieldName;
        }

        // Make sure the field is an object, otherwise fail
        if (field.type != field.types.object && field.subtype) {
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
            objType: field.subtype,
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
    }
});

module.exports = ObjectSelect;