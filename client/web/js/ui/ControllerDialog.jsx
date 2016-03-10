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
        title: React.PropTypes.string
    },

    /**
     * Set defaults
     */
    getDefaultProps: function() {
        return {
            title: 'Browse',
        };
    },

    render: function() {

        var standardActions = [
            { text: 'Cancel', onClick: this._handleCancel }
        ];

        return (
            <Dialog
                ref="dialog"
                title={this.props.title}
                actions={standardActions}
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
