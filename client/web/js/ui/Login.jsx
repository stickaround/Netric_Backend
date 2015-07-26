/**
 * Render the application shell for a large device
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Paper = require("./Paper.jsx");
var TextField = require("./TextField.jsx");
var RaisedButton = require("./RaisedButton.jsx");
var FontIcon = require("./FontIcon.jsx");
var Snackbar = require("./Snackbar.jsx");
var RadioButton = require("./RadioButton.jsx");
var RadioButtonGroup = require("./RadioButtonGroup.jsx");
var location = require("../location/location")

/**
 * Large application component
 */
var Large = React.createClass({

  propTypes: {
    onLogin: React.PropTypes.func,
    errorText: React.PropTypes.string,
    processing: React.PropTypes.bool,
    accounts: React.PropTypes.array,
  },

  getDefaultProps: function() {
    return {
      processing: false
    }
  },

  getInitialState: function() {
    return {
      username: null,
      password: null,
      loginDisabled: true,

    };
  },

  componentDidUpdate: function() {

    // Show error snackbar
    if (this.props.errorText) {
      this.refs.snackbar.show();
    } else {
      this.refs.snackbar.dismiss();
    }
  },

  render: function() {

    var processingIcon = null;
    if (this.props.processing) {
      processingIcon = <span> <i className="fa fa-spinner fa-pulse" /></span>
    }

    // Check if we should print accounts to select
    var accountOptions = null;
    if (this.props.accounts) {
      
      var radioButtons = [];
      for (var i in this.props.accounts) {

        var accountTitle = (this.props.accounts[i].title) ?
          this.props.accounts[i].title : this.props.accounts[i].account;

        radioButtons.push(
          <RadioButton
              value={this.props.accounts[i].instanceUri}
              label={accountTitle}
              defaultChecked={true} />
        );
      }

      accountOptions = (
        <div>
          <div className="font-style-body-1">
            Select an account to log into:
          </div>
          <RadioButtonGroup name="selectAccout" onChange={this._handleAccountSelected}>
              {radioButtons}
          </RadioButtonGroup>
        </div>
      );
    }

      var imagePath = location.getRelativeUrlRoot();
      imagePath += "/img/logo_login.png";

    return (
      <div className="login-page">
        <div className="login-logo-con">
          <img className="login-logo" src={imagePath} />
        </div>
        <div className="login-form-con">
          <TextField
                floatingLabelText="Username"
                onChange={this._handleUsernameChange} />
          <TextField
                type="password"
                floatingLabelText="Password"
                onChange={this._handlePasswordChange} />
          {accountOptions}
          <div className="login-form-actions">
            <RaisedButton disabled={this.state.loginDisabled} onClick={this._handleLoginClick} primary={true}>
              <span className="raised-button-label">
                Login
                {processingIcon}
              </span>
            </RaisedButton>
          </div>
          
        </div>
        <Snackbar ref="snackbar" message={this.props.errorText} />
      </div>
    );
  },

  _handleUsernameChange: function(e) {
    // Clear any errors
    this.setProps({errorText:null});

    this.state.username = e.target.value;
    this._updateLoginState();
  },

  _handleAccountSelected: function(e, selectedValue) {
    if (this.props.onSetAccount) {
      this.props.onSetAccount(selectedValue);
    }
  },

  _handlePasswordChange: function(e) {
    // Clear any errors
    this.setProps({errorText:null});
    
    this.state.password = e.target.value;
    this._updateLoginState();
  },

  _updateLoginState: function() {
    // Update enabled state of the login button
    if (this.state.username && this.state.password) {
      this.setState({loginDisabled: false});
    } else {
      this.setState({loginDisabled: true});
    }
  },

  _handleLoginClick: function(e) {
    if (!this.state.username) {
      // TODO set error state of username
      return;
    }

    if (!this.state.password) {
      // TODO set error state of password
      return;
    }

    if (this.props.onLogin) {
      this.props.onLogin(this.state.username, this.state.password);
    }
  }

});

module.exports = Large;
