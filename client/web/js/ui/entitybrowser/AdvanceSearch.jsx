/**
 * Advance Search used for browse mode
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var SearchCondition = require('./SearchCondition.jsx');
var Dialog = Chamel.Dialog;
var IconButton = Chamel.IconButton;

/**
 * Module shell
 */
var AdvanceSearch = React.createClass({

    propTypes: {
        title : React.PropTypes.string,
        entityFields: React.PropTypes.array,
        objType: React.PropTypes.string.required,
    },

    getDefaultProps: function() {
        return {
            title: "Advance Search",
        }
    },
    
    getInitialState: function() {
        return { 
        	conditionCount: 1,
        	removedConditions: new Array(),
        	};
    },

    componentDidMount: function() {
    },
    

    render: function() {
    	
    	// Buttons for dialog window
    	var dialogActions = [
    	                     { text: 'Cancel' },
    	                     { text: 'Search', onClick: this._handleDialogSubmit, ref: 'search' }
    	                 ];
    	
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
            	<Dialog ref="linkDialog" title={this.props.title} actions={dialogActions} modal={false} >
        			{searchCondition}
        			<IconButton onClick={this._handleAddCondition} className="fa fa-plus" />
            	</Dialog>
        );
    },
    
    /**
     * Shows the advance search dialog
     *
     * @param {Integer} conditionIndex		The index of the condition to be removed
     * @public
     */
    show: function() {
    	this.refs.linkDialog.show();
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
