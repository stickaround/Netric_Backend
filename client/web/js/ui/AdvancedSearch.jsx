/**
 * Render an advanced search
 *

 */
'use strict';

var React = require('react');
var events = require('../util/events');
var Controls = require('./Controls.jsx');
var Conditions = require('./entity/Conditions.jsx');
var ColumnView = require('./advancedsearch/ColumnView.jsx');
var SaveView = require('./advancedsearch/SaveView.jsx');
var SortOrder = require('./advancedsearch/SortOrder.jsx');
var IconButton = Controls.IconButton;
var FlatButton = Controls.FlatButton;
var Snackbar = Controls.Snackbar;
var AppBar = Controls.AppBar;

/**
 * Displays the advanced search to filter the results list using conditions. It can also set the sort order and columns to view.
 */
var AdvancedSearch = React.createClass({

    propTypes: {

        /**
         * An instance of browserView where we get the condition, orderBy, and columnToView data
         *
         * @type {entity/BrowserView}
         */
        browserView: React.PropTypes.object.isRequired,

        /**
         * The type of object we are implementing the advanced search
         *
         * @type {func}
         */
        objType: React.PropTypes.string.isRequired,

        /**
         * Event triggered when applying the changes of browser view
         *
         * @type {func}
         */
        onApplySearch: React.PropTypes.func,

        /**
         * Event triggered when the user wants to set the current browserView as the default view
         *
         * @type {func}
         */
        onSetDefaultView: React.PropTypes.func,

        /**
         * Event triggered when the brower view will be saved
         *
         * @type {func}
         */
        onSaveView: React.PropTypes.func,

        /**
         * Determine if we should display the appbar which probably means we are not in dialog mode
         *
         * @type {bool}
         */
        showAppBar: React.PropTypes.bool,

        /**
         * Flag that will determine if we are gonna save the view as new view
         *
         * @type {bool}
         */
        eventsObj: React.PropTypes.object
    },

    getInitialState: function () {

        var browserView = this.props.browserView;

        // Return the initial state
        return {
            browserView: browserView,
            conditionData: browserView.getConditions(),
            orderByData: browserView.getOrderBy(),
            columnToViewData: browserView.getTableColumns(),
            displaySaveView: false,
            createNew: false,
            statusText: ''
        };
    },

    componentDidUpdate: function () {

        // Hide the snackbar if the component did re-render
        if (this.state.statusText == '') {
            this.refs.snackbar.dismiss();
        }
    },

    componentDidMount: function() {

        // Event listener for advanced search dialog actions
        events.listen(this.props.eventsObj, "advancedSearchAction", function (evt) {

            switch(evt.data.actionType) {
                case 'applySearch':
                    this._handleApplyAdvancedSearch();
                    break;
                case 'displaySaveView':
                    this._handleShowSaveDisplay(evt.data.createNew);
                    break;
                case 'setDefaultView':
                    this._handleSetDefault();
                    break;
            }
        }.bind(this));
    },

    render: function () {
        var display = null,
            snackbar = null;

        // Set the snackbar for form notifications
        snackbar = <Snackbar ref="snackbar" message={this.state.statusText}/>;

        // Display the save view dialog where the user can input the browserView name, description and isDefault
        if (this.state.displaySaveView) {

            var name = this.state.browserView.name;
            var id = this.state.browserView.id
            var description = this.state.browserView.description;
            var isDefault = this.state.browserView.default;

            // If we are creating a new view, then let's reset the id and description to null values
            if (this.state.createNew) {
                id = null;
                description = null;
                isDefault = true;
            }

            // If the browserView is not yet saved, then set the default name to My Custom View
            if (id === null || this.state.browserView.system) {
                name = 'My Custom View';
            }

            // Display the save view component
            display = (
                <SaveView
                    key={id}
                    id={id}
                    name={name}
                    description={description}
                    default={isDefault}
                    onSave={this._handleSaveView}
                    eventsObj={this.props.eventsObj}/>
            );
        } else { // Display the advance search criteria

            // Conditions Display
            var conditionsDisplay = (
                <Conditions
                    objType={this.props.objType}
                    conditions={this.state.conditionData}
                    onChange={this._handleCriteriaChange.bind(this, 'condition')}
                />
            );

            // Sort Order Display
            var sortOrderDisplay = (
                <SortOrder
                    objType={this.props.objType}
                    orderByData={this.state.orderByData}
                    onChange={this._handleCriteriaChange.bind(this, 'sortOrder')}
                />
            );

            // Columns to View Display
            var columnViewDisplay = (
                <ColumnView
                    objType={this.props.objType}
                    columnToViewData={this.state.columnToViewData}
                    onChange={this._handleCriteriaChange.bind(this, 'columnView')}
                />
            );

            var display = (
                <div>
                    <div>
                        <span className='advanced-search-title'>Search Conditions: </span>
                        {conditionsDisplay}
                    </div>
                    <div>
                        <span className='advanced-search-title'>Sort By: </span>
                        {sortOrderDisplay}
                    </div>
                    <div>
                        <span className='advanced-search-title'>Column View: </span>
                        {columnViewDisplay}
                    </div>
                </div>
            );
        }

        // Show the AppBar if this.props.showAppBar is true (not in a dialog)
        let appBar = null;
        if (this.props.showAppBar) {
            let elementLeft = null;
            if (this.props.onNavBackBtnClick) {
                elementLeft = (
                    <IconButton
                        key="back"
                        iconClassName="fa fa-arrow-left"
                        onClick={this._handleBackClick}/>
                );
            }

            appBar = <AppBar
                fixed={true}
                key="appBarAdvancedSearch"
                title={"Advanced Search"}
                zDepth={0}
                iconElementLeft={elementLeft}>
                <div>
                    <IconButton
                        key="apply"
                        iconClassName="fa fa-check"
                        onClick={this._handleApplyAdvancedSearch}/>
                </div>
            </AppBar>

        }

        return (
            <div className="entity-browser-advancedsearch-container">
                {appBar}
                {display}
                {snackbar}
            </div>
        );
    },

    /**
     * Executes the advanced search
     *
     * @private
     */
    _handleApplyAdvancedSearch: function () {

        var browserView = this.state.browserView;

        // Set the updated condition, orderBy, and ColumnToView data
        browserView.setConditions(this.state.conditionData);
        browserView.setOrderBy(this.state.orderByData);
        browserView.setTableColumns(this.state.columnToViewData);

        if (this.props.onApplySearch) {
            this.props.onApplySearch(browserView);
        }
    },

    /**
     * Hides the save view component
     *
     * @private
     */
    _handleHideSaveDisplay: function () {
        this.setState({displaySaveView: false});
    },

    /**
     * Displays the save view component
     *
     * @param {bool} createNew Flag that will determine, if we are going to create/save a new view
     * @private
     */
    _handleShowSaveDisplay: function (createNew) {
        this.setState({
            displaySaveView: true,
            createNew: createNew
        });
    },

    /**
     * Saves the advanced search criteria
     *
     * @param {object} data Contains the user input details for additional browser view information
     * @private
     */
    _handleSaveView: function (data) {

        var browserView = this.state.browserView;

        // Set the updated condition, orderBy, and ColumnToView data
        browserView.setConditions(this.state.conditionData);
        browserView.setOrderBy(this.state.orderByData);
        browserView.setTableColumns(this.state.columnToViewData);

        // Save the browserView details.
        if (this.props.onSaveView) {
            this.props.onSaveView(browserView, data);
        }

        this.setState({
            displaySaveView: false,
            statusText: data.name + ' view is saved.'
        });

        // Show the status that the view is successfully saved.
        this.refs.snackbar.show();
    },

    /**
     * Fuction that will set the current browserView as the default view
     *
     * @private
     */
    _handleSetDefault: function () {

        // Create a new instance of browserView object using the props.browserView as our base object
        var browserView = this.state.browserView;

        // Always make sure we have an objType set in the browserView before we set it as the default view.
        browserView.setObjType(this.props.objType)

        if(this.props.onSetDefaultView) {
            this.props.onSetDefaultView(browserView);
        }
    },

    /**
     * When the user changes the criteria, handle it here
     *
     * @param {entity/Where[]} conditions Array of where conditions set
     * @private
     */
    _handleCriteriaChange: function (criteria, data) {

        // Update the state based on the criteria that was changed
        switch (criteria) {
            case 'condition':
                this.setState({'conditionData': data});
                break;
            case 'sortOrder':
                this.setState({'orderByData': data});
                break;
            case 'columnView':
                this.setState({'columnToViewData': data});
                break;
        }
    },

    /**
     * The user clicked back in the toolbar/appbar
     *
     * @param {DOMEvent} evt
     * @private
     */
    _handleBackClick: function (evt) {
        if (this.props.onNavBackBtnClick) {
            this.props.onNavBackBtnClick();
        }
    }
});

module.exports = AdvancedSearch;