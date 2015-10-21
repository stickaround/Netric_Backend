/**
 * Render controller in a modal dialog
 *
 * @jsx React.DOM
 */
'use strict';
var React = require('react');
var Chamel = require("chamel");
var Dialog = Chamel.Dialog;

/**
 * Dialog shell
 */
var ControllerDialog = React.createClass({

    render: function() {

        var standardActions = [
            { text: 'Cancel', onClick: this._handleCancel }
        ];

        return (
            <Dialog
                ref="dialog"
                title="Controller"
                actions={standardActions}
                modal={true}>
                <div ref="dialogContent" />
            </Dialog>
        );
    },

    _handleCancel: function() {
        this.refs.dialog.dismiss();
    },

    /**
     * Hide the dialog
     */
    dismiss: function() {
        this.refs.dialog.dismiss();
    },

    /**
     * Show the dialog
     */
    show: function() {
        this.refs.dialog.show();
    }


});

module.exports = ControllerDialog;
