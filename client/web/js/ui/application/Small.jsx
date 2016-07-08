/**
 * Render the application shell for a small device
 *

 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var Paper = Chamel.Paper;

/**
 * Small application component
 */
var Small = React.createClass({

  getInitialState: function() {
    return {orgName: this.props.orgName};
  },

  render: function() {
    return (
      <div>
        <div ref="appMain"></div>
      </div>
    );
  }
});

module.exports = Small;
