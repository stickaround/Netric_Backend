/**
 * Advance Search used for browse mode
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var groupingLoader = require("../../entity/groupingLoader");
var ObjectSelect = require("../entity/ObjectSelect.jsx");
var DropDownMenu = Chamel.DropDownMenu;
var TextField = Chamel.TextField;
var IconButton = Chamel.IconButton;

var bLogicMenu = [
                { payload: 'and', text: 'And' },
                { payload: 'or', text: 'Or' },
            ];

var boolInputMenu = [
              { payload: 'true', text: 'true' },
              { payload: 'false', text: 'false' },
          ];

/**
 * Module shell
 */
var SearchCondition = React.createClass({

    propTypes: {
        onRemove: React.PropTypes.func,
        fieldData: React.PropTypes.object,
        index: React.PropTypes.number,
        objType: React.PropTypes.string.isRequired,
        entity: React.PropTypes.object,
        savedCondition: React.PropTypes.object,
    },

    getInitialState: function() {
        var searchFields = this.props.fieldData.fields;
        var field = null;
        
        // Set the default field entry based on the selected index
        if(searchFields) {
            field = searchFields[this.props.fieldData.selectedIndex];
        }
        
        // Set the condition operators based on the initial value of field condition
        if(searchFields) {
            this._getConditionOperators(field)
        }
        
        // Set the default value for search condition
        var bLogic = bLogicMenu[0].payload;
        var fieldName = field.name
        var operator = this.operators[0].payload;
        var value = null;
        
        // if savedCondition is set, then we will override the default search condition
        if(this.props.savedCondition) {
            bLogic = this.props.savedCondition.bLogic;
            fieldName = this.props.savedCondition.fieldName;
            operator = this.props.savedCondition.operator;
            value = this.props.savedCondition.value;
        }
        
        // Get the condition valueInput based on the initial value of field condition
        if(searchFields) {
            this._getConditionValueInput(field, this.props.fieldData.selectedIndex, value);
        }
        
        // Return the initial state
        return {
            bLogic: bLogic, 
            fieldName: fieldName, 
            operator: operator, 
            value: value,
            selectedField: this.props.fieldData.selectedIndex,
            selectedbLogic: this._getSelectedIndex(bLogicMenu, bLogic),
            selectedOperator: this._getSelectedIndex(this.operators, operator),
        	};
    },

    render: function() {
        return (
        		<div className="row" key={this.props.index}>
					<div className="col-small-1">
	    				<DropDownMenu 
	    				        menuItems={bLogicMenu} 
	    				        selectedIndex={parseInt(this.state.selectedbLogic)} 
	    				        onChange={this._handleCriteriaClick.bind(this, 'bLogic')} />
					</div>
	    			<div className="col-small-4">
	    				<DropDownMenu 
	    				        menuItems={this.props.fieldData.fields} 
	    				        selectedIndex={parseInt(this.state.selectedField)} 
	    				        onChange={this._handleFieldClick} />
					</div>
					<div className="col-small-4" >
						<DropDownMenu 
						        menuItems={this.operators} 
						        selectedIndex={parseInt(this.state.selectedOperator)} 
						        onChange={this._handleCriteriaClick.bind(this, 'operator')} />
					</div>
					<div className="col-small-2">
						{this.valueInput}
					</div>
					<div className="col-small-1">
						<IconButton 
						        onClick={this._handleRemoveCondition.bind(this, this.props.index)} 
						        className="fa fa-times" />
					</div>
				</div>
        );
    },
    
    /**
     * Callback used to handle commands when user selects the blogic/operator dropdown
     *
     * @param {string} type     Type of criteria that was changed
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @param {Integer} key     The index of the menu clicked
     * @param {array} field     The object value of the menu clicked
     * @private
     */
    _handleCriteriaClick: function(type, e, key, field) {
        switch(type) {
            case 'bLogic':
                this.setState({
                    bLogic: field.payload,
                    selectedbLogic: key
                });
                break;
            case 'operator':
                this.setState({
                    operator: field.payload,
                    selectedOperator: key
                });
                break;
        }
    },
    
    /**
     * Callback used to handle commands when user blurs on the input text
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @private
     */
    _handleInputBlur: function(e) {
        this.setState({
            value: e.target.value
        });
    },
    
    /**
     * Callback used to handle commands when user selects a value in the dropdown input value
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @param {Integer} key     The index of the menu clicked
     * @param {array} field The object value of the menu clicked
     * @private
     */
    _handleValueSelect: function(e, key, field) {
        this.setState({
            value: field.payload
        });
    },

    /**
     * Callback used to handle commands when user selects a field name in the condition search
     *
     * @param {DOMEvent} e 		Reference to the DOM event being sent
     * @param {Integer} key		The index of the menu clicked
     * @param {array} field	The object value of the menu clicked
     * @private
     */
    _handleFieldClick: function(e, key, field) {
    	this._getConditionValueInput(field, key, null);
    },

    /**
     * Removes the search criteria
     *
     * @param {Integer} index		The index of the condition to be removed
     * @private
     */
    _handleRemoveCondition: function (index) {
    	if(this.props.onRemove) this.props.onRemove('conditions', index);
    },
    
    /**
     * Get the groupings data of the field selected
     *
     * @param {array} field	Collection of the field selected information
     * @private
     */
    _getGroupingsValueInput: function(field) {
    	var fieldName = field.name;
    	
    	// Make sure the groupings cache object is initialized for this object
        if (!this._groupingLoaders) {
            this._groupingLoaders = {};
        }
        
        // If the groups is already saved in the cache
        if (this._groupingLoaders[fieldName]) {
            var groupings = this._groupingLoaders[fieldName];
            this._createGroupingsMenu(groupings, field);
        }
        else {
            /* We really only want to setup the groupings once because we
             * will be calling this function any time a change is made to the
             * groupings and we do not want to add additional listeners.
             */
            groupingLoader.get(this.props.objType, fieldName, function(groupings) {
            	this._createGroupingsMenu(groupings, field);
            	
            	// Cache grouping so we do not try to set it up again with listeners
            	this._groupingLoaders[fieldName] = groupings;
            }.bind(this));            
        }
    },
    
    
    /**
     * Get the search condition input input type based on the field type selected
     *
     * @param {array} field	            Collection of the field selected information
     * @param {Integer} fieldIndex      The index of the menu field clicked
     * @param {string} value            The default value or initial value
     * @private
     */
    _getConditionValueInput: function(field, fieldIndex, value) {
    	var valueInput = null;
    	var fieldType = field.type;
    	var updateState = true; // Determine if we need to update the state.
    	
    	switch(fieldType) {
        	case 'fkey':
            case 'fkey_multi':
                updateState = false; // We do not need to update the state since it will be handled in _getGroupingsValueInput() function
                
                this._getGroupingsValueInput(field);
                break;
                
            case 'object':
                var fieldValue = this.props.entity.getValue(field.name);
                
                var valueInput = (<ObjectSelect
                                    onChange={this._handleSetValue}
                                    objType={this.props.objType}
                                    fieldName={field.name}
                                    value={fieldValue}
                                    label={fieldValue}
                                    />)
                break;
                
    		case 'bool':
    		    if(value == null) {
    		        value = boolInputMenu[0].payload;
    		    }
    		    
    			valueInput = ( <DropDownMenu onChange={this._handleValueSelect} selectedIndex={ ( value.toString() === 'true' ? 0 : 1 )} menuItems={boolInputMenu} /> )
    			break;
    			
    		default:
    		    valueInput = ( <TextField onBlur={this._handleInputBlur} hintText="Search" value={value} /> )
    			break;
    	}
    	
    	if(valueInput) {
    	    this.valueInput = valueInput;
    	}   
    	
    	// Update the state if the component is already mounted
    	if(this.isMounted() && updateState) {
    	    this._getConditionOperators(field); // Update the operators dropdown
    	    
    	    this.setState({
                fieldName: field.name,
                value: value,
                selectedField: fieldIndex,
                selectedOperator: 0, // Set the operator's index to 0
            });
    	}
    },
    
    /**
     * Get the groupings data of the field selected
     *
     * @param {array} field	Collection of the field selected information
     * @private
     */
    _getGroupingsValueInput: function(field) {
    	var fieldName = field.name;
    	
    	// Make sure the groupings cache object is initialized for this object
        if (!this._groupingLoaders) {
            this._groupingLoaders = {};
        }
        
        if (this._groupingLoaders[fieldName]) {
            var groupings = this._groupingLoaders[fieldName];
            this._createGroupingsMenu(groupings, field);
        }
        else {
            /* We really only want to setup the groupings once because we
             * will be calling this function any time a change is made to the
             * groupings and we do not want to add additional listeners.
             */
            groupingLoader.get(this.props.objType, fieldName, function(groupings) {
            	this._createGroupingsMenu(groupings, field);
            	
            	// Cache grouping so we do not try to set it up again with listeners
            	this._groupingLoaders[fieldName] = groupings;
            }.bind(this));            
        }
    },
    
    /**
     * Get the groupings data of the field selected
     *
     * @param {array} groupings		Groupings data based on the selected conditions field and current object type
     * @param {array} field		Collection of the field selected information
     * @private
     */
    _createGroupingsMenu: function(groupings, field)
    {
    	var groups = [];
    	
    	groupings.groups.map(function(group) {
    		groups.push({
							payload: group.id,
							text: group.name,
						});
    	});
    	
    	this._getConditionOperators(field);
    	this.valueInput = ( <DropDownMenu onChange={this._handleValueSelect} selectedIndex={0} menuItems={groups} /> );
    	
    	this.setState({
            fieldName: field.name,
            operator: this.operators[0].payload,
            value: groups[0].payload
        });
    },
    
    /**
     * Get the search condition operator based on the field type selected
     *
     * @param {array} field	Collection of the field selected information
     * @private
     */
    _getConditionOperators: function(field) {
    	var operators = null;
    	var fieldType = field.type;
    	
    	switch(fieldType) {
	        case 'fkey_multi':
	        case 'fkey':
	            var operators = [
	                                {payload: "is_equal", text: "is equal to"},
	                                {payload: "is_not_equal", text: "is not equal to"}
	                            ];    
	            break;
	        case 'number':
	        case 'real':
	        case 'integer':
	            var operators = [
	                                {payload: "is_equal", text: "is equal to"},
	                                {payload: "is_not_equal", text: "is not equal to"},
	                                {payload: "is_greater", text: "is greater than"},
	                                {payload: "is_less", text: "is less than"},
	                                {payload: "is_greater_or_equal", text: "is greater than or equal to"},
	                                {payload: "is_less_or_equal", text: "is less than or equal to"},
	                                {payload: "begins_with", text: "begins with"}
	                            ];
	            break;
	        case 'date':
	        case 'timestamp':
	            var operators = [
	                                {payload: "is_equal", text: "is equal to"},
	                                {payload: "is_not_equal", text: "is not equal to"},
	                                {payload: "is_greater", text: "is greater than"},
	                                {payload: "is_less", text: "is less than"},
	                                {payload: "day_is_equal", text: "day is equal to"},
	                                {payload: "month_is_equal", text: "month is equal to"},
	                                {payload: "year_is_equal", text: "year is equal to"},
	                                {payload: "is_greater_or_equal", text: "is greater than or equal to"},
	                                {payload: "is_less_or_equal", text: "is less than or equal to"},
	                                {payload: "last_x_days", text: "within last (x) days"},
	                                {payload: "last_x_weeks", text: "within last (x) weeks"},
	                                {payload: "last_x_months", text: "within last (x) months"},
	                                {payload: "last_x_years", text: "within last (x) years"},
	                                {payload: "next_x_days", text: "within next (x) days"},
	                                {payload: "next_x_weeks", text: "within next (x) weeks"},
	                                {payload: "next_x_months", text: "within next (x) months"},
	                                {payload: "next_x_years", text: "within next (x) years"}
	                            ];
	            break;
	        case 'bool':
	            var operators = [
	                                {payload: "is_equal", text: "is equal to"},
	                                {payload: "is_not_equal", text: "is not equal to"}
	                            ];
	            break;
	        default: // Text
	            var operators = [
	                                {payload: "is_equal", text: "is equal to"},
	                                {payload: "is_not_equal", text: "is not equal to"},
	                                {payload: "begins_with", text: "begins with"},
	                                {payload: "contains", text: "contains"}
	                            ];
	            break;
    	}
    	
    	this.operators = operators;
    },
    
    /**
     * Gets the index of the saved field/operator/blogic value
     *
     * @param {array} data      Array of data that will be mapped to get the index of the saved field/operator/blogic value
     * @param {array} value     The value that will be used to get the index
     * @private
     */
    _getSelectedIndex: function(data, value) {
        var index = 0;
        for(var idx in data) {
            if(data[idx].payload == value) {
                index = idx;
                break;
            }
        }
        
        return index;
    },
    
    /**
     * Returns the condition set
     *
     * @public
     */
    getCriteria: function() {
        var condition = {
                bLogic: this.state.bLogic, 
                fieldName: this.state.fieldName,
                operator: this.state.operator, 
                value: this.state.value
        }
        
        return condition;
    }
});

module.exports = SearchCondition;
