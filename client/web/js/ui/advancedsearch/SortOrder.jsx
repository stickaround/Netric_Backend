/**
 * Sort By used for advance search
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var DropDownMenu = Chamel.DropDownMenu;
var IconButton = Chamel.IconButton;

var directionMenu = [
                     { name: 'asc', text: 'Ascending' },
                     { name: 'desc', text: 'Descending' },
                 ];


/**
 * Module shell
 */
var SortOrder = React.createClass({

    propTypes: {
        onRemove: React.PropTypes.func,
        fieldData: React.PropTypes.object,
        index: React.PropTypes.number,
        objType: React.PropTypes.string.isRequired,
        savedOrder: React.PropTypes.object,
    },
    
    getInitialState: function() {
        var selectedFieldIndex = 0;
        var selectedDirectionIndex = 0;
        
        // if savedOrder is set, then we will override the default sort order
        if(this.props.savedOrder) {
            selectedFieldIndex = this.props.fieldData.selectedIndex;
            selectedDirectionIndex = ( this.props.savedOrder.direction == 'asc' ? 0 : 1 );
        }
        
        // Return the initial state
        return { 
            fieldName: this.props.fieldData.fields[selectedFieldIndex].name, 
            direction: directionMenu[selectedDirectionIndex].name,
            selectedFieldIndex: selectedFieldIndex,
            selectedDirectionIndex: selectedDirectionIndex
            };
    },

    render: function() {
    		
        return (
        		<div className="row" key={this.props.index}>
					<div className="col-small-3">
						<DropDownMenu 
						        menuItems={this.props.fieldData.fields} 
						        selectedIndex={this.state.selectedFieldIndex} 
						        onChange={this._handleMenuClick.bind(this, 'fieldName')} />
					</div>
	    			<div className="col-small-2">
	    				<DropDownMenu 
	    				        menuItems={directionMenu} 
	    				        selectedIndex={this.state.selectedDirectionIndex} 
	    				        onChange={this._handleMenuClick.bind(this, 'direction')} />
					</div>
	    			<div className="col-small-1">
						<IconButton 
						        className="fa fa-times"
						        onClick={this._handleRemoveOrder.bind(this, this.props.index)} />
					</div>
				</div>
        	);
    },
    
    /**
     * Removes the Sort Order
     *
     * @param {Integer} conditionIndex		The index of the condition to be removed
     * @private
     */
    _handleRemoveOrder: function (index) {
    	if(this.props.onRemove) this.props.onRemove('sortOrder', index);
    },
    
    /**
     * Callback used to handle commands when user selects the field name / sort by dropdown
     *
     * @param {string} type     Type of criteria that was changed
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @param {Integer} key     The index of the menu clicked
     * @param {array} field     The object value of the menu clicked
     * @private
     */
    _handleMenuClick: function(type, e, key, field) {
        switch(type) {
            case 'fieldName':
                this.setState({
                    fieldName: field.name,
                    selectedFieldIndex: key
                });
                break;
            case 'direction':
                this.setState({
                    direction: field.name,
                    selectedDirectionIndex: key
                });
                break;
        }
    },
    
    /**
     * Returns the Sort Order set
     *
     * @public
     */
    getCriteria: function() {
        var sortOrder = { 
                fieldName: this.state.fieldName,
                direction: this.state.direction
        }
        
        return sortOrder;
    }
});

module.exports = SortOrder;
