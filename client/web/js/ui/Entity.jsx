/**
 * Render an entity
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.Entity");

/**
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};

/**
 * Module shell
 */
netric.ui.Entity = React.createClass({

    render: function() {

        return (
            <div>
                Render: {this.props.objType}.{this.props.oid}
            </div>
        );
    }

});
