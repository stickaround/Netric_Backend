/**
 * Render an entity browser
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.entitybrowser.List");

/**
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};
netric.ui.entitybrowser = netric.ui.entitybrowser || {};

/**
 * Module shell
 */
netric.ui.entitybrowser.List = React.createClass({

    render: function() {

        var entityNodes = this.props.entities.map(function(entity) {
            return (
                <li key={entity.id} onClick={this._sendClick.bind(null, entity.objType, entity.id)}>{entity.id}</li>
            );
        }.bind(this));

        return (
            <ul>
                {entityNodes}
            </ul>
        );
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
    }

});
