/**
 * Render an entity browser
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.EntityBrowser");

alib.require("netric.ui.ActionBar");

/** 
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};

/**
 * Module shell
 */
netric.ui.EntityBrowser = React.createClass({

  getInitialState: function() {
    return {name: "Browser"};
  },

  /*
  componentDidMount: function() {

    netric.module.loader.get("messages", function(mdl){
      this.setState({name: mdl.name});
    }.bind(this));
  },
  */

  render: function() {

    var actionBar = "";

    if (this.props.onNavBtnClick) {
      actionBar = <netric.ui.ActionBar title={this.state.name} onNavBtnClick={this.menuClick_} />;
    } else {
      actionBar = <netric.ui.ActionBar title={this.state.name} />;
    }

    return (
      <div>
        <div>
          {actionBar}
        </div>
        <div ref="moduleMain">
          Browser loaded
        </div>
      </div>
    );
  },

  // The menu item was clicked
  menuClick_: function(evt) {
    if (this.props.onNavBtnClick)
      this.props.onNavBtnClick(evt);
  },

});
