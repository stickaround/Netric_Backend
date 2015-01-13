/**
 * Render the application shell for a large device
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.component.application.Large");

alib.require("netric.ui.component.ActionBar");

/** 
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};
netric.ui.component = netric.ui.component || {};
netric.ui.component.application = netric.ui.component.application || {};

/**
 * Large application component
 */
netric.ui.component.application.Large = React.createClass({
  getInitialState: function() {
    return {orgName: this.props.orgName};
  },
  render: function() {
    return (
      <div>
        <div id="app-header" className="app-header app-header-large">
          <div id="app-header-logo" className="app-header-logo-con">
            <img src={this.props.logoSrc} id="app-header-logo" />
          </div>
          <div id="app-header-search" className="app-header-search-con">
            Search goes here
          </div>
          <div className="app-header-profile-con">
            <i className="fa fa-camera-retro fa-lg"></i>
          </div>
        </div>
        <div id="app-body" className="app-body app-body-large">
          Put the app body here
        </div>
      </div>
    );
  }
});
