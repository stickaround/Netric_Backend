/**
 * Objectsref UIML element
 *
 * @jsx React.DOM
 */
'use strict';

// Load dependencies
var React = require('react');
var CustomEventTrigger = require("../../mixins/CustomEventTrigger.jsx");
var controller = require("../../../controller/controller");

/**
 * Objectsref/entityList element
 */
var Objectsref = React.createClass({

    mixins: [CustomEventTrigger],

    /**
     * Render the brwoser after the component mounts
     */
    componentDidMount: function() {
        // Require EntityBrowserController here so we do not risk a circular dependency
        var EntityBrowserController = require("../../../controller/EntityBrowserController");

        var xmlNode = this.props.xmlNode;
        var name = xmlNode.getAttribute('name');
        var objType = xmlNode.getAttribute('obj_type');
        var refField = xmlNode.getAttribute('ref_field');

        var data = {
            type: controller.types.FRAGMENT,
            objType: objType,
            onEntityClick: function(objType, oid) {
                this.sendEntityClickEvent_(objType, oid);
            }.bind(this)
        }

        // Create browser and render
        var browser = new EntityBrowserController();
        browser.load(data, ReactDOM.findDOMNode(this.refs.bcon), null, function() {
            browser.render();
        });
    },

    /**
     * Render the component
     */
    render: function() {

    	return (
            <div ref="bcon"></div>
        );
        
    },

    /**
     * Trigger a custom event to send back to the entity controller 
     */
    sendEntityClickEvent_: function(objType, oid) {
        this.triggerCustomEvent("entityclick", {objType:objType, id:oid});
    }

});

module.exports = Objectsref;