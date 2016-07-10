/**
 * Handle a web hook type action
 *
 * All actions have a 'data' field, which is just a JSON encoded string
 * used by the backend when executing the action.
 *
 * When the ActionDetails plugin is rendered it will decode or parse the string
 * and pass it down to the type component.
 *

 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var netric = require("../../../../../base");
var Controls = require('../../../../Controls.jsx');
var TextField = Controls.TextField;

/**
 * Manage action data for webhook
 */
var WebHook = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * Callback to call when a user changes any properties of the action
         */
        onChange: React.PropTypes.func,

        /**
         * Flag indicating if we are in edit mode or view mode
         */
        editMode: React.PropTypes.bool,

        /**
         * The object type this action is running against
         */
        objType: React.PropTypes.string.isRequired,

        /**
         * Data from the action - decoded JSON object
         */
        data: React.PropTypes.object
    },


    /**
     * Render action type form
     *
     * @returns {JSX}
     */
    render: function () {

        if(this.props.editMode) {
            return (
                <div>
                    <TextField
                        floatingLabelText='Url'
                        ref="urlInput"
                        defaultValue={this.props.data.url}
                        onBlur={this._handleDataChange}/>
                </div>
            );
        } else {
            return (
                <div className="entity-form-field">
                    <div className="entity-form-field-label">
                        Url
                    </div>
                    <div className="entity-form-field-value">
                        {this.props.data['url']}
                    </div>
                </div>
            );
        }
    },

    /**
     * When a property changes send an event so it can be handled
     *
     * @param {DOMEvent} evt Reference to the DOM event being sent
     * @private
     */
    _handleDataChange: function (evt) {
        let data = this.props.data;
        data['url'] = evt.target.value;

        if (this.props.onChange) {
            this.props.onChange(data);
        }
    },
});

module.exports = WebHook;
