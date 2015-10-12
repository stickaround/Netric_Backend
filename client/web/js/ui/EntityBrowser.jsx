/**
 * Render an entity browser
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var List = require("./entitybrowser/List.jsx");
var AppBarBrowse = require("./entitybrowser/AppBarBrowse.jsx");
var Loading = require("./Loading.jsx");
var Chamel = require('chamel');
var IconButton = Chamel.IconButton;

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
      selectedEntities: React.PropTypes.array,
      browserView: React.PropTypes.object,
      collectionLoading: React.PropTypes.bool
  },

  getDefaultProps: function() {
      return {
          layout: '',
          title: "Browser",
          entities: [],
          selectedEntities: [],
          collectionLoading: false
      }
  },

  render: function() {

      var bodyContent = null;

      if (this.props.entities.length == 0 && this.props.collectionLoading) {
          bodyContent = <Loading />;
      } else if (this.props.entities.length == 0) {
          bodyContent = <div className="entity-browser-blank">No items found.</div>;
      } else {
          bodyContent = (<List
              onEntityListClick={this.props.onEntityListClick}
              onEntityListSelect={this.props.onEntityListSelect}
              onLoadMoreEntities={this.props.onLoadMoreEntities}
              entities={this.props.entities}
              selectedEntities={this.props.selectedEntities}
              browserView={this.props.browserView}
              layout={this.props.layout}
          	  collectionLoading={this.props.collectionLoading} />);

          if (this.props.collectionLoading) {
              // TODO: display loading indicator over the list
          }
      }

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
            {bodyContent}
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