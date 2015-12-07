/**
 * Component that handles rendering Status Updates
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var Activity = require("./Activity.jsx");
var Chamel = require('chamel');
var TextField = Chamel.TextField;
var FlatButton = Chamel.FlatButton;
var IconButton = Chamel.IconButton;

/**
 * Render Status into an entity form
 */
var StatusUpdate = React.createClass({

    propTypes: {
        entity: React.PropTypes.object,
    },

    getInitialState: function () {

        // Return the initial state
        return {
            refresh: false
        };
    },

    render: function () {
        return (
            <div>
                <div className="entity-comments-form">
                    <div className="entity-comments-form-center">
                        <TextField ref="statusInput" hintText="Add Status" multiLine={true}/>
                    </div>
                    <div className="entity-comments-form-right">
                        <FlatButton
                            label="Send"
                            iconClassName="fa fa-paper-plane"
                            onClick={this._handleStatusSend}
                            />
                    </div>
                </div>
                <Activity entity={this.props.entity} refresh={this.state.refresh} />
            </div>
        );
    },

    _handleStatusSend: function() {
        var status = this.refs.statusInput.getValue();
        var objRefeference = this.props.entity.objType + ":" + this.props.entity.id;

        this.props.entity.addStatusUpdate(status, objRefeference, this._refreshActivity);
    },

    _refreshActivity: function() {
        this.setState({refresh: true});
    }
});

module.exports = StatusUpdate;
