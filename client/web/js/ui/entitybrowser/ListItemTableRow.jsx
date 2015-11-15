/**
 * Table row view of an entity
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var EntityField = require("../../entity/definition/Field");
var Chamel = require('chamel');
var Checkbox = Chamel.Checkbox;

/**
 * Module shell
 */
var ListItemTableRow = React.createClass({

    propTypes: {

        /**
         * Callback to call when a user clicks on an entity
         *
         * @var {function}
         */
        onClick: React.PropTypes.func,

        /**
         * Callback to call any time a user selects an entity from the list
         *
         * @var {function}
         */
        onCheck: React.PropTypes.func,

        /**
         * The entity we are printing
         *
         * @var {netric/entity/Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Contains settings for which columns to show in the row
         *
         * @var {netric/entity/BrowserView}
         */
        browserView: React.PropTypes.object
    },

    /**
     * Render the entity table row
     *
     * @returns {React.DOM}
     */
    render: function () {
        var entity = this.props.entity;
        var classes = "entity-browser-item entity-browser-item-trow";
        if (this.props.selected) {
            classes += " selected";
        }

        // Add columns
        var columns = [];
        var fields = this.getFieldsToRender_();
        for (var i = 0; i < fields.length; i++) {

            // The first cell should has a bold/name class
            var cellClassName = (i === 0) ? "entity-browser-item-trow-name" : null;
            // Get the value
            var fieldDef = this.props.entity.def.getField(fields[i]);
            var cellContents = null;

            switch (fieldDef.type) {
                case EntityField.types.fkey:
                case EntityField.types.fkeyMulti:
                case EntityField.types.object:
                case EntityField.types.objectMulti:
                    cellContents = this.props.entity.getValueName(fields[i]);
                    if (cellContents instanceof Object) {
                        var buf = "";
                        for (var prop in cellContents) {
                            buf += (buf) ? ", " + cellContents[prop] : cellContents[prop];
                        }
                        cellContents = buf;
                    }
                    break;
                case EntityField.types.date:
                case EntityField.types.timestamp:
                    cellContents = this.props.entity.getTime(fields[i], true);
                    break;
                default:
                    cellContents = this.props.entity.getValue(fields[i]);
                    break;
            }

            // Truncate long strings
            if (cellContents) {
                cellContents = (cellContents.length>20)
                    ? cellContents.substr(0,100)+'...' : cellContents;
            }

            // Add the column
            columns.push(
                <td className={cellClassName} onClick={this.props.onClick}>
                    <div>{cellContents}</div>
                </td>
            );
        }

        return (
            <tr className={classes}>
                <td className="entity-browser-item-trow-icon">
                    <div className="entity-browser-item-cmp-icon">
                        <Checkbox checked={this.props.selected} onCheck={this.toggleSelected} />
                    </div>
                </td>
                {columns}
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
    },

    /**
     * Try to determine which fields we should use for table columns
     *
     * @return {string[]}
     */
    getFieldsToRender_: function() {
        var fields = (this.props.browserView) ? this.props.browserView.getTableColumns() : [];

        // If no table columns are defined in the view, then guess
        if (fields.length < 1) {
            if (this.props.entity.def.getNameField()) {
                fields.push(this.props.entity.def.getNameField());
            }

            // TODO: Add more common fields here
        }

        return fields;
    }

});

module.exports = ListItemTableRow;
