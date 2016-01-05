/**
 * Objectsref UIML element
 *
 * @jsx React.DOM
 */
'use strict';

// Load dependencies
var React = require('react');
var ReactDOM = require('react-dom');
var CustomEventTrigger = require("../../mixins/CustomEventTrigger.jsx");
var controller = require("../../../controller/controller");
var Where = require("../../../entity/Where");

/**
 * Objectsref/entityList element
 */
var Objectsref = React.createClass({

    mixins: [CustomEventTrigger],

    getInitialState: function () {

        // Return the initial state
        return {
            /**
             * The reference field for this entity
             *
             * @type {string}
             */
            refField: null,

            /**
             * The reference value for reference field
             *
             * @type {string}
             */
            refFieldValue: null,
        };
    },

    /**
     * Render the browser after the component mounts
     */
    componentDidMount: function () {
        if (this.props.entity.id) {
            this._displayObjectBrowser(this.props);
        }
    },

    /**
     * Render the entity browser after receiving new props
     */
    componentWillReceiveProps: function (nextProps) {
        if (nextProps.entity.id) {
            this._displayObjectBrowser(nextProps);
        }
    },

    /**
     * Render the component
     */
    render: function () {

        var note = null;
        if (!this.props.entity.id) {
            note = "Please save changes to view more details.";
        }

        return (
            <div ref="bcon">{note}</div>
        );
    },

    /**
     * Trigger a custom event to send back to the entity controller
     */
    _sendEntityClickEvent: function (objType, oid) {

        if (oid == 'new') {

            // If we have refField is set, then add it in the query parameters
            if (this.state.refField) {
                oid += '?' + this.state.refField + '=' + this.state.refFieldValue;
                oid += '&' + this.state.refField + '_val=' + encodeURIComponent(this.state.entityName);

            }
        }

        this.triggerCustomEvent("entityclick", {objType: objType, id: oid});
    },

    /**
     * Display the object browser in the div with ref='bcon'
     *
     * @param {object} sourceProps      The props object that we will use to evaluate xmlNode
     * @private
     */
    _displayObjectBrowser: function (sourceProps) {

        // Require EntityBrowserController here so we do not risk a circular dependency
        var EntityBrowserController = require("../../../controller/EntityBrowserController");

        // Get the objType that will be used for object browser
        var objType = sourceProps.xmlNode.getAttribute('obj_type');
        var refField = sourceProps.xmlNode.getAttribute('ref_field');
        var refFieldValue = sourceProps.entity.getValue(this.refField) || sourceProps.entity.id;
        var entityName = sourceProps.entity.getValue('name');

        // Add filter for to reference current entity
        var filters = [];
        if (this.refField && this.refFieldValue) {

            // Create a filter reference
            var filter = new Where(this.refField);

            filter.equalTo(this.refFieldValue);
            filters.push(filter);
        }

        var data = {
            type: controller.types.FRAGMENT,
            hideAppBar: true,
            hideToolbar: false,
            objType: objType,
            eventsObj: this.props.eventsObj,
            filters: filters,
            onEntityClick: function (objType, oid) {
                this._sendEntityClickEvent(objType, oid);
            }.bind(this)
        }

        // Create browser and render
        var browser = new EntityBrowserController();
        browser.load(data, ReactDOM.findDOMNode(this.refs.bcon));

        // Update the state
        this.setState({
            refField: refField,
            refFieldValue: refFieldValue,
            entityName: entityName
        });
    }

});

module.exports = Objectsref;