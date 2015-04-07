/**
 * Table row view of an entity
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Module shell
 */
var ListItemTableRow = React.createClass({

    render: function () {
        var entity = this.props.entity;
        var classes = "entity-browser-item entity-browser-item-trow";
        if (this.props.selected) {
            classes += " selected";
        }

        return (
            <tr className={classes}>
                <td className="entity-browser-item-trow-icon">
                    <input type="checkbox" checked={this.props.selected} onChange={this.toggleSelected} />
                </td>
                <td className="entity-browser-item-trow-name" onClick={this.props.onClick}>
                    Subject Here
                </td>
                <td onClick={this.props.onClick}>
                    From Here
                </td>
                <td onClick={this.props.onClick}>
                    Snippet here {entity.id}
                </td>
            </tr>
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

module.exports = ListItemTableRow;
