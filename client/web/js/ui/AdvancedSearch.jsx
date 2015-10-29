/**
 * Render an advanced search
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var Condition = require('./advancedsearch/Condition.jsx');
var ColumnView = require('./advancedsearch/ColumnView.jsx');
var SaveView = require('./advancedsearch/SaveView.jsx');
var SortOrder = require('./advancedsearch/SortOrder.jsx');
var IconButton = Chamel.IconButton;
var FlatButton = Chamel.FlatButton;

/**
 * Displays the advanced search to filter the results list using conditions. It can also set the sort order and columns to view.
 */
var AdvancedSearch = React.createClass({

	propTypes: {
	    onChangeTitle: React.PropTypes.func,
	    onApplySearch: React.PropTypes.func,
	    onSaveView: React.PropTypes.func,
	    onSaveView: React.PropTypes.func,
	    title: React.PropTypes.string,
	    objType: React.PropTypes.string,
	    browserView: React.PropTypes.object,
	    entityDefinition: React.PropTypes.object
	},

	getDefaultProps: function() {
		return {
			title: "Advanced Search",
		}
	},
	
	getInitialState: function() {
        // Return the initial state
        return { 
            renderCount: 0,
            displaySaveView: false
        };
    },
	
	render: function() {
	    
	    var display = null;
	    
	    if(this.state.displaySaveView) {
	        
	        // Display the save view component
	        display = (
	                <SaveView
	                    browserView={this.props.browserView}
	                    onSave={this._handleSaveView} 
	                    onCancel={this._handleHideSaveDisplay} />
	        );
	    } else { // Display the advance search criteria
	        // Conditions Display
	        var conditionsDiplay = [];
	        var conditions = this.props.browserView.getConditions();
	        for(var idx in conditions) {
	            conditionsDiplay.push( this._getCriteriaDisplay('condition', conditions[idx], idx) );
	        }
	        
	        // Sort Order Display
	        var sortOrderDiplay = [];
	        var orderBy = this.props.browserView.getOrderBy();
	        for(var idx in orderBy) {
	            sortOrderDiplay.push( this._getCriteriaDisplay('sortOrder', orderBy[idx], idx) );
	        }
	        
	        // Columns to View Display
	        var columnViewDiplay = [];
	        var columnView = this.props.browserView.getTableColumns();
	        for(var idx in columnView) {
	            
	            // We need to create a column object since in browserView the columns are stored as a string in an array
	            var column = {fieldName: columnView[idx]};
	            
	            columnViewDiplay.push( this._getCriteriaDisplay('columnView', column, idx) );
	        }
	        
	        // Display the condition, sort by and columns to view
	        display = (
	                <div>
    	                <div>
                            <span className='advance-search-title'>Search Conditions: </span>
                            {conditionsDiplay}
                            <IconButton onClick={this._handleAddCriteria.bind(this, 'condition')} className="fa fa-plus" />
                        </div>
                        <div>
                            <span className='advance-search-title'>Sort By: </span>
                            {sortOrderDiplay}
                            <IconButton onClick={this._handleAddCriteria.bind(this, 'sortOrder')} className="fa fa-plus" />
                        </div>
                        <div>
                            <span className='advance-search-title'>Column View: </span>
                            {columnViewDiplay}
                            <IconButton onClick={this._handleAddCriteria.bind(this, 'columnView')} className="fa fa-plus" />
                        </div>
                        <div>
                            <FlatButton label='Apply' onClick={this._handleAdvancedSearch} />
                            <FlatButton label='Save Changes' onClick={this._handleShowSaveDisplay} />
                        </div>
                    </div>
	        );
	    }
	    
        
		return (
				<div>
					{display}
				</div>
		);
	},
    
    /**
     * Removes the selected criteria
     * 
     * @param {string} type     Type of criteria to be removed
     * @param {int} index       The index of the condition that will be removed
     * @private
     */
    _handleRemoveCondition: function(type, index) {
        switch(type) {
            case 'condition':
                this.props.browserView.removeCondition(index);
                break;
            case 'sortOrder':
                this.props.browserView.removeOrderBy(index);
                break;
            case 'columnView':
                this.props.browserView.removeTableColumn(index);
                break;
        }
        
        // Update the state so it will re-render the changes
        this.setState({
            renderCount: this.state.renderCount+1
        });
    },
    
    /**
     * Adds a new search condition
     *
     * @param {string} type		Type of criteria to be added
     * @private
     */
    _handleAddCriteria: function(type) {
        var field = this.props.entityDefinition.fields[0];
        
        switch(type) {
            case 'condition':
                this.props.browserView.addCondition(field.name);
                break;
            case 'sortOrder':
                this.props.browserView.addOrderBy(field.name, 'asc');
                break;
            case 'columnView':
                this.props.browserView.addTableColumn(field.name);
                break;
        }
        
        // Update the state so it will re-render the changes
        this.setState({
            renderCount: this.state.renderCount+1
        });
    },
    
    /**
     * Executes the advanced search
     *
     * @private
     */
    _handleAdvancedSearch: function() {
        if(this.props.onApplySearch) this.props.onApplySearch(this.props.browserView);
    },
    
    /**
     * Updates the column view using a function in browserView.
     * Only tableColumn has update functionality because columns are saved as a string in an array.
     *
     * @param {string} fieldName    Column name that will be saved based on the index provided
     * @param {int} index           The index of column that will be removed
     * @private
     */
    _handleUpdateColumn: function(fieldName, index) {
        this.props.browserView.updateTableColumn(fieldName, index);
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
     * @private
     */
    _handleShowSaveDisplay: function () {
         this.setState({displaySaveView: true});
     },
    
   /**
    * Saves the advanced search criteria 
    *
    * @private
    */
     _handleSaveView: function () {
        if(this.props.onSaveView) this.props.onSaveView(this.props.browserView)
        
        if(this.props.onChangeTitle) this.props.onChangeTitle("Advanced Search - " + this.props.browserView.name)
    },
    
    /**
     * Gets the fields to be used in search criteria
     *
     * @param {string} selectedField      Field name that is currently selected
     * @return {Array} Returns the field data that will be used to display in the field drop down
     * @private
     */
    _getEntityFieldData: function(selectedField) {
    	if(this.props.entityDefinition == null) {
    		return null;
    	}
    	
    	var fieldData = new Object();
    	
    	fieldData.fields = [];
    	fieldData.selectedIndex = 0;
    	this.props.entityDefinition.fields.map(function(field, index) {
    	    
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
     * Get the criteria component to be displayed. This will display either condition, sort order, or columns to view
     * 
     * @param {string} type         Type of criteria to be displayed
     * @param {object} data         Instance of the criteria object (condition, sort order, column view) that contains data to be displayed
     * @param {int} index           Current index of the condition to be displayed
     * @return {string} Returns the type of criteria to be displayed
     * @private
     */
    _getCriteriaDisplay: function(type, data, index) {
        var display = null;
        var fieldName = data.fieldName || data.field;
        var fieldData = this._getEntityFieldData(fieldName); // Get the entity field data including the field name index (if available)
        var key = fieldName + index.toString();
        var index = parseInt(index);
    	
    	switch(type) {
            case 'condition':
                display = (
                            <Condition
                                key={key}
                                index={index}
                                fieldData={fieldData}
                                objType={this.props.objType}
                                condition={data}
                                onRemove={this._handleRemoveCondition}
                            /> 
                );
                break;
            case 'sortOrder':
                display = (
                            <SortOrder
                                key={key}
                                index={index}
                                fieldData={fieldData}
                                objType={this.props.objType}
                                orderBy={data}
                                onRemove={this._handleRemoveCondition}
                            /> 
                ); 
                break;
            case 'columnView':
                display = (
                            <ColumnView
                                key={key}
                                index={index}
                                fieldData={fieldData}
                                objType={this.props.objType}
                                column={data}
                                onUpdate={this._handleUpdateColumn}
                                onRemove={this._handleRemoveCondition}
                            /> 
                );
                break;
        }
    	
    	return display;
    },
});

module.exports = AdvancedSearch;