/**
 * List Item used where object type is 'workflow_action'
 *

 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var Controls = require('../../Controls.jsx');
var Where = require('../../../entity/Where');
var Device = require("../../../Device");
var controller = require("../../../controller/controller");

// Setup controls we will be using
var RaisedButton = Controls.RaisedButton;
var IconButton = Controls.IconButton;
var DropDownIcon = Controls.DropDownIcon;

// Setup Drop-down menu items
var menuItems = [
    { payload: 'create', text: 'Add Child Action'},
    { payload: 'delete', text: 'Delete'}
];

/**
 * List item for an WorkflowAction
 */
var WorkflowAction = React.createClass({

    propTypes: {
        /**
         * Workflow action Entity
         *
         * @type {Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Function that will handle the clicking of object reference link
         *
         * @var {function}
         */
        onEntityListClick: React.PropTypes.func,

        /**
         * Callback used when an individual entity is removed
         *
         * @type {function}
         */
        onRemoveEntity: React.PropTypes.func,

        /**
         * Callback called when a new entity is created
         *
         * @type {function}
         */
        onCreateNewEntity: React.PropTypes.func
    },

    /**
     * Set initial state
     */
    getInitialState: function () {
        return {
            entityController: null
        };
    },

    /**
     * Render the browser after the component mounts
     */
    componentDidMount: function () {
        this._loadActions();
    },

    /**
     * Render the list item
     */
    render: function () {

        let entity = this.props.entity;
        let name = entity.getValue("name");
        let notes = entity.getValue("notes");
        let type = entity.getValue("type_name");

        // Convert type from lower_case to "Upper Case"
        type = type.replace("_", " ");
        type = type.replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g,
            function($1){
                return $1.toUpperCase();
        });

        return (
          <div>
            <div className='entity-browser-item entity-browser-item-cmp'>
                <div className='entity-browser-item-cmp-icon'>
                    <i className="fa fa-cog" />
                </div>
                <div className='entity-browser-item-cmp-body' onClick={this._handleClick}>
                    <div className='entity-browser-item-cmp-header'>
                        {name}
                    </div>
                    <div className='entity-browser-item-cmp-subheader'>
                        {type}
                    </div>
                    <div className='entity-browser-item-cmp-caption'>
                        {notes}
                    </div>
                </div>
                <div className='entity-browser-item-cmp-right'>
                    <DropDownIcon
                        menuItems={menuItems}
                        iconClassName="fa fa-ellipsis-v"
                        onChange={this._handleMenuClick}
                    />
                </div>
            </div>
            <div style={{paddingLeft: "30px"}} ref="bcon"></div>
          </div>
        );
    },

    /**
     * Handles the clicking of object reference link
     *
     * @private
     */
    _handleClick: function () {
        if (this.props.onEntityListClick) {
            this.props.onEntityListClick('workflow_action', this.props.entity.id);
        }
    },

    /**
     * Load the entity browser controller either inline or as dialog for smaller devices
     *
     * @private
     */
    _loadActions: function () {

        // Check if we have already loaded the entity browser controller for this specific objType
        if (this.state.entityController) {
            // Just refresh the results and return
            this.state.entityController.refresh();
            return;
        }

        let entity = this.props.entity;

        // Add filters
        var filters = [];

        // Add filter for workflow id
        var whereCond = new Where("workflow_id");
        whereCond.equalTo(entity.getValue("workflow_id"));
        filters.push(whereCond);

        // Add filter for action id
        var whereActCond = new Where("parent_action_id");
        whereActCond.equalTo(entity.id);
        filters.push(whereActCond);

        var data = {
            type: controller.types.FRAGMENT,
            hideToolbar: true,
            toolbarMode: 'toolbar',
            objType: "workflow_action",
            filters: filters,
            hideNoItemsMessage: true,
            onEntityClick: function (objType, oid) {
              if (this.props.onEntityListClick) {
                  this.props.onEntityListClick(objType, oid);
              }
            }.bind(this),
            onCreateNewEntity: function (objType, opt_data) {
                var data = opt_data || null;
                this._handleAddAction(opt_data);
            }.bind(this)
        }

        // Require EntityBrowserController here so we do not risk a circular dependency
        var EntityBrowserController = require("../../../controller/EntityBrowserController");

        // Create browser and render
        var browser = new EntityBrowserController();
        browser.load(data, ReactDOM.findDOMNode(this.refs.bcon));

        // Update the state objects
        this.setState({
            entityController: browser
        });
    },

    /**
     * Trigger a create new entity event to send back to the entity controller
     *
     * @param {Object} data Optional data to pass to the new entity form
     */
    _handleAddAction: function (opt_data) {
        let data = {
            workflow_id: this.props.entity.getValue("workflow_id"),
            parent_action_id: this.props.entity.id
        }

        if (opt_data) {
            data = opt_data;
        }

        if (this.props.onCreateNewEntity) {
            this.props.onCreateNewEntity(data);
        }
    },

    /**
     * Handle when a user clicks on a menu item from the dropdown menu
     *
     * @param {DomEvent} evt The event triggered
     * @param {string} text The label of the menu item clicked
     * @param {Object} data The menu item
     */
    _handleMenuClick: function(evt, index, data) {
        switch (data.payload) {
            case 'create':
                this._handleAddAction();
                break;
            case 'delete':
                if (this.props.onRemoveEntity) {
                    this.props.onRemoveEntity(this.props.entity.id);
                }
                break;
        }
    }
});

module.exports = WorkflowAction;
