/**
 * Render an entity browser
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var AppBar = require("./AppBar.jsx");
var List = require("./entitybrowser/List.jsx");

/**
 * Module shell
 */
var EntityBrowser = React.createClass({

    propTypes: {
        onEntityListClick: React.PropTypes.func,
        onEntityListSelect: React.PropTypes.func,
        layout : React.PropTypes.string,
        entities: React.PropTypes.array,
        selectedEntities: React.PropTypes.array
    },

    getDefaultProps: function() {
        return {
            layout: '',
            entities: [],
            selectedEntities: []
        }
    },

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
        appBar = <AppBar title={this.state.name} onNavBtnClick={this.menuClick_} />;
    } else {
        appBar = <AppBar title={this.state.name} />;
    }

    return (
      <div>
        <div>
          {appBar}
        </div>
        <div ref="moduleMain">
            <List
                onEntityListClick={this.props.onEntityListClick}
                onEntityListSelect={this.props.onEntityListSelect}
                entities={this.props.entities}
                selectedEntities={this.props.selectedEntities}
                layout={this.props.layout} />
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

module.exports = EntityBrowser;