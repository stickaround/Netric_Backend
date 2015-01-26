/**
 * Render the application shell for a small device
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.application.Small");

alib.require("netric.ui.ActionBar");

/** 
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};
netric.ui.application = netric.ui.application || {};

/**
 * Small application component
 */
netric.ui.application.Small = React.createClass({
  
  getInitialState: function() {
    return {orgName: this.props.orgName};
  },

  render: function() {
    return (
      <div>
        <a href={"javascript:netric.location.go('" + this.props.basePath + "messages" + "')" }>Go to messages</a>
        <div ref="appMain"></div>
      </div>
    );
  }
});
