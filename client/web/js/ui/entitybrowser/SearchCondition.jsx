/**
 * Advance Search used for browse mode
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var groupingLoader = require("../../entity/groupingLoader");
var Dialog = Chamel.Dialog;
var DropDownMenu = Chamel.DropDownMenu;
var TextField = Chamel.TextField;
var IconButton = Chamel.IconButton;

var bLogic = [
                { payload: '&&', text: 'And' },
                { payload: '||', text: 'Or' },
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
        entityFields: React.PropTypes.array,
        conditionIndex: React.PropTypes.number,
        objType: React.PropTypes.string.required,
    },

    getInitialState: function() {
        return { 
        	operators: null,
        	inputType: null
        	};
    },

    componentDidMount: function() {
    	
    	var searchFields = this._getConditionFields();
    	
    	// Get the conditions input type if the initial field type is fkey/object
    	if(searchFields[0].type == 'fkey') {
    		this._getGroupingsInputType(searchFields[0]);
    	}
    },

    render: function() {
    	
    	var operators = this.state.operators;
    	var inputType = this.state.inputType;
    	var searchFields = this._getConditionFields();
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
    		
        return (
        		<div className="row" key={this.props.conditionIndex}>
					<div className="col-small-1">
	    				<DropDownMenu  
	                    	menuItems={bLogic} />
					</div>
	    			<div className="col-small-4">
	    				<DropDownMenu menuItems={searchFields} onChange={this._handleFieldClick} />
					</div>
					<div className="col-small-4" >
						<DropDownMenu menuItems={operators} />
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
     * Callback used to handle commands when user selects a field name in the condition search
     *
     * @param {DOMEvent} e 		Reference to the DOM event being sent
     * @param {Integer} key		The index of the menu clicked
     * @param {Object} field	The object value of the menu clicked
     * @private
     */
    _handleFieldClick: function(e, key, field) {
    	switch(field.type)
    	{
    		case 'fkey':
    		case 'fkey_multi':
    			this._getGroupingsInputType(field);
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
    	if(this.props.onRemove) this.props.onRemove(conditionIndex);
    },
    
    /**
     * Gets the fields to be used in search criteria
     *
     * @private
     */
    _getConditionFields: function() {
    	if(this.props.entityFields == null) {
    		return null;
    	}
    	
    	var initialTest = {payload: -1, name: 'groups', text: 'groups', type: 'fkey'};
    	
    	var fields = [initialTest];
    	
    	this.props.entityFields.map(function(field) {
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
     * Get the search condition input input type based on the field type selected
     *
     * @param {object} field	Collection of the field selected information
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
    			inputType = ( <TextField hintText="Search" /> )
    			break;
    	}
    	
    	return inputType;
    },
    
    /**
     * Get the groupings data of the field selected
     *
     * @param {object} field	Collection of the field selected information
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
        	var cls = this;
            groupingLoader.get(this.props.objType, fieldName, function(groupings) {
            	cls._createGroupingsMenu(groupings, field);
            	
            	// Cache grouping so we do not try to set it up again with listeners
            	cls._groupingLoaders[fieldName] = groupings;
            });            
        }
    },
    
    /**
     * Get the groupings data of the field selected
     *
     * @param {array} groupings		Groupings data based on the selected conditions field and current object type
     * @param {object} field		Collection of the field selected information
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
     * @param {object} field	Collection of the field selected information
     * @private
     */
    _getConditionOperators: function(field) {
    	var operators = null;
    	var fieldType = field.type;
    	
    	switch(fieldType) {
	        case 'fkey_multi':
	        case 'fkey':
	            var operators = [
	                                {paylod: "is_equal", text: "is equal to"},
	                                {paylod: "is_not_equal", text: "is not equal to"}
	                            ];    
	            break;
	        case 'number':
	        case 'real':
	        case 'integer':
	            var operators = [
	                                {paylod: "is_equal", text: "is equal to"},
	                                {paylod: "is_not_equal", text: "is not equal to"},
	                                {paylod: "is_greater", text: "is greater than"},
	                                {paylod: "is_less", text: "is less than"},
	                                {paylod: "is_greater_or_equal", text: "is greater than or equal to"},
	                                {paylod: "is_less_or_equal", text: "is less than or equal to"},
	                                {paylod: "begins_with", text: "begins with"}
	                            ];
	            break;
	        case 'date':
	        case 'timestamp':
	            var operators = [
	                                {paylod: "is_equal", text: "is equal to"},
	                                {paylod: "is_not_equal", text: "is not equal to"},
	                                {paylod: "is_greater", text: "is greater than"},
	                                {paylod: "is_less", text: "is less than"},
	                                {paylod: "day_is_equal", text: "day is equal to"},
	                                {paylod: "month_is_equal", text: "month is equal to"},
	                                {paylod: "year_is_equal", text: "year is equal to"},
	                                {paylod: "is_greater_or_equal", text: "is greater than or equal to"},
	                                {paylod: "is_less_or_equal", text: "is less than or equal to"},
	                                {paylod: "last_x_days", text: "within last (x) days"},
	                                {paylod: "last_x_weeks", text: "within last (x) weeks"},
	                                {paylod: "last_x_months", text: "within last (x) months"},
	                                {paylod: "last_x_years", text: "within last (x) years"},
	                                {paylod: "next_x_days", text: "within next (x) days"},
	                                {paylod: "next_x_weeks", text: "within next (x) weeks"},
	                                {paylod: "next_x_months", text: "within next (x) months"},
	                                {paylod: "next_x_years", text: "within next (x) years"}
	                            ];
	            break;
	        case 'bool':
	            var operators = [
	                                {paylod: "is_equal", text: "is equal to"},
	                                {paylod: "is_not_equal", text: "is not equal to"}
	                            ];
	            break;
	        default: // Text
	            var operators = [
	                                {paylod: "is_equal", text: "is equal to"},
	                                {paylod: "is_not_equal", text: "is not equal to"},
	                                {paylod: "begins_with", text: "begins with"},
	                                {paylod: "contains", text: "contains"}
	                            ];
	            break;
    	}
    	
    	return operators;
    }
});

module.exports = SearchCondition;
