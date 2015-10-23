/**
 * Render an advance search
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var Condition = require('./advancedsearch/Condition.jsx');
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
		savedCriteria: React.PropTypes.array,
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
	    var dialogActions = [
	                         { text: 'Cancel' },
	                         { text: 'Save', onClick: this._handleSaveView, ref: 'save' }
	                     ]; 
	    
	    var criteriaDisplay = [];
    	
	    for(var idx in searchCriteria)
	    {	
	        // Get the current criteria
    		var criteria = searchCriteria[idx];
    		
    		// Check if the current criteria has the default values for count and removed entries
    		if(!this.state.criteriaCount[criteria] || !this.state.removedCriteria[criteria]) {
    		    var criteriaCount = 0;
    		    
    		    if(this.props.savedCriteria && this.props.savedCriteria[criteria]) {
    		        criteriaCount = this.props.savedCriteria[criteria].length;
    		    }
    		    
    			this.state.criteriaCount[criteria] = criteriaCount;
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
        											this._getCriteriaDisplay(criteria, index)
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
        alib.events.triggerEvent(
                this.props.eventsObj,
                "apply_advance_search",
                {criteria: this._buildSearchCriteria()}
            );
    },
    
    /**
     * Displays the save view dialog. 
     *
     * @private
     */
    _handleShowViewDialog: function() {
        this.refs.saveViewDialog.show()
    },
    
   /**
    * Saves the advanced search criteria 
    *
    * @private
    */
    _handleSaveView: function () {
        alib.events.triggerEvent(
                this.props.eventsObj,
                "save_advance_search",
                {
                    criteria: this._buildSearchCriteria(),
                    name: this.refs.viewName.getValue()
                }
            );
    },
    
    /**
     * Builds the criteria for saving
     *
     * @private
     */
    _buildSearchCriteria: function() {
        var advanceSearchCriteria = [];
        
        for(var idx in searchCriteria)
        {   
            
            var criteria = searchCriteria[idx]; // Get the current criteria name
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
        
        return advanceSearchCriteria;
    },
    
    /**
     * Gets the fields to be used in search criteria
     *
     * @param {string} selectedField      Field name that is currently selected
     * @private
     */
    _getEntityFieldData: function(selectedField) {
    	if(this.props.entity == null) {
    		return null;
    	}
    	
    	//var initialTest = {payload: -1, name: 'note', text: 'Note', type: 'object'};
    	var fieldData = new Object();
    	
    	fieldData.fields = [];
    	fieldData.selectedIndex = 0;
    	this.props.entity.def.fields.map(function(field, index) {
    	    
    	    if(field.name == selectedField) {
    	        fieldData.selectedIndex = parseInt(index);
    	    }
    	    
    	    fieldData.fields.push({
    						payload: field.name,
    						id: field.id,
    						name: field.name,
    						text: field.title, 
    						type: field.type
    					});
    	});
    	
    	
    	
    	return fieldData;
    },
    
    /**
     * Get the criteria to be displayed. Either Conditions, SortOrder or ColumnView
     *
     * @param {string} criteria		Type of criteria to be removed
     * @param {array} field			Collection of the field selected information
     * @param {integer} index		The index to be removed
     * @private
     */
    _getCriteriaDisplay: function(criteria, index) {
    	
    	var display = null;
    	var savedData = null;
    	var selectedField = null;
    	var ref = criteria + index.toString();
    	
    	// Get the saved criteria
    	if(this.props.savedCriteria && this.props.savedCriteria[criteria]) {
    	    savedData = this.props.savedCriteria[criteria][index];
    	    
    	    // Check if saved data is already available or if the criteria is about to be added
    	    if(savedData) {
    	        selectedField = savedData.fieldName;
    	    }
    	}
    	
    	var fieldData = this._getEntityFieldData(selectedField); // Get the entity field data including the saved field index (if available)
    	
    	switch(criteria) {
    		case 'conditions':
    		    
    			// Push the search condition component to the array for display
    			display = ( <Condition key={index}
    			                        ref={ref}
    			                        entity={this.props.entity}
    									objType={this.props.objType}
    			                        fieldData={fieldData} 
    					    			onRemove={this._handleRemoveCriteria}
    					    			index={index}
    			                        savedCondition={savedData} /> );
    			break;
    		case 'sortOrder':
    			// Push the sort by component to the array for display
    			display = ( <SortOrder 	key={index}
    			                        ref={ref}
										objType={this.props.objType}
    			                        fieldData={fieldData} 
					    				onRemove={this._handleRemoveCriteria}
					    				index={index}
    			                        savedOrder={savedData} /> );
    			break;
    		case 'columnView':
    			// Push the sort by component to the array for display
    			display = ( <ColumnView key={index}
    			                        ref={ref}
										objType={this.props.objType}
    			                        fieldData={fieldData} 
					    				onRemove={this._handleRemoveCriteria}
					    				index={index}
    			                        savedColumn={savedData} /> );
    			break;
    	}
    	
    	return display;
    }

});

module.exports = AdvancedSearch;