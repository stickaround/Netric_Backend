/**
 * Render a module
 *
 * @jsx React.DOM
 */
'use strict';
var React = require('react');

var LeftNav = require("./LeftNav.jsx");
var LeftNavModuleHeader = require("./LeftNavModuleHeader.jsx");

/**
 * Module shell
 */
var Module = React.createClass({

  getInitialState: function() {
    return {name: "Loading..."};
  },

  getDefaultProps: function() {
    return {
      leftNavDocked: false
    };
  },

  componentDidMount: function() {

  },

  render: function() {

    // Set module main
    var moduleMainClass = "module-main";
    if (this.props.leftNavDocked) {
        moduleMainClass += " left-nav-docked";
    }
    var leftNavHeader = <LeftNavModuleHeader moduleTitle={this.props.title} />

    return (
        <div>
            <LeftNav onChange={this.onLeftNavChange_} ref="leftNav" menuItems={this.props.leftNavItems} docked={this.props.leftNavDocked} header={leftNavHeader} />
            <div ref="moduleMain" className={moduleMainClass}></div>
        </div>
    );
  },

  // The left navigation was changed
  onLeftNavChange_: function(evt, index, payload) {
    if (this.props.onLeftNavChange) {
      this.props.onLeftNavChange(evt, index, payload);
    }
  }

});

module.exports = Module;
