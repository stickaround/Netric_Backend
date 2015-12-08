/**
 * Handles the rendering a status update form
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var controller = require('../../../controller/controller');
var Where = require("../../../entity/Where");
var Chamel = require('chamel');
var DropDownMenu = Chamel.DropDownMenu;
var TextField = Chamel.TextField;
var FlatButton = Chamel.FlatButton;
var IconButton = Chamel.IconButton;

/**
 * Render Status into an entity form
 */
var StatusUpdate = React.createClass({

    propTypes: {
        entity: React.PropTypes.object,

        /**
         * Object type to be displayed in the entity browser.
         *
         * Possible values are: activity, status_update
         */
        objTypeList: React.PropTypes.string
    },

    getDefaultProps: function () {
        return {
            objTypeList: 'status_update'
        }
    },

    getInitialState: function () {

        // Return the initial state
        return {
            viewMenuData: null,
            browser: null
        };
    },

    componentDidMount: function () {
        this._loadStatusUpdates();
    },

    render: function () {
        var viewDropdownDisplay = null;

        if (this.state.viewMenuData) {
            viewDropdownDisplay = (
                <DropDownMenu
                    menuItems={this.state.viewMenuData}
                    selectedIndex={0}
                    onChange={this._handleFilterChange}/>
            );
        }

        return (
            <div className="entity-comments">
                <div className="entity-comments-form">
                    <div className="entity-comments-form-center">
                        <TextField ref="statusInput" hintText="Add Status" multiLine={true}/>
                    </div>
                    <div className="entity-comments-form-right">
                        <FlatButton
                            label="Send"
                            iconClassName="fa fa-paper-plane"
                            onClick={this._handleStatusSend}
                            />
                    </div>
                </div>

                {viewDropdownDisplay}

                <div ref='statusUpdateContainer'></div>
            </div>
        );
    },

    /**
     * Handles the sending of status updates
     *
     * @private
     */
    _handleStatusSend: function () {
        var status = this.refs.statusInput.getValue();
        var StatusUpdateManager = require("../../../entity/StatusUpdateManager");

        // Set the object reference
        StatusUpdateManager.objReference = this.props.entity.objType + ":" + this.props.entity.id;

        // Send the status
        StatusUpdateManager.send(status, null, this._loadStatusUpdates);

        // Clear the status input
        this.refs.statusInput.clearValue();
    },

    /**
     * Callback used to handle the changing of filter dropdown
     *
     * @param {DOMEvent} e          Reference to the DOM event being sent
     * @param {int} key             The index of the menu clicked
     * @param {array} menuItem      The object value of the menu clicked
     * @private
     */
    _handleFilterChange: function (e, key, menuItem) {
        this._loadStatusUpdates(menuItem.conditions)
    },

    /**
     * Load the EntityBrowserController to display the status updates for this entity
     *
     * @param {entity.Where[]} conditions      These are the conditions that will limit/filter the status updates
     * @private
     */
    _loadStatusUpdates: function (conditions) {
        var inlineCon = ReactDOM.findDOMNode(this.refs.statusUpdateContainer);
        var BrowserController = require('../../../controller/EntityBrowserController');
        var browser = this.state.statusUpdateBrowser;

        // Add filter to only show status updates from the referenced object
        var filter = new Where('associations');
        filter.equalTo(this.props.entity.objType + ":" + this.props.entity.id);

        // If conditions is not set, then we create a blank conditions array
        if (!conditions) {
            conditions = [];
        }

        // Set the reference filter in the conditions
        conditions.push(filter);

        // Check if entity browser is not yet set
        if (!browser) {
            browser = new BrowserController();

            // Lets create a callback function to set the statusUpdate view dropdown once entity browser has been loaded
            var callbackFunc = function () {

                // We dont need to get statusUpdate views if it is already set.
                if (!this.state.viewMenuData) {
                    var statusUpdateViews = browser.getEntityDefinition().getViews();
                    this._setViewMenuData(statusUpdateViews);
                }
            }

            // Load the entity browser
            browser.load({
                type: controller.types.FRAGMENT,
                title: "Status Update",
                objType: this.props.objTypeList,
                hideToolbar: true,
                filters: conditions
            }, inlineCon, null, callbackFunc.bind(this));

            this.setState({statusUpdateBrowser: browser});
        } else {

            // If entity browser is already set, then lets just update the filters and refresh the results
            browser.updateFilters(conditions);
        }
    },

    /**
     * Set the statusUpdate view menu data in the state
     *
     * @param {array} statusUpdateViews     The statusUpdate view data from entity definition
     * @private
     */
    _setViewMenuData: function (statusUpdateViews) {
        var viewMenu = [];

        for (var idx in statusUpdateViews) {
            var view = statusUpdateViews[idx];

            viewMenu.push({
                text: view.name,
                conditions: view.getConditions()
            });
        }

        this.setState({viewMenuData: viewMenu});
    }
    
    
});

module.exports = StatusUpdate;
