/**
 * Render an entity browser
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var List = require("./entitybrowser/List.jsx");
var IconButton = require("./IconButton.jsx");
var AppBarBrowse = require("./entitybrowser/AppBarBrowse.jsx");

/**
 * Module shell
 */
var EntityBrowser = React.createClass({

  propTypes: {
      onEntityListClick: React.PropTypes.func,
      onEntityListSelect: React.PropTypes.func,
      onPerformAction: React.PropTypes.func,
      layout : React.PropTypes.string,
      title : React.PropTypes.string,
      actionHandler : React.PropTypes.object,
      entities: React.PropTypes.array,
      deviceSize: React.PropTypes.number,
      selectedEntities: React.PropTypes.array
  },

  getDefaultProps: function() {
      return {
          layout: '',
          title: "Browser",
          entities: [],
          selectedEntities: []
      }
  },

  render: function() {

    return (
      <div>
        <div>
          <AppBarBrowse 
            title={this.props.title}
            actionHandler={this.props.actionHandler}
            deviceSize={this.props.deviceSize}
            onNavBtnClick={this.props.onNavBtnClick}
            onSearchChange={this.props.onSearchChange}
            onPerformAction={this.props.onPerformAction}
            onSelectAll={this.handleSeelctAll_}
            selectedEntities={this.props.selectedEntities} />
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

  /** 
   * Select/Deselect all
   */
  handleSeelctAll_: function(selected) {
    if (this.props.onEntityListSelect) {
      this.props.onEntityListSelect(selected);
    }
  }

});

module.exports = EntityBrowser;