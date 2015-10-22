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

var bLogic = [
                { payload: 'and', text: 'And' },
                { payload: 'or', text: 'Or' },
            ];

var boolInputType = [
              { payload: 'true', text: 'true' },
              { payload: 'false', text: 'false' },
          ];

/**
 * Module shell
 */
var SearchCondition = React.createClass({

    propTypes: {
    	onRemove: React.PropTypes.func,
        conditionFields: React.PropTypes.array,
        conditionIndex: React.PropTypes.number,
        objType: React.PropTypes.string.isRequired,
        entity: React.PropTypes.object,
    },

    getInitialState: function() {
        return { 
        	operators: null,
        	inputType: null
        	};
    },

    componentDidMount: function() {
    	
    	var searchFields = this.props.conditionFields;
    	
    	// Get the conditions input type if the initial field type is fkey/object
    	if(searchFields[0].type == 'fkey') {
    		this._getGroupingsInputType(searchFields[0]);
    	}
    },

    render: function() {
    	
    	var operators = this.state.operators;
    	var inputType = this.state.inputType;
    	var searchFields = this.props.conditionFields;
    	var field = null;
    	
    	// Get the first entry of the condition field
    	if(searchFields) {
    		field = searchFields[0];
    	}
    	
    	// If operators are not set, then get the condition operators based on the initial value of field condition
    	if(operators == null && searchFields) {
    		operators = this._getConditionOperators(field)
    	}
    	
    	// If inputTypes are not set, then get the condition inputTypes based on the initial value of field condition
    	if(inputType == null && searchFields) {
    		inputType = this._getConditionInputType(field);
    	}
    	
    	// Set the default condition values
    	if(!this._condition) {
    	    this._condition = {
    	                        bLogic: bLogic[0].payload, 
    	                        fieldName: field.name, 
    	                        operator: operators[0].payload, 
    	                        value: null
    	                        };
    	}
    		
        return (
        		<div className="row" key={this.props.conditionIndex}>
					<div className="col-small-1">
	    				<DropDownMenu menuItems={bLogic} onChange={this._handleCriteriaClick.bind(this, 'bLogic')} />
					</div>
	    			<div className="col-small-4">
	    				<DropDownMenu menuItems={searchFields} onChange={this._handleFieldClick} />
					</div>
					<div className="col-small-4" >
						<DropDownMenu menuItems={operators} onChange={this._handleCriteriaClick.bind(this, 'operator')} />
					</div>
					<div className="col-small-2">
						{inputType}
					</div>
					<div className="col-small-1">
						<IconButton onClick={this._handleRemoveCondition.bind(this, this.props.conditionIndex)} className="fa fa-times" />
					</div>
				</div>
        );
    },
    
    /**
     * Callback used to handle commands when user selects the blogic dropdown
     *
     * @param {string} type     Type of criteria that was changed
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @param {Integer} key     The index of the menu clicked
     * @param {array} field     The object value of the menu clicked
     * @private
     */
    _handleCriteriaClick: function(type, e, key, field) {
        this._condition[type] = field.payload;
        
        console.log(this._condition);
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
        
        // Update the condition fieldname
        this._condition.fieldName = field.name;
        
    	switch(field.type)
    	{
    		case 'fkey':
    		case 'fkey_multi':
    			this._getGroupingsInputType(field);
    			break;
    		case 'object':
    			var fieldValue = this.props.entity.getValue(field.name);
    	        
    			var inputType = (<ObjectSelect
                                    onChange={this._handleSetValue}
                                    objType={this.props.objType}
                                    fieldName={field.name}
                                    value={fieldValue}
                                    label={fieldValue}
                                    />)
    			
    			
    			this.setState({
    	    		inputType: inputType
    	    	});
    			break;
    		default:
    			this.setState({
    	    		operators: this._getConditionOperators(field),
    	    		inputType: this._getConditionInputType(field)
    	    	});
    			break;
    	}
    },

    /**
     * Removes the search criteria
     *
     * @param {Integer} conditionIndex		The index of the condition to be removed
     * @private
     */
    _handleRemoveCondition: function (conditionIndex) {
    	if(this.props.onRemove) this.props.onRemove('conditions', conditionIndex);
    },
    
    _handleInputText: function(e) {
        console.log(e);
    },
    
    /**
     * Get the groupings data of the field selected
     *
     * @param {array} field	Collection of the field selected information
     * @private
     */
    _getGroupingsInputType: function(field) {
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
     * Get the search condition input input type based on the field type selected
     *
     * @param {array} field	Collection of the field selected information
     * @private
     */
    _getConditionInputType: function(field) {
    	var inputType = null;
    	var fieldType = field.type;
    	
    	switch(fieldType) {
    		case 'bool':
    			inputType = ( <DropDownMenu menuItems={boolInputType} /> )
    			break;
    		default:
    		    inputType = ( <TextField ref="inputType" hintText="Search" /> )
    			break;
    	}
    	
    	return inputType;
    },
    
    /**
     * Get the groupings data of the field selected
     *
     * @param {array} field	Collection of the field selected information
     * @private
     */
    _getGroupingsInputType: function(field) {
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
    	
		this.setState({
			operators: this._getConditionOperators(field),
    		inputType: ( <DropDownMenu menuItems={groups} /> )
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
    	
    	return operators;
    },
    
    /**
     * Returns the condition set
     *
     * @public
     */
    getCriteria: function() {
        var value = null;
        switch(this.state.type) {
            case 'bool':
                break;
            default:
                //value = this.state.inputType.type.prototype.getValue();
                value = this.refs.inputType.getValue();
                break;
        }
        
        // Set the value of the condition
        this._condition.value = value;
        
        return this._condition;
    }
});

module.exports = SearchCondition;
