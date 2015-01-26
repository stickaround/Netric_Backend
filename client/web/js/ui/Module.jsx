/**
 * Render a module
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.Module");

alib.require("netric.ui.ActionBar");

/** 
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};

/**
 * Module shell
 */
netric.ui.Module = React.createClass({

  getInitialState: function() {
    return {name: "Loading..."};
  },

  getDefaultProps: function() {
    return {
      leftNavDocked: false
    };
  },

  componentDidMount: function() {

    netric.module.loader.get("messages", function(mdl){
      this.setState({name: mdl.name});
    }.bind(this));

    
  },

  render: function() {

    // Set module main 
    var moduleMainClass = "module-main";
    if (this.props.leftNavDocked) {
      moduleMainClass += " left-nav-docked";
    }      

    return (
      <div>
        <netric.ui.LeftNav onChange={this.onLeftNavChange_} ref="leftNav" menuItems={this.props.leftNavItems} docked={this.props.leftNavDocked} />
        <div ref="moduleMain" className={moduleMainClass}>
        </div>
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
