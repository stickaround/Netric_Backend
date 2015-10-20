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

var sortType = [
                     { payload: 'asc', text: 'Ascending' },
                     { payload: 'desc', text: 'Descending' },
                 ];


/**
 * Module shell
 */
var SortOrder = React.createClass({

    propTypes: {
    	onRemove: React.PropTypes.func,
        sortFields: React.PropTypes.array,
        sortIndex: React.PropTypes.number,
        objType: React.PropTypes.string.isRequired,
    },
    
    componentDidMount: function() {
    },

    render: function() {
    		
        return (
        		<div className="row" key={this.props.sortIndex}>
					<div className="col-small-3">
						<DropDownMenu menuItems={this.props.sortFields} />
					</div>
	    			<div className="col-small-2">
	    				<DropDownMenu menuItems={sortType} />
					</div>
	    			<div className="col-small-1">
						<IconButton onClick={this._handleRemoveOrder.bind(this, this.props.sortIndex)} className="fa fa-times" />
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
    _handleRemoveOrder: function (sortIndex) {
    	if(this.props.onRemove) this.props.onRemove('sortOrder', sortIndex);
    },
});

module.exports = SortOrder;
