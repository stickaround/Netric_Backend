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
        viewFields: React.PropTypes.array,
        viewIndex: React.PropTypes.number,
        objType: React.PropTypes.string.isRequired,
    },
    
    componentDidMount: function() {
    },

    render: function() {
    		
        return (
        		<div className="row" key={this.props.viewIndex}>
					<div className="col-small-3">
						<DropDownMenu menuItems={this.props.viewFields} />
					</div>
	    			<div className="col-small-1">
						<IconButton onClick={this._handleRemoveOrder.bind(this, this.props.viewIndex)} className="fa fa-times" />
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
    _handleRemoveOrder: function (viewIndex) {
    	if(this.props.onRemove) this.props.onRemove('columnView', viewIndex);
    },
    
    /**
     * Returns the Sort Order set
     *
     * @public
     */
    getCriteria: function() {
        return null;
    }
});

module.exports = ColumnView;
