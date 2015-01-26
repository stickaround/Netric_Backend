/**
 * Overlay used to hide elements below current displayed componenet
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.Overlay");

/** 
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};

/**
 * Small application component
 */
netric.ui.Overlay = React.createClass({
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