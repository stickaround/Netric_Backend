/**
 * Render the application shell for a large device
 *

 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var Paper = Chamel.Paper;


/**
 * Large application component
 */
var Large = React.createClass({
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
            Search goes here and there
          </div>
          <div className="app-header-profile-con">
            <i className="fa fa-camera-retro fa-lg"></i>
          </div>
        </div>
        <div id="app-body" ref="appMain" className="app-body app-body-large"></div>
      </div>
    );
  }
});

module.exports = Large;
