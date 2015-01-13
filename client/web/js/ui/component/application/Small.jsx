/**
 * Render the application shell for a small device
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.component.application.Small");

alib.require("netric.ui.component.ActionBar");

/** 
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};
netric.ui.component = netric.ui.component || {};
netric.ui.component.application = netric.ui.component.application || {};

/**
 * Small application component
 */
netric.ui.component.application.Small = React.createClass({
  
  getInitialState: function() {
    return {orgName: this.props.orgName};
  },

  render: function() {
    return (
      <div>
        <div>
          <netric.ui.component.ActionBar title={this.state.orgName}></netric.ui.component.ActionBar>
        </div>
        <div ref="moduleMain">
          Load the module here
        </div>
      </div>
    );
  }
});
