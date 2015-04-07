/**
 * Render an entity browser
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ListItem = require("./ListItem.jsx");
var ListItemTableRow = require("./ListItemTableRow.jsx");

/**
 * Module shell
 */
var List = React.createClass({

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

    render: function() {

        var entityNodes = this.props.entities.map(function(entity) {
            var item = null;
            var selected = (this.props.selectedEntities.indexOf(entity.id) != -1);

            switch (entity.objType) {

                case "activity":
                    // TODO: add activity
                    break;
                case "comment":
                    // TODO: add comment
                    break;
                /*
                 * All other object types will either be displayed as a table row
                 * for large devices or a regular detailed item for small or preview mode.
                 */
                default:

                    if (this.props.layout == 'table'){
                        item = <ListItemTableRow
                            key={entity.id}
                            selected={selected}
                            entity={entity}
                            onClick={this._sendClick.bind(null, entity.objType, entity.id)}
                            onSelect={this._sendSelect.bind(null, entity.id)} />;
                    } else {
                        item = <ListItem
                            key={entity.id}
                            selected={selected}
                            entity={entity}
                            onClick={this._sendClick.bind(null, entity.objType, entity.id)}
                            onSelect={this._sendSelect.bind(null, entity.id)} />;
                    }

                    break;
            }

            return item;

        }.bind(this));

        if (this.props.layout == 'table'){
            return (
                <table><tbody>
                    {entityNodes}
                </tbody></table>
            );
        } else {
            return (
                <div>
                    {entityNodes}
                </div>
            );
        }
    },

    /**
     * User has clicked/touched an entity in the list
     *
     * @param {string} objType
     * @param {string} oid
     */
    _sendClick: function(objType, oid) {
        if (this.props.onEntityListClick) {
            this.props.onEntityListClick(objType, oid);
        }
    },

    /**
     * User has selected an entity (usually a checkbox)
     *
     * @param {string} objType
     * @param {string} oid
     */
    _sendSelect: function(oid) {
        if (this.props.onEntityListSelect) {
            this.props.onEntityListSelect(oid);
        }
    }

});

module.exports = List;
