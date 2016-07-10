/**
 * Handles the changing of user's password
 *
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var Controls = require('../../../Controls.jsx');
var TextField = Controls.TextField;
var AppBar = Controls.AppBar;
var IconButton = Controls.IconButton;
var RaisedButton = Controls.RaisedButton;
var Snackbar = Controls.Snackbar;

/**
 * Manage action data for changing the password
 */
var ChangePassword = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * Entity being edited
         *
         * @type {entity\Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Generic object used to pass events back up to controller
         *
         * @type {Object}
         */
        eventsObj: React.PropTypes.object,

        /**
         * Flag indicating if we are in edit mode or view mode
         *
         * @type {bool}
         */
        editMode: React.PropTypes.bool,

        /**
         * Function that is called when clicking the back button
         *
         * @type {function}
         */
        onNavBtnClick: React.PropTypes.func,

        /**
         * Function that is called to save the password
         *
         * @type {function}
         */
        saveEntity: React.PropTypes.func.isRequired
    },

    getInitialState: function () {
        // Return the initial state
        return {
            snackbarMessage: '',
        };
    },

    /**
     * Render action type form
     *
     * @returns {JSX}
     */
    render: function () {
        var toolBar = null,
            displayButton = null;

        // If we are not displaying the toolbar, then we need to display a button to change the password
        if (this.props.hideToolbar) {
            displayButton = (
                <div className="entity-form-field entity-form-button">
                    <RaisedButton
                        label="Change Password"
                        onClick={this._handleChangePassword}/>
                </div>
            );
        } else {
            var elementLeft = (
                <IconButton
                    iconClassName='fa fa-arrow-left'
                    onClick={this._handleBackButtonClicked}
                />
            );

            toolBar = (
                <AppBar
                    iconElementLeft={elementLeft}
                    title={this.props.title}>
                    <IconButton
                        iconClassName='fa fa-check'
                        onClick={this._handleChangePassword}
                    />
                </AppBar>
            );
        }

        return (
            <div className="entity-form">
                {toolBar}
                <div className="entity-form-field">
                    <TextField type="password" ref="newPassword" floatingLabelText="New Password"/>
                </div>
                <div className="entity-form-field">
                    <TextField type="password" ref="confirmPassword" floatingLabelText="Confirm Password"/>
                </div>
                {displayButton}
                <Snackbar ref="snackbar" message={this.state.snackbarMessage}/>
            </div>
        );
    },

    /**
     * Respond when the user clicks the change password button
     *
     * @param {DOMEvent} evt Reference to the DOM event being sent
     * @private
     */
    _handleChangePassword: function (evt) {

        // We need to check if there is a new password provided
        if (this.refs.newPassword.getValue().length == 0) {
            this.refs.newPassword.setErrorText("Please input a new password.");
            return false;
        }

        // Check if the new password is the same as confirm password
        if (this.refs.newPassword.getValue() != this.refs.confirmPassword.getValue()) {
            this.refs.confirmPassword.setErrorText("Passwords did not match.");
            return false;
        }

        // Set the new password in the props.entity
        this.props.entity.setValue("password", this.refs.newPassword.getValue());

        // Let's save now the updated password
        this.props.saveEntity(this.props.entity, this._finishedUpdatingPassword);

        // Display a message that we are currently updating the user's password
        this.setState({snackbarMessage: "Updating password..."});

        // Show the snackbar
        this.refs.snackbar.show();
    },

    /**
     * Respond when the user clicks the back button
     *
     * @param {DOMEvent} evt Reference to the DOM event being sent
     * @private
     */
    _handleBackButtonClicked: function (evt) {
        if (this.props.onNavBtnClick) {
            this.props.onNavBtnClick();
        }
    },

    /**
     * This is a callback function that is called when the saving of entity is finished
     *
     * @param {object} resp The response sent by the server after saving the entity
     * @private
     */
    _finishedUpdatingPassword: function () {

        // After updating the password, let's clear the password inputs
        this.refs.newPassword.setValue("");
        this.refs.confirmPassword.setValue("");

        // Display a message that we have successfully updated the user's password
        this.setState({snackbarMessage: "Password has been successfully updated."});
    }
});

module.exports = ChangePassword;
