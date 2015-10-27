/**
 * Sort By used for advance search.
 * Pass the orderBy object in the props and should contains the fieldName and direction data
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
 * Displays the sort order used in the advanced search.
 */
var SortOrder = React.createClass({

    propTypes: {
        onRemove: React.PropTypes.func,
        fieldData: React.PropTypes.object,
        index: React.PropTypes.number,
        objType: React.PropTypes.string.isRequired,
        orderBy: React.PropTypes.object,
    },
    
    getInitialState: function() {
        var selectedDirectionIndex = ( this.props.orderBy.direction == 'asc' ? 0 : 1 );
        
        // Return the initial state
        return { 
            fieldName: this.props.orderBy.field, 
            direction: this.props.orderBy.direction,
            selectedFieldIndex: this.props.fieldData.selectedIndex,
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
						        onChange={this._handleFieldNameClick} />
					</div>
	    			<div className="col-small-2">
	    				<DropDownMenu 
	    				        menuItems={directionMenu} 
	    				        selectedIndex={this.state.selectedDirectionIndex} 
	    				        onChange={this._handleDirectionClick } />
					</div>
	    			<div className="col-small-1">
						<IconButton 
						        className="fa fa-times"
						        onClick={this._handleRemoveOrder} />
					</div>
				</div>
        	);
    },
    
    /**
     * Removes the Sort Order
     *
     * @param {int} conditionIndex		The index of the condition to be removed
     * @private
     */
    _handleRemoveOrder: function () {
    	if(this.props.onRemove) this.props.onRemove('sortOrder', this.props.index);
    },
    
    /**
     * Callback used to handle commands when user selects the field name in the dropdown menu
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @param {int} key         The index of the menu clicked
     * @param {array} field     The object value of the menu clicked
     * @private
     */
    _handleFieldNameClick: function(e, key, field) {
        this.props.orderBy.field = field.name;
        this.setState({
            selectedFieldIndex: key
        });
    },
    
    /**
     * Callback used to handle commands when user selects the sort direction in the dropdown menu
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @param {int} key         The index of the menu clicked
     * @param {array} field     The object value of the menu clicked
     * @private
     */
    _handleDirectionClick: function(e, key, field) {
        this.props.orderBy.direction = field.name;
        this.setState({
            selectedDirectionIndex: key
        });
        console.log(this.props.orderBy);
    }
});

module.exports = SortOrder;
