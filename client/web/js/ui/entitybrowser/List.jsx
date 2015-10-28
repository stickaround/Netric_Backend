/**
 * Render an entity browser
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ListItem = require("./ListItem.jsx");
var ListItemTableRow = require("./ListItemTableRow.jsx");
var Loading = require("../Loading.jsx");
var CommentItem = require("./item/Comment.jsx");

/**
 * Module shell
 */
var List = React.createClass({
	
    propTypes: {
        onEntityListClick: React.PropTypes.func,
        onEntityListSelect: React.PropTypes.func,
        onLoadMoreEntities: React.PropTypes.func,
        layout : React.PropTypes.string,
        entities: React.PropTypes.array,
        collectionLoading: React.PropTypes.bool,

        // Instance of Netric/Entity/BrowserView defining which columns a table show show
        browserView: React.PropTypes.object,
        selectedEntities: React.PropTypes.array,
    },

    getDefaultProps: function() {
        return {
            layout: '',
            entities: [],
            collectionLoading: false,
            selectedEntities: []
        }
    },
    
    _loadMoreEntities: function() {
    	
		// Function load more entities. The argument 50 will increment the current limit. 
		this.props.onLoadMoreEntities(50);
		
		if (this.isMounted()) {
			this.setState({
				loadingFlag: false, // set to false to get new updates when reached at bottom
			});
		}
    },
	
	componentDidMount: function() {
		
		// Get the current container of the object
		var container = React.findDOMNode(this.refs.entityContainer);
		
		/*
		 * Uncomment this to test the div container scrolling
		 * container.style.height = "200px";
		 * container.style.overflow = "auto";
		 */ 
		 
		// If the window container has the scroll bars
		if(container.scrollHeight == container.offsetHeight){
			container = window;
		}
		
		alib.events.listen(container, "scroll", this._handleScroll);
		
		this.setState({
			loadingFlag: false, // set to false to get new updates when reached at bottom
			scrollContainer: container,
		})
	},
	
	componentWillUnmount: function() {
		
		// remove the scroll event that was binded into the container
		alib.events.unlisten(this.state.scrollContainer, "scroll", this._handleScroll);
	},

    render: function() {

        var layout = this.props.layout;
    	
        var entityNodes = this.props.entities.map(function(entity) {
            var item = null;
            var selected = (this.props.selectedEntities.indexOf(entity.id) != -1);

            switch (entity.objType) {

                case "activity":
                    // TODO: add activity
                    break;
                case "comment":
                    // TODO: add comment
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
                /*
                 * All other object types will either be displayed as a table row
                 * for large devices or a regular detailed item for small or preview mode.
                 */
                default:
                    if (this.props.layout == 'table') {
                        item = <ListItemTableRow
                            key={entity.id}
                            selected={selected}
                            entity={entity}
                            browserView={this.props.browserView}
                            onClick={this._sendClick.bind(null, entity.objType, entity.id, entity.getName())}
                            onSelect={this._sendSelect.bind(null, entity.id)} />;
                    } else {
                        item = <ListItem
                            key={entity.id}
                            selected={selected}
                            entity={entity}
                            onClick={this._sendClick.bind(null, entity.objType, entity.id, entity.getName())}
                            onSelect={this._sendSelect.bind(null, entity.id)} />;
                    }

                    break;
            }

            return item;

        }.bind(this));
        
        var loadingIndicator = null;
        
        // Place loading indicator at the bottom of the list if we will display additioal entities
        if (this.props.entities.length && this.props.collectionLoading) {
        	loadingIndicator = <Loading
        	className="scroll-loading" />;
        }

        if (layout === 'table') {
            return (
                <div ref="entityContainer" className="entity-browser-list">
                    <table className="entity-browser-list-table">
                        <tbody>
                            {entityNodes}
                        </tbody></table>
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
    _sendClick: function(objType, oid, title) {
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
    _sendSelect: function(oid) {
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
    _handleScroll: function(evt) {
    	
    	// This will determine if the scroll has reached the bottom of the page
    	var bottomPage = false;
    	
    	// Check if the scroll event is coming from the window or from the div list container
    	if(this.state.scrollContainer == window) {
    		var windowHeight = $(window).height();
    		var inHeight = $(document).height();
    		var scrollTop = $(window).scrollTop();
            
    		// User reached at bottom
    		if(scrollTop >= inHeight - windowHeight) {
    			bottomPage = true
    		}
    	}
    	else{
    		var offsetHeight = evt.target.offsetHeight;
    		var scrollHeight = evt.target.scrollHeight;
    		var scrollTop = evt.target.scrollTop;
            
    		// User reached at bottom
    		if(offsetHeight + scrollTop == scrollHeight) {
    			bottomPage = true;
    		}
    	}
    	
    	// loadingFlag will avoid multiple request and if set to true it will load more entities
    	if(!this.state.loadingFlag && bottomPage) { 
    		this.setState({
    			loadingFlag:true,  
    		});
    		
    		this._loadMoreEntities(); // calls the function that will load additional entities
    	}
    }
});

module.exports = List;
