/**
 * Default compressed view of an entity
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Module shell
 */
var ListItem = React.createClass({

    render: function () {
        var entity = this.props.entity;
        var classes = "entity-browser-item entity-browser-item-cmp";
        if (this.props.selected) {
            classes += " selected";
        }
        var headerText = entity.getName();
        var snippet = entity.getSnippet();

        return (
            <div className={classes}>
                <div className="entity-browser-item-cmp-icon">
                    <input type="checkbox" checked={this.props.selected} onChange={this.toggleSelected} />
                </div>
                <div className="entity-browser-item-cmp-body"
                    onClick={this.props.onClick}>
                    <div className="entity-browser-item-cmp-header">
                        {headerText}
                    </div>
                    <div className="entity-browser-item-cmp-subheader">
                        From Here
                    </div>
                    <div className="entity-browser-item-cmp-caption">
                        {snippet}
                    </div>
                </div>
            </div>
        );
    },

    /**
     * Toggle selected state
     */
    toggleSelected: function() {
        if (this.props.onSelect) {
            this.props.onSelect();
        }
    }

});

module.exports = ListItem;