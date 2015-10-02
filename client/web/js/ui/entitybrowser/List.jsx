/**
 * Render an entity browser
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ListItem = require("./ListItem.jsx");
var ListItemTableRow = require("./ListItemTableRow.jsx");

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
        routePath: React.PropTypes.string,

        // Instance of Netric/Entity/BrowserView defining which columns a table show show
        browserView: React.PropTypes.object,
        selectedEntities: React.PropTypes.array
    },

    getDefaultProps: function() {
        return {
            layout: '',
            routePath: '',
            entities: [],
            selectedEntities: []
        }
    },
    
    loadMoreEntities: function(){
    	var newLimitIncrement = 50; // This will add the number to the current limit
		
		// method to update the entities
		this.props.onLoadMoreEntities(newLimitIncrement);
		 
		if (this.isMounted()){
			this.setState({
				loadingFlag: false, // set to false to get new updates when reached at bottom
			});
		}
    },
	
	componentDidMount: function(){
		// Get the current container of the object
		var container = document.getElementById(this.props.routePath);
		
		/*
		 * Uncomment this to test the div container scrolling
		 * container.style.height = "200px";
		 * container.style.overflow = "auto";
		 */
		
		// If the div container has scroll bars
		if(container.scrollHeight > container.offsetHeight)
		{
			container.addEventListener("scroll", this.handleScroll);
		}
		else{
			window.addEventListener("scroll", this.handleScroll);
		}
		
		this.setState({
			loadingFlag: false, // set to false to get new updates when reached at bottom
		})
	},
	
	componentWillUnmount: function(){
		var container = document.getElementById(this.props.routePath);
		
		if(container.scrollHeight > container.offsetHeight)
		{
			container.removeEventListener("scroll", this.handleScroll);
		}
		else{
			window.removeEventListener('scroll', this.handleScroll, false);
		}
	},

    render: function() {
    	
        var entityNodes = this.props.entities.map(function(entity) {
            var item = null;
            var selected = (this.props.selectedEntities.indexOf(entity.id) != -1);

            switch (entity.objType) {

                case "activity":
                    // TODO: add activity
                    break;
                case "comment":
                    // TODO: add comment
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
                            onClick={this._sendClick.bind(null, entity.objType, entity.id)}
                            onSelect={this._sendSelect.bind(null, entity.id)} />;
                    } else {
                        item = <ListItem
                            key={entity.id}
                            selected={selected}
                            entity={entity}
                            onClick={this._sendClick.bind(null, entity.objType, entity.id)}
                            onSelect={this._sendSelect.bind(null, entity.id)} />;
                    }

                    break;
            }

            return item;

        }.bind(this));

        if (this.props.layout == 'table') {
            return (
                <div onscroll="handleScroll(this)" className="entity-browser-list">
                    <table className="entity-browser-list-table">
                        <tbody>
                            {entityNodes}
                        </tbody></table>
                </div>
            );
        } else {
            return (
                <div onscroll="handleScroll(this)">
                    {entityNodes}
                </div>
            );
        }
    },

    /**
     * User has clicked/touched an entity in the list
     *
     * @param {string} objType
     * @param {string} oid
     */
    _sendClick: function(objType, oid) {
        if (this.props.onEntityListClick) {
            this.props.onEntityListClick(objType, oid);
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
     * Handles the scroll event
     *
     */
    handleScroll:function(e){
    	
    	var bottomPage = false;
    	
    	// Check if the scroll event is coming from the div object or from the window
    	if(typeof e.target.id !== "undefined" && e.target.id == this.props.routePath)
    	{
    		var offsetHeight = e.target.offsetHeight;
            var scrollHeight = e.target.scrollHeight;
            var scrollTop = e.target.scrollTop;
            
            //user reached at bottom
            if(offsetHeight + scrollTop == scrollHeight){
            	bottomPage = true;
            }
    	}
    	else{
    		var windowHeight = $(window).height();
            var inHeight = $(document).height();
            var scrollT = $(window).scrollTop();
            
            //user reached at bottom
            if(scrollT >= inHeight - windowHeight ){
            	bottomPage = true
            }
    	}
    	
    	// to avoid multiple request
    	if(!this.state.loadingFlag && bottomPage){ 
    		this.setState({
    			loadingFlag:true,  
    		});
    		this.loadMoreEntities(); // calls the function that will load additional entities
    	}
    }
});

module.exports = List;
