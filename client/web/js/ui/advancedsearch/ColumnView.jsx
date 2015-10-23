/**
 * Column View used for advance search
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var DropDownMenu = Chamel.DropDownMenu;
var IconButton = Chamel.IconButton;


/**
 * Module shell
 */
var ColumnView = React.createClass({

    propTypes: {
        onRemove: React.PropTypes.func,
        fieldData: React.PropTypes.object,
        index: React.PropTypes.number,
        objType: React.PropTypes.string.isRequired,
        savedColumn: React.PropTypes.object,
    },
    
    getInitialState: function() {
        var selectedFieldIndex = 0;
        
        // if column view is set, then we will override the default sort order
        if(this.props.savedColumn) { 
            selectedFieldIndex = this.props.fieldData.selectedIndex;
        }
        
        // Return the initial state
        return { 
            fieldName: this.props.fieldData.fields[selectedFieldIndex].name,
            selectedFieldIndex: selectedFieldIndex,
            };
    },

    render: function() {
    		
        return (
        		<div className="row" key={this.props.index}>
					<div className="col-small-3">
						<DropDownMenu menuItems={this.props.fieldData.fields} selectedIndex={this.state.selectedFieldIndex} onChange={this._handleMenuClick} />
					</div>
	    			<div className="col-small-1">
						<IconButton onClick={this._handleRemoveOrder.bind(this, this.props.index)} className="fa fa-times" />
					</div>
				</div>
        	);
    },
    
    /**
     * Removes the view Order
     *
     * @param {Integer} conditionIndex		The index of the condition to be removed
     * @private
     */
    _handleRemoveOrder: function (index) {
    	if(this.props.onRemove) this.props.onRemove('columnView', index);
    },
    
    /**
     * Callback used to handle commands when user selects the column field
     *
     * @param {string} type     Type of criteria that was changed
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @param {Integer} key     The index of the menu clicked
     * @param {array} field     The object value of the menu clicked
     * @private
     */
    _handleMenuClick: function(e, key, field) {
        this.setState({
                        fieldName: field.name,
                        selectedFieldIndex: key
                    });
    },
    
    /**
     * Returns the Column View set
     *
     * @public
     */
    getCriteria: function() {
        var columnView = { 
                fieldName: this.state.fieldName,
        }
        
        return columnView;
    }
});

module.exports = ColumnView;
