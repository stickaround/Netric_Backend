/**
 * Main application toolbar for small/mobile devices
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.ActionBar");

/** 
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};

/**
 * Small application component
 */
netric.ui.ActionBar = React.createClass({

	render: function() {

		var navBtn = "X";

		// Set the back/menu button
		if (this.props.onNavBtnClick) {
			var navBtn = <i className="fa fa-bars fa-lg" onClick={this.props.onNavBtnClick}></i>;
		}

		return (
		  <div className="app-header app-header-small">
		    {navBtn}
		    <span>{this.props.title}</span>
		  </div>
		);
	}
});