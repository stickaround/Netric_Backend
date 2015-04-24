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
		id: React.PropTypes.string,
		name: React.PropTypes.string
	},

	render: function() {

		return (
			<div {...this.props} className='grouping-chip'>
				{this.props.name}
			</div>
		);
	}
});

module.exports = GroupingChip;
