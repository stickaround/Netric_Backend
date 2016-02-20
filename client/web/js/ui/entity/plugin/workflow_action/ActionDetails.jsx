/**
 * The action data details editor
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var netric = require("../../../../base");
var log = require("../../../../log");
var entityLoader = require("../../../../entity/loader");

var _actionTypes = {
    update_field: require("./type/UpdateField.jsx")
};

/**
 * Manage actions for a workflow
 */
var WorkflowActions = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * Entity being edited
         *
         * @type {Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Flag indicating if we are in edit mode or view mode
         *
         * @type {bool}
         */
        editMode: React.PropTypes.bool
    },

    /**
     * We have entered the DOM
     */
    componentDidMount: function() {
        /*
         * Some actions need to know what type of object/entity we are acting on
         * so we need to get that property from the parent workflow of this action.
         */
        if (this.props.entity.getValue('workflow_id')) {
            entityLoader.get("workflow", this.props.entity.getValue('workflow_id'), function(ent) {
                this._handleWorkflowLoaded(ent);
            }.bind(this));
        }
    },

    /**
     * Get the starting state of this component
     */
    getInitialState: function() {
        // We need to know the type of object we are acting on
        return ({objTypeActedOn: null})
    },

    /**
     * Render the component
     */
    render: function() {

        let type = this.props.entity.getValue("type_name");
        let objType = this.state.objTypeActedOn;
        let data = {};

        // If we have not yet loaded the objType, then do not render anything visible
        if (!objType) {
            return (<div />);
        }

        // Set the action data
        if (this.props.entity.getValue("data")) {
            data = JSON.parse(this.props.entity.getValue("data"));
        }

        // Crete the action type component that sets the data
        let typeComponent = null;
        if (_actionTypes[type]) {
            typeComponent = React.createElement(_actionTypes[type], {
                data: data,
                onChange: this._handleDataChange,
                editMode: this.props.editMode,
                objType: objType
            });
        } else {
            log.error("No editor plugin found for action type " + type);
        }
        return (
            <div>
                {typeComponent}
            </div>
        );
    },

    /**
     * Update the data field of the action entity when changed
     *
     * @param data
     * @private
     */
    _handleDataChange: function(data) {
        let encodedData = "";
        if (data) {
            encodedData = JSON.stringify(data);
        }

        this.props.entity.setValue("data", encodedData);
    },

    /**
     * Callback used when the workflow has loaded
     *
     * @param {Entity} workflow The workflow loaded
     * @private
     */
    _handleWorkflowLoaded: function(workflow) {
        this.setState({objTypeActedOn: workflow.getValue("object_type")});
    }
});

module.exports = WorkflowActions;
