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
	
	getInitialState:function(){
        //setting initiall values
        //attach the scroll event listener to the scroll handler function
    	window.addEventListener("scroll", this.handleScroll);
    	
		return{
			loadingFlag: false,   // to avoid multiple fetch request if user is keep scrolling
		}

	},

    propTypes: {
        onEntityListClick: React.PropTypes.func,
        onEntityListSelect: React.PropTypes.func,
        onLoadMoreEntities: React.PropTypes.func,
        layout : React.PropTypes.string,
        entities: React.PropTypes.array,

        // Instance of Netric/Entity/BrowserView defining which columns a table show show
        browserView: React.PropTypes.object,
        selectedEntities: React.PropTypes.array
    },

    getDefaultProps: function() {
        return {
            layout: '',
            entities: [],
            selectedEntities: []
        }
    },
    
    loadMoreEntities: function(){
		var newLimitIncrement = 50; // This will add the number to the current limit
		
	    //method to update the entities
		this.props.onLoadMoreEntities(newLimitIncrement);
		 
		if (this.isMounted()) {
			this.setState({
	          loadingFlag: false, // set to false to get new updates when reached at bottom
	        });
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
                <div className="entity-browser-list">
                    <table className="entity-browser-list-table">
                        <tbody>
                            {entityNodes}
                        </tbody></table>
                </div>
            );
        } else {
            return (
                <div>
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
        //this function will be triggered if user scrolls
        var windowHeight = $(window).height();
        var inHeight = $(document).height();
        var scrollT = $(window).scrollTop();
        
        if(scrollT >= inHeight - windowHeight ){  //user reached at bottom
        	if(!this.state.loadingFlag){  //to avoid multiple request 
        		this.setState({
        			loadingFlag:true,  
        		});
        		this.loadMoreEntities(); // calls the function that will load additional entities
        	}
        }
    }
    
    

});

module.exports = List;
