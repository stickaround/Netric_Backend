/**
 * AppBar used for browse mode
 *

 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var IconButton = Chamel.IconButton;

/**
 * Module shell
 */
var AppBarBrowse = React.createClass({

    propTypes: {
        onPerformAction: React.PropTypes.func,
        // Entity actions to perform when selected
        actions: React.PropTypes.array
    },

    getDefaultProps: function() {
        return {
            onPerformAction: null
        };
    },

    render: function() {

        var icons = [];
        for (var i in this.props.actions) {
            var act = this.props.actions[i];
            icons.push(
                <IconButton
                    iconClassName={act.iconClassName}
                    onClick={this.handleActionClick_.bind(this, act.name)}>
                </IconButton>
            );
        }

        return (<div>{icons}</div>);
    },

    handleActionClick_: function(actionName) {
        if (this.props.onPerformAction) {
            this.props.onPerformAction(actionName);
        }
    }
});

module.exports = AppBarBrowse;
