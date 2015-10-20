/**
 * Render an advance search
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var SearchCondition = require('./advancesearch/SearchCondition.jsx');
var Dialog = Chamel.Dialog;
var IconButton = Chamel.IconButton;

/**
 * Module shell
 */
var AdvanceSearch = React.createClass({

	propTypes: {
		onPerformAction: React.PropTypes.func,
		layout : React.PropTypes.string,
		title : React.PropTypes.string,
		actionHandler : React.PropTypes.object,
		deviceSize: React.PropTypes.number,
		entityFields: React.PropTypes.array,
		objType: React.PropTypes.string,
	},

	getDefaultProps: function() {
		return {
			layout: '',
			title: "Browser",
		}
	},
	
	getInitialState: function() {
        return { 
        	conditionCount: 1,
        	removedConditions: new Array(),
        	};
    },

	render: function() {
		
		var removedConditions = this.state.removedConditions; // Get the removed conditions
    	var searchCondition = []; // Search Conditions will be stored in an array for additional conditions
    	
    	for(var cIndex=0; cIndex<this.state.conditionCount; cIndex++) {
    		
    		// Check if the current index condition is already removed
    		if(removedConditions.indexOf(cIndex) == -1) {
    			
    			// Push the search condition component to the array for display
    			searchCondition.push( <SearchCondition key={cIndex}
    									objType={this.props.objType}
						    			entityFields={this.props.entityFields} 
						    			onRemove={this._handleRemoveCondition}
						    			conditionIndex={cIndex} /> );
    		}
    	}	
    	
		return (
				<div>
					{searchCondition}
					<IconButton onClick={this._handleAddCondition} className="fa fa-plus" />
				</div>
		);
	},
    
    /**
     * Removes the selected condition
     *
     * @private
     */
    _handleRemoveCondition: function(conditionIndex) {
    	var removedConditions = this.state.removedConditions;
    	
    	removedConditions.push(conditionIndex);
    	
    	this.setState({
    		removedConditions: removedConditions
    	});
    },
    
    /**
     * Adds a new search condition
     *
     * @private
     */
    _handleAddCondition: function() {
    	this.setState({
    		conditionCount: this.state.conditionCount+1
    	});
    }

});

module.exports = AdvanceSearch;