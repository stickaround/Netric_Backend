/**
 * Render an entity browser
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.EntityBrowser");

alib.require("netric.ui.AppBar");

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

    var appBar = "";

    if (this.props.onNavBtnClick) {
        appBar = <netric.ui.AppBar title={this.state.name} onNavBtnClick={this.menuClick_} />;
    } else {
        appBar = <netric.ui.AppBar title={this.state.name} />;
    }

    return (
      <div>
        <div>
          {appBar}
        </div>
        <div ref="moduleMain">
            <netric.ui.entitybrowser.List
                onEntityListClick={this.props.onEntityListClick}
                entities={this.props.entities}
            />
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
