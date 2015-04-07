/**
 * Overlay used to hide elements below current displayed componenet
 *
 * @jsx React.DOM
 */
'use strict';
var React = require('react');

/**
 * Small application component
 */
var Overlay = React.createClass({
	// mixins: [Classable],

	propTypes: {
		show: React.PropTypes.bool
	},

	render: function() {

		var classes = "overlay";
		if (this.props.show) {
			classes += " is-shown";
		}

		return (
			<div {...this.props} className={classes} />
		);
	}
});

module.exports = Overlay;
