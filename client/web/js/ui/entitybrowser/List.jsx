/**
 * Render an entity browser
 *

 */
'use strict';

var React = require('react');
var ReactDOM = require("react-dom")
var ListItem = require("./ListItem.jsx");
var ListItemTableRow = require("./ListItemTableRow.jsx");
var Loading = require("../Loading.jsx");
var CommentItem = require("./item/Comment.jsx");
var ActivitytItem = require("./item/Activity.jsx");
var StatusUpdateItem = require("./item/StatusUpdate.jsx");
var WorkflowActionItem = require("./item/WorkflowAction.jsx");
var controller = require("../../controller/controller");
var Device = require("../../Device");

/**
 * Module shell
 */
var List = React.createClass({

    propTypes: {
        onEntityListClick: React.PropTypes.func,
        onEntityListSelect: React.PropTypes.func,
        onLoadMoreEntities: React.PropTypes.func,
        onCreateNewEntity: React.PropTypes.func,
        onRemoveEntity: React.PropTypes.func,
        layout: React.PropTypes.string,
        entities: React.PropTypes.array,
        collectionLoading: React.PropTypes.bool,

        // Instance of Netric/Entity/BrowserView defining which columns a table show show
        browserView: React.PropTypes.object,
        selectedEntities: React.PropTypes.array,

        /**
         * The filters used to display this entity browser list
         *
         * @var {array}
         */
        filters: React.PropTypes.array,

        /**
         * The total number of entities
         *
         * @var {integer}
         */
        entitiesTotalNum: React.PropTypes.number
    },

    getDefaultProps: function () {
        return {
            layout: '',
            entities: [],
            collectionLoading: false,
            selectedEntities: [],
            onRemoveEntity: null
        }
    },

    componentDidMount: function () {

        // Get the current container of the object
        var container = ReactDOM.findDOMNode(this.refs.entityContainer);

        /*
         * Uncomment this to test the div container scrolling
         * container.style.height = "200px";
         * container.style.overflow = "auto";
         */

        var offsetHeight = container.offsetHeight;
        var chrome = /Chrome\//.test(navigator.userAgent);

        // If we are browsing using the chrome browser, then we need to increment the offsetHeight by 2
        if (chrome) {
            offsetHeight += 2;
        }

        /*
         * Determine whether the container has the scrollbar or if the window has the scrollbar
         *
         * Or if the device used is small or mobile, then we will set that the container of the scrollbar is window
         */
        if (container.scrollHeight == offsetHeight
            || netric.getApplication().device.size <= Device.sizes.small) {
            container = window;
        }

        alib.events.listen(container, "scroll", this._handleScroll);

        this.setState({
            loadingFlag: false, // set to false to get new updates when reached at bottom
            scrollContainer: container,
        })

        /*
         * Check if we do not have a scrollbar, then we will try to load more entities
         *  or until the total number of entities has been loaded in the collection
         */
        if (!this._listDisplayHasScrollbar()) {
            this._loadMoreEntitiesUntilScrollbar();
        }
    },

    componentWillUnmount: function () {

        // remove the scroll event that was binded into the container
        alib.events.unlisten(this.state.scrollContainer, "scroll", this._handleScroll);
    },

    render: function () {

        var layout = this.props.layout;

        var entityNodes = this.props.entities.map(function (entity) {
            var item = null;
            var selected = (this.props.selectedEntities.indexOf(entity.id) != -1);

            switch (entity.objType) {

                case "activity":
                    item = (
                        <ActivitytItem
                            key={entity.id}
                            entity={entity}
                            filters={this.props.filters}
                            onRemoveEntity={this.props.onRemoveEntity}
                            onEntityListClick={this.props.onEntityListClick}
                        />
                    )
                    break;
                case "comment":
                    item = (
                        <CommentItem
                            key={entity.id}
                            selected={selected}
                            entity={entity}
                            browserView={this.props.browserView}
                            onRemoveEntity={this.props.onRemoveEntity}
                            onClick={this._sendClick.bind(null, entity.objType, entity.id, entity.getName())}
                            onSelect={this._sendSelect.bind(null, entity.id)}
                        />
                    );
                    break;
                case "status_update":
                    item = (
                        <StatusUpdateItem
                            key={entity.id}
                            entity={entity}
                            onRemoveEntity={this.props.onRemoveEntity}
                            onEntityListClick={this.props.onEntityListClick}
                        />
                    );
                    break;

                case "workflow_action":
                    item = (
                        <WorkflowActionItem
                            key={entity.id}
                            entity={entity}
                            onCreateNewEntity={this.props.onCreateNewEntity}
                            onRemoveEntity={this.props.onRemoveEntity}
                            onEntityListClick={this.props.onEntityListClick}
                        />
                    );
                    break;
                /*
                 * All other object types will either be displayed as a table row
                 * for large devices or a regular detailed item for small or preview mode.
                 */
                default:
                    if (layout === 'table') {
                        item = (
                            <ListItemTableRow
                                key={entity.id}
                                selected={selected}
                                entity={entity}
                                browserView={this.props.browserView}
                                onClick={this._sendClick.bind(null, entity.objType, entity.id, entity.getName())}
                                onSelect={this._sendSelect.bind(null, entity.id)}
                                onRemoveEntity={this.props.onRemoveEntity}
                            />
                        );
                    } else {
                        item = (
                            <ListItem
                                key={entity.id}
                                selected={selected}
                                entity={entity}
                                onClick={this._sendClick.bind(null, entity.objType, entity.id, entity.getName())}
                                onSelect={this._sendSelect.bind(null, entity.id)}
                                onRemoveEntity={this.props.onRemoveEntity}
                            />
                        );
                    }

                    break;
            }

            return item;

        }.bind(this));

        var loadingIndicator = null;

        // Place loading indicator at the bottom of the list if we will display additioal entities
        if (this.props.entities.length && this.props.collectionLoading) {
            loadingIndicator = <Loading
                className="scroll-loading"/>;
        }

        if (layout === 'table') {
            return (
                <div ref="entityContainer" className="entity-browser-list">
                    <table className="entity-browser-list-table">
                        <tbody>
                        {entityNodes}
                        </tbody>
                    </table>
                    {loadingIndicator}
                </div>
            );
        } else {
            return (
                <div ref="entityContainer">
                    {entityNodes}
                    {loadingIndicator}
                </div>
            );
        }
    },

    /**
     * User has clicked/touched an entity in the list
     *
     * @param {string} objType
     * @param {string} oid
     * @param {string} title The textual name or title of the entity
     */
    _sendClick: function (objType, oid, title) {
        if (this.props.onEntityListClick) {
            this.props.onEntityListClick(objType, oid, title);
        }
    },

    /**
     * User has selected an entity (usually a checkbox)
     *
     * @param {string} objType
     * @param {string} oid
     */
    _sendSelect: function (oid) {
        if (this.props.onEntityListSelect) {
            this.props.onEntityListSelect(oid);
        }
    },

    /**
     * This function will trigger if the window or div list container has scrollbar.
     * This will detect if the user has reached the bottom of the page and call loadMoreEntities() to display additional entities
     *
     * @param {DOMEvent} evt Reference to the DOM event being sent
     */
    _handleScroll: function (evt) {

        // This will determine if the scroll has reached the bottom of the page
        var bottomPage = false;

        // Check if the scroll event is coming from the window or from the div list container
        if (this.state.scrollContainer == window) {
            var windowHeight = $(window).height();
            var inHeight = $(document).height();
            var scrollTop = $(window).scrollTop();

            // User reached at bottom
            if (scrollTop >= inHeight - windowHeight) {
                bottomPage = true
            }
        }
        else {
            var offsetHeight = evt.target.offsetHeight;
            var scrollHeight = evt.target.scrollHeight;
            var scrollTop = evt.target.scrollTop;

            // User reached at bottom
            if (offsetHeight + scrollTop == scrollHeight) {
                bottomPage = true;
            }
        }

        // loadingFlag will avoid multiple request and if set to true it will load more entities
        if (!this.state.loadingFlag && bottomPage) {
            this.setState({
                loadingFlag: true,
            });

            this._loadMoreEntities(); // calls the function that will load additional entities
        }
    },

    /**
     * Function that will load more entities and set the loading flag state to false
     *
     * @param opt_callback Optional callback function that will be called after the collection is refreshed
     * @private
     */
    _loadMoreEntities: function (opt_callback) {

        var totalEntitiesNum = this.props.entitiesTotalNum;
        var entitiesNum = this.props.entities.length;

        // If we have already loaded all the entities, then we do not need send a request to the server to load more entities
        if (totalEntitiesNum == entitiesNum) {
            return false;
        }

        // Lets create our own callback function first so we can set the state before we call the argument opt_callback
        var funcFinishedLoading = function () {
            if (this.isMounted()) {
                this.setState({
                    loadingFlag: false, // set to false to get new updates when reached at bottom
                });
            }

            // Now let's call the argument opt_callback function
            if (opt_callback) {
                opt_callback();
            }
        }.bind(this)

        // Function load more entities. The argument 50 will increment the current limit.
        this.props.onLoadMoreEntities(50, funcFinishedLoading);
    },

    /**
     * Function that will load more entities until scrollbar is displayed or if all entities are loaded
     *
     * @private
     */
    _loadMoreEntitiesUntilScrollbar: function () {
        var func = function checkIfWillLoadMore() {

            var hasScrollbar = window.innerWidth > document.documentElement.clientWidth;

            var totalEntitiesNum = this.props.entitiesTotalNum;
            var entitiesNum = this.props.entities.length;

            // If scrollbar is still not yet displayed and we have more entities to load, then repeat this function
            if (!this._listDisplayHasScrollbar()
                && totalEntitiesNum > entitiesNum) {
                this._loadMoreEntitiesUntilScrollbar();
            }
        }.bind(this);

        // Load more entities if we have more entities to load
        this._loadMoreEntities(func);
    },

    /**
     * This function will evaluate the browser list container or the document.body if it has an scrollbar
     *
     * @returns {boolean}
     * @private
     */
    _listDisplayHasScrollbar: function () {

        // Get the current container of the object
        var container = ReactDOM.findDOMNode(this.refs.entityContainer);
        var offsetHeight = container.offsetHeight;

        // If we are browsing using the chrome browser, then we need to increment the offsetHeight by 2
        var chrome = /Chrome\//.test(navigator.userAgent);
        if (chrome) {
            offsetHeight += 2;
        }

        if (container.scrollHeight > offsetHeight
            || document.body.scrollHeight > document.body.offsetHeight) {
            return true;
        }

        return false;
    }
});

module.exports = List;
