/**
 * Render an advance search
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var SearchCondition = require('./advancedsearch/SearchCondition.jsx');
var SortOrder = require('./advancedsearch/SortOrder.jsx');
var ColumnView = require('./advancedsearch/ColumnView.jsx');
var Dialog = Chamel.Dialog;
var IconButton = Chamel.IconButton;
var FlatButton = Chamel.FlatButton;

var searchCriteria = ['conditions', 'sortOrder', 'columnView'];

/**
 * Module shell
 */
var AdvancedSearch = React.createClass({

	propTypes: {
		layout : React.PropTypes.string,
		title : React.PropTypes.string,
		deviceSize: React.PropTypes.number,
		entity: React.PropTypes.object,
		objType: React.PropTypes.string,
		collectionLoading: React.PropTypes.bool,
		eventsObj: React.PropTypes.object,
	},

	getDefaultProps: function() {
		return {
			layout: '',
			title: "Browser",
		}
	},
	
	getInitialState: function() {
        return { 
        	criteriaCount: [],
        	removedCriteria: []
        	};
    },

	render: function() {
		
		var fields = this._getEntityFields();
		var criteriaDisplay = [];
    	
    	for(var idx in searchCriteria)
    	{	
    		// Get the current criteria
    		var criteria = searchCriteria[idx];
    		
    		// Check if the current criteria has the default values for count and removed entries
    		if(!this.state.criteriaCount[criteria] || !this.state.removedCriteria[criteria]) {
    			this.state.criteriaCount[criteria] = 1;
    			this.state.removedCriteria[criteria] = [];
    		}
    		
    		// Check if the current criteria has the initial value for display
    		if(!criteriaDisplay[criteria]) {
    		    criteriaDisplay[criteria] = [];
    		}
    		
    		var removedCriteria = this.state.removedCriteria[criteria];
    		var count = this.state.criteriaCount[criteria];
    		
    		for(var index=0; index<count; index++) {
        		
        		// Check if the current index criteria is already removed
        		if(removedCriteria.indexOf(index) == -1) {
        		    criteriaDisplay[criteria].push(
        											this._getCriteriaDisplay(criteria, fields, index)
        										);
        		}
        	}	
    	}
    	
		return (
				<div>
					<div>
						<span className='advance-search-title'>Search Conditions: </span>
						{criteriaDisplay['conditions']}
						<IconButton onClick={this._handleAddCriteria.bind(this, 'conditions')} className="fa fa-plus" />
					</div>
					<div>
						<span className='advance-search-title'>Sort By: </span>
						{criteriaDisplay['sortOrder']}
						<IconButton onClick={this._handleAddCriteria.bind(this, 'sortOrder')} className="fa fa-plus" />
					</div>
					<div>
						<span className='advance-search-title'>Column View: </span>
						{criteriaDisplay['columnView']}
						<IconButton onClick={this._handleAddCriteria.bind(this, 'columnView')} className="fa fa-plus" />
					</div>
					<div>
					    <FlatButton label="Apply" onClick={this._handleAdvancedSearch} />
					</div>
				</div>
		);
	},
    
    /**
     * Removes the selected criteria
     *
     * @param {string} criteria		Type of criteria to be removed
     * @param {integer} index		The index to be removed
     * @private
     */
    _handleRemoveCriteria: function(criteria, index) {
    	var removedCriteria = this.state.removedCriteria;
    	
    	removedCriteria[criteria].push(index);
    	
    	this.setState({
    		removedCriteria: removedCriteria
    	});
    },
    
    /**
     * Adds a new search condition
     *
     * @param {string} criteria		Type of criteria to be added
     * @private
     */
    _handleAddCriteria: function(criteria) {
    	var conditionCount = this.state.criteriaCount;
    	
    	conditionCount[criteria] = conditionCount[criteria]+1; 
    	
    	this.setState({
    		conditionCount: conditionCount
    	});
    },
    
    /**
     * Executes the advanced search
     *
     * @private
     */
    _handleAdvancedSearch: function() {
        
        var advanceSearchCriteria = [];
        
        for(var idx in searchCriteria)
        {   
            // Get the current criteria
            var criteria = searchCriteria[idx];
            
            var removedCriteria = this.state.removedCriteria[criteria];
            var count = this.state.criteriaCount[criteria];
            
            if(!advanceSearchCriteria[criteria]) {
                advanceSearchCriteria[criteria] = [];
            }
            
            for(var index=0; index<count; index++) {
                var ref = criteria + index.toString();
                var currentCriteria = this.refs[ref];
                
                // Check if the current index criteria is already removed
                if(removedCriteria.indexOf(index) == -1 && currentCriteria) {
                    advanceSearchCriteria[criteria].push(currentCriteria.getCriteria());
                }
            }   
        }
        
        
        alib.events.triggerEvent(
                this.props.eventsObj,
                "apply_advance_search",
                {criteria: advanceSearchCriteria}
            );
    },
    
    /**
     * Gets the fields to be used in search criteria
     *
     * @private
     */
    _getEntityFields: function() {
    	if(this.props.entity == null) {
    		return null;
    	}
    	
    	//var initialTest = {payload: -1, name: 'note', text: 'Note', type: 'object'};
    	
    	var fields = [];
    	
    	this.props.entity.def.fields.map(function(field) {
    		fields.push({
    						payload: field.id,
    						name: field.name,
    						text: field.title, 
    						type: field.type
    					});
    	});
    	
    	
    	return fields;
    },
    
    /**
     * Get the criteria to be displayed. Either Conditions, SortOrder or ColumnView
     *
     * @param {string} criteria		Type of criteria to be removed
     * @param {array} field			Collection of the field selected information
     * @param {integer} index		The index to be removed
     * @private
     */
    _getCriteriaDisplay: function(criteria, fields, index) {
    	
    	var display = null;
    	var ref = criteria + index.toString();
    	
    	switch(criteria) {
    		case 'conditions':
    		    
    			// Push the search condition component to the array for display
    			display = ( <SearchCondition key={index}
    			                        ref={ref}
    			                        entity={this.props.entity}
    									objType={this.props.objType}
    					    			conditionFields={fields} 
    					    			onRemove={this._handleRemoveCriteria}
    					    			conditionIndex={index} /> );
    			break;
			case 'sortOrder':
    			// Push the sort by component to the array for display
    			display = ( <SortOrder 	key={index}
										objType={this.props.objType}
					    				sortFields={fields} 
					    				onRemove={this._handleRemoveCriteria}
					    				sortIndex={index} /> );
    			break;
			case 'columnView':
    			// Push the sort by component to the array for display
    			display = ( <ColumnView key={index}
										objType={this.props.objType}
					    				viewFields={fields} 
					    				onRemove={this._handleRemoveCriteria}
					    				viewIndex={index} /> );
    			break;
    	}
    	
    	return display;
    }

});

module.exports = AdvancedSearch;