/**
 * Default compressed view of an entity
 *

 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var Checkbox = Chamel.Checkbox;

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
        var headerTime = entity.getTime(null, true);
        var snippet = entity.getSnippet();
        var actors = entity.getActors();

        var actorsUi = null;
        if (actors) {
            actorsUi = (
                <div className="entity-browser-item-cmp-subheader">
                    {actors}
                </div>
            );
        }

        return (
            <div className={classes}>
                <div className="entity-browser-item-cmp-icon">
                    <Checkbox checked={this.props.selected} onCheck={this.toggleSelected} />
                </div>
                <div className="entity-browser-item-cmp-body"
                    onClick={this.props.onClick}>
                    <div className="entity-browser-item-cmp-header">
                        {headerText}
                        <div className="entity-browser-item-cmp-time">
                            {headerTime}
                        </div>
                    </div>
                    {actorsUi}
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