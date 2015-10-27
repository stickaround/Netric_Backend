/**
 * Search condition used for advanced search.
 * Pass the condition object in the props which is an instance of Where object.
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var groupingLoader = require("../../entity/groupingLoader");
var ObjectSelect = require("../entity/ObjectSelect.jsx");
var GroupingSelect = require("../entity/GroupingSelect.jsx");
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
 * Displays the search conditions used in advanced search
 */
var SearchCondition = React.createClass({

    propTypes: {
        index: React.PropTypes.number,
        objType: React.PropTypes.string,
        onRemove: React.PropTypes.func,
        fieldData: React.PropTypes.object,
        condition: React.PropTypes.object,
    },

    getInitialState: function() {
        var searchFields = this.props.fieldData.fields;
        var field = null;
        var operators = null;
        var valueInput = null;
        
        // Set the default field entry based on the selected index
        if(searchFields) {
            field = searchFields[this.props.fieldData.selectedIndex];
            
            valueInput = this._getConditionValueInput(field, this.props.fieldData.selectedIndex, this.props.condition.value);
            operators = this._getConditionOperators(field.type)
        }
        
        // Return the initial state
        return {
            valueInput: valueInput,
            operators: operators,
            selectedField: this.props.fieldData.selectedIndex,
            selectedbLogic: this._getSelectedIndex(bLogicMenu, this.props.condition.bLogic),
            selectedOperator: this._getSelectedIndex(this._operators, this.props.condition.operator),
        	};
    },

    render: function() {
        return (
                <div className="row" key={this.props.index}>
                    <div className="col-small-1">
                        <DropDownMenu 
                            menuItems={bLogicMenu} 
                            selectedIndex={parseInt(this.state.selectedbLogic)} 
                            onChange={this._handleBlogicClick} />
                    </div>
                    <div className="col-small-4">
                        <DropDownMenu 
                            menuItems={this.props.fieldData.fields} 
                            selectedIndex={parseInt(this.state.selectedField)} 
                            onChange={this._handleFieldClick} />
                    </div>
                    <div className="col-small-4" >
                        <DropDownMenu 
                            menuItems={this.state.operators} 
                            selectedIndex={parseInt(this.state.selectedOperator)} 
                            onChange={this._handleOperatorClick} />      
                    </div>
                    <div className="col-small-2">
                        {this.state.valueInput}
                    </div>
                    <div className="col-small-1">
                        <IconButton 
                            onClick={this._handleRemoveCondition} 
                            className="fa fa-times" />  
                    </div>    
                </div>
        );
    },
    
    /**
     * Callback used to handle commands when user selects the a value in bLogic dropdown
     *
     * @param {DOMEvent} e          Reference to the DOM event being sent
     * @param {int} key             The index of the menu clicked
     * @param {array} menuItem      The object value of the menu clicked
     * @private
     */
    _handleBlogicClick: function(e, key, menuItem) {
        this.props.condition.bLogic = menuItem.payload;
        
        this.setState({
            selectedbLogic: key
        });
    },
    
    /**
     * Callback used to handle commands when user selects the a value in operator dropdown
     *
     * @param {DOMEvent} e          Reference to the DOM event being sent
     * @param {int} key             The index of the menu clicked
     * @param {array} menuItem      The object value of the menu clicked
     * @private
     */
    _handleOperatorClick: function(e, key, menuItem) {
        this.props.condition.operator = menuItem.payload;
        
        this.setState({
            selectedOperator: key
        });
    },
    
    /**
     * Handles blur on the value input
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @private
     */
    _handleValueInputBlur: function(e) {
        this.props.condition.value = e.target.value;
    },
    
    /**
     * Callback used to handle commands when user selects a value in the dropdown groupings input
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @param {int} key     The index of the menu clicked
     * @param {array} field The object value of the menu clicked
     * @private
     */
    _handleGroupingSelect: function(payload, text) {
        this.props.condition.value = e.target.value;
    },

    /**
     * Callback used to handle commands when user selects a field name in the condition search
     *
     * @param {DOMEvent} e 		Reference to the DOM event being sent
     * @param {int} key		The index of the menu clicked
     * @param {array} field	The object value of the menu clicked
     * @private
     */
    _handleFieldClick: function(e, key, field) {
    	this._getConditionValueInput(field, key, null);
    },

    /**
     * Removes the contidion
     *
     * @private
     */
    _handleRemoveCondition: function () {
        if(this.props.onRemove) this.props.onRemove('condition', this.props.index);
    },
    
    /**
     * Get the search condition input type based on the field type selected
     *
     * @param {array} field	            Collection of the field selected information
     * @param {int} fieldIndex      The index of the menu field clicked
     * @param {string} value            The default value or initial value
     * @private
     */
    _getConditionValueInput: function(field, fieldIndex, value) {
    	var valueInput = null;
    	
    	switch(field.type) {
        	case 'fkey':
            case 'fkey_multi':
                valueInput = (
                        <GroupingSelect
                            onChange={this._handleGroupingSelect}
                            objType={this.props.objType}
                            fieldName={field.name}
                            label={null}
                        />
                );
                break;
                
            case 'object':
                valueInput = (
                        <ObjectSelect
                            onChange={this._handleSetValue}
                            objType={this.props.objType}
                            fieldName={field.name}
                            value={null}
                        />
                );
                break;
                
    		case 'bool':
    		    if(null === value) {
    		        value = boolInputMenu[0].payload;
    		    }
    		    
    			valueInput = (
    			        <DropDownMenu 
    			            onChange={this._handleValueSelect}
    			            selectedIndex={ ( value.toString() === 'true' ? 0 : 1 )}
    			            menuItems={boolInputMenu}
    			        />
    			);
    			break;
    			
    		default:
    		    valueInput = (
    		            <TextField 
    		                onBlur={this._handleValueInputBlur} 
    		                hintText="Search" value={value} 
    		            />
    		    );
    			break;
    	}  
    	
    	// Update the state if the component is already mounted
    	if(this.isMounted()) {
    	    var operators = this._getConditionOperators(field.type); // get the operators based on the field type
    	    
    	    // Update the condition data
    	    this.props.condition.fieldName = field.name;
    	    this.props.condition.operator = operators[0].payload;
    	    this.props.condition.value = value;
    	    
    	    this.setState({
    	        valueInput: valueInput,
    	        operators: operators,
                selectedField: fieldIndex,
                selectedOperator: 0, // Set the operator's index to 0
            });
    	}
    	
    	return valueInput;
    },
    
    /**
     * Get the search condition operator based on the field type selected
     *
     * @param {string} fieldType    The type of the field
     * @private
     */
    _getConditionOperators: function(fieldType) {
    	var fieldOperators = this.props.condition.getOperatorsForFieldType(fieldType)
    	var operators = [];
    	
    	for(var idx in fieldOperators) {
    	    operators.push({
    	        payload: idx,
    	        text: fieldOperators[idx]
    	    });
    	}
    	
    	return operators;
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
});

module.exports = SearchCondition;
