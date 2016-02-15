/**
 * Render an entity browser
 *
 * @jsx React.DOM
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
var controller = require("../../controller/controller");

/**
 * Module shell
 */
var List = React.createClass({

    propTypes: {
        onEntityListClick: React.PropTypes.func,
        onEntityListSelect: React.PropTypes.func,
        onLoadMoreEntities: React.PropTypes.func,
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
            selectedEntities: []
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

        /*
         * Determine whether the contaier has the scrollbar or if the window has the scrollbar
         *
         * We need to set a threshold when compairing the scrollHeight to offsetHeight
         * Sometimes, there's a slight difference between scroll and offset heights.
         * For example, in Google Chrome, the scrollHeight is higher than the offsetHeight by 2
         */
        var offsetHeight = container.offsetHeight;
        if (container.scrollHeight >= offsetHeight &&
            container.scrollHeight <= (offsetHeight + 2)) {
            container = window;
        }

        alib.events.listen(container, "scroll", this._handleScroll);

        this.setState({
            loadingFlag: false, // set to false to get new updates when reached at bottom
            scrollContainer: container,
        })

        var hasScrollbar = (window.innerWidth > document.documentElement.clientWidth);
        if (!hasScrollbar) {
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
                            onEntityListClick={this.props.onEntityListClick}
                        />
                    )
                    break;
                /*
                 * All other object types will either be displayed as a table row
                 * for large devices or a regular detailed item for small or preview mode.
                 */
                default:
                    if (layout === 'table') {
                        item = <ListItemTableRow
                            key={entity.id}
                            selected={selected}
                            entity={entity}
                            browserView={this.props.browserView}
                            onClick={this._sendClick.bind(null, entity.objType, entity.id, entity.getName())}
                            onSelect={this._sendSelect.bind(null, entity.id)}/>;
                    } else {
                        item = <ListItem
                            key={entity.id}
                            selected={selected}
                            entity={entity}
                            onClick={this._sendClick.bind(null, entity.objType, entity.id, entity.getName())}
                            onSelect={this._sendSelect.bind(null, entity.id)}/>;
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

            var totalEntitiesNum = this.props.entitiesTotalNum;
            var entitiesNum = this.props.entities.length;

            // Only load more entities if we still have entities to load
            if(totalEntitiesNum > entitiesNum) {
                this._loadMoreEntities(); // calls the function that will load additional entities
            }
        }
    },

    /**
     * Function that will load more entities and set the loading flag state to false
     *
     * @param opt_callback Optional callback function that will be called after the collection is refreshed
     * @private
     */
    _loadMoreEntities: function (opt_callback) {

        // Function load more entities. The argument 50 will increment the current limit.
        this.props.onLoadMoreEntities(50, opt_callback);

        if (this.isMounted()) {
            this.setState({
                loadingFlag: false, // set to false to get new updates when reached at bottom
            });
        }
    },

    /**
     * Function that will load more entities until scrollbar is displayed or if all entities are loaded
     *
     * @private
     */
    _loadMoreEntitiesUntilScrollbar: function() {
        var func = function checkIfWillLoadMore() {
            var totalEntitiesNum = this.props.entitiesTotalNum;
            var entitiesNum = this.props.entities.length;
            var hasScrollbar = window.innerWidth > document.documentElement.clientWidth;

            // If scrollbar is still not yet displayed and we have more entities to load, then repeat this function
            if(!hasScrollbar && totalEntitiesNum > entitiesNum) {
                this._loadMoreEntitiesUntilScrollbar();
            }
        }.bind(this);

        this._loadMoreEntities(func);
    }
});

module.exports = List;
