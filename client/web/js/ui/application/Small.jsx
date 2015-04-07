/**
 * Render the application shell for a small device
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Paper = require("../Paper.jsx");
var netric = require("../../main.js");

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
        <a href={"javascript:netric.location.go('" + this.props.basePath + "messages" + "')" }>Go to messages</a>
        <div ref="appMain"></div>
      </div>
    );
  }
});

module.exports = Small;
