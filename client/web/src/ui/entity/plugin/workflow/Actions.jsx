/**
 * Plugin for displaying actions for a workflow
 *

 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var netric = require("../../../../base");
var EntityConditions = require('../../Conditions.jsx');
var Where = require('../../../../entity/Where');
var Controls = require('../../../Controls.jsx');
var controller = require("../../../../controller/controller");
var CustomEventTrigger = require("../../../mixins/CustomEventTrigger.jsx");
var CustomEventListen = require("../../../mixins/CustomEventListen.jsx");

// Setup controls we will be using
var RaisedButton = Controls.RaisedButton;

/**
 * Manage actions for a workflow
 */
var WorkflowActions = React.createClass({

    mixins: [CustomEventTrigger, CustomEventListen],

    /**
     * Expected props
     */
    propTypes: {

        /**
         * Entity being edited
         *
         * @type {entity\Entity}
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

        this.listenCustomEvent("entityClose", function EntityClosedActCallback () {
            this._loadActions();
        }.bind(this));
    },

    /**
     * Render actions for a workflow
     */
    render: function() {
        if (!this.props.entity.id) {
            return <div>Save changes to the workflow before modifying actions</div>;
        }

        return (
          <div className="container-fluid">
            <div className="row mgb1">
              <div className="col-small-12"  ref="bcon"></div>
            </div>
            <div className="row">
                <div className="col-small-12">
                    <RaisedButton onClick={this._handleAddActionClick} label={"Add Action"} />
                </div>
            </div>
          </div>
        );
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

        // Add filters
        var filters = [];

        // Add filter for workflow id
        var whereCond = new Where("workflow_id");
        whereCond.equalTo(this.props.entity.id);
        filters.push(whereCond);

        // Add filter for action id
        var whereActCond = new Where("parent_action_id");
        whereActCond.equalTo("");
        filters.push(whereActCond);

        var data = {
            type: controller.types.FRAGMENT,
            hideToolbar: true,
            toolbarMode: 'toolbar',
            objType: "workflow_action",
            filters: filters,
            onEntityClick: function (objType, oid) {
                this._sendEntityClickEvent(objType, oid);
            }.bind(this),
            onCreateNewEntity: function (objType, opt_data) {
                var data = opt_data || null;
                this._handleAddAction(opt_data);
            }.bind(this)
        }

        // Require EntityBrowserController here so we do not risk a circular dependency
        var EntityBrowserController = require("../../../../controller/EntityBrowserController");

        // Create browser and render
        var browser = new EntityBrowserController();
        browser.load(data, ReactDOM.findDOMNode(this.refs.bcon));

        // Update the state objects
        this.setState({
            entityController: browser
        });
    },

    /**
     * Trigger a custom event to send back to the entity controller
     */
    _sendEntityClickEvent: function (objType, oid) {
        this.triggerCustomEvent("entityclick", {objType: objType, id: oid});
    },

    /**
     * User clicked on the Add Action button
     *
     * @param {Object} data Optional data to pass to the new entity form
     */
    _handleAddActionClick: function () {
        this._handleAddAction();
    },

    /**
     * Trigger a create new entity event to send back to the entity controller
     *
     * @param {Object} data Optional data to pass to the new entity form
     */
    _handleAddAction: function (opt_data) {
        var params = opt_data || {};
        params["workflow_id"] = this.props.entity.id;
        //params[refField + '_val'] = encodeURIComponent(entityName);

        this.triggerCustomEvent("entitycreatenew", {
            objType: "workflow_action",
            params: params
        });
    }
});

module.exports = WorkflowActions;
