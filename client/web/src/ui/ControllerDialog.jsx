/**
 * Render controller in a modal dialog
 *

 */
'use strict';
var React = require('react');
var Chamel = require("chamel");
var Dialog = Chamel.Dialog;

/**
 * Dialog shell
 */
var ControllerDialog = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        title: React.PropTypes.string,
        dialogActions: React.PropTypes.array
    },

    /**
     * Set defaults
     */
    getDefaultProps: function() {
        return {
            title: 'Browse',
            actions: null
        };
    },

    render: function() {

        let dialogActions = [
            { text: 'Cancel', onClick: this._handleCancel }
        ];

        if (this.props.dialogActions) {
            dialogActions = this.props.dialogActions;
        }

        return (
            <Dialog
                id="dialog"
                key="dialog"
                ref="dialog"
                title={this.props.title}
                actions={dialogActions}
                autoDetectWindowHeight={true}
                autoScrollBodyContent={true}
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
        this.reposition();
    },

    /**
     * Reposition the dialog
     */
    reposition: function() {
        if (this.refs.dialog) {
            this.refs.dialog.reposition();
        }
    }


});

module.exports = ControllerDialog;
