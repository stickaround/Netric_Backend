/**
 * Render a module
 *

 */
'use strict';
var React = require('react');

var Chamel = require("chamel");
var LeftNav = Chamel.LeftNav;
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

    // Setup the left nav header
    var leftNavHeader = (
      <LeftNavModuleHeader 
        moduleName={this.props.name} 
        onModuleChange={this.onModuleChange_}
        deviceIsSmall={this.props.deviceIsSmall} 
        title={this.props.title} 
        modules={this.props.modules} 
        user={this.props.user}/>
    );

    return (
        <div>
            <LeftNav
                onChange={this.onLeftNavChange_}
                ref="leftNav"
                menuItems={this.props.leftNavItems}
                docked={this.props.leftNavDocked}
                header={leftNavHeader}
            />
            <div ref="moduleMain" className={moduleMainClass}></div>
        </div>
    );
  },

  // The left navigation was changed
  onLeftNavChange_: function(evt, index, payload) {
    if (this.props.onLeftNavChange) {
      this.props.onLeftNavChange(evt, index, payload);
    }
  },

  /**
   * The user selected a different menu
   */
  onModuleChange_: function(evt, moduleName) {
    if (this.props.onModuleChange) {
      this.props.onModuleChange(evt, moduleName);
    }
  }

});

module.exports = Module;
