/**
 * A grouping chip displays a single grouping entry
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Chip used to represent a grouping entry
 */
var GroupingChip = React.createClass({

	propTypes: {
		id: React.PropTypes.number,
		name: React.PropTypes.string,
		onRemove: React.PropTypes.func
	},

	render: function() {

		var remEl = null;
		if (this.props.onRemove) {
			remEl = (<span> | <i className="fa fa-times" onClick={this._handleRemove}/></span>);
		}

		return (
			<div {...this.props} className='grouping-chip'>
				{this.props.name}
				{remEl}
			</div>
		);
	},

	/**
	 * Handle removing the grouping chip
	 */
	_handleRemove: function(evt) {
		if (this.props.onRemove) {
			this.props.onRemove(this.props.id, this.props.name);
		}
	}
});

module.exports = GroupingChip;
