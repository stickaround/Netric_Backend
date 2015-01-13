/**
 * Main application toolbar for small/mobile devices
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.component.ActionBar");

/** 
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};
netric.ui.component = netric.ui.component || {};

/**
 * Small application component
 */
netric.ui.component.ActionBar = React.createClass({
  render: function() {
    return (
      <div className="app-header app-header-small">
        <i className="fa fa-bars fa-lg"></i>
        <span>{this.props.title}</span>
      </div>
    );
  }
});