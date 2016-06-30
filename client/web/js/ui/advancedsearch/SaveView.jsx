/**
 * Provides user inputs required for browser view details
 * Displays the inputs for View Name, Description and if this view will be the default view.
 *

 */
'use strict';

var React = require('react');
var events = require('../../util/events');
var Controls = require('../Controls.jsx');
var TextField = Controls.TextField;
var FlatButton = Controls.FlatButton;
var Checkbox = Controls.Checkbox;

/**
 * Displays input fields for saving the browser view
 */
var SaveView = React.createClass({
    
    propTypes: {

        /**
         * The id of the browser view
         *
         * @type {int}
         */
        id: React.PropTypes.string,

        /**
         * The name of the browser view
         *
         * @type {string}
         */
        name: React.PropTypes.string,

        /**
         * The description of the browser view
         *
         * @type {string}
         */
        description: React.PropTypes.string,

        /**
         * Flag that determines if the browser view will be set as a default view for this specific user
         *
         * @type {bool}
         */
        default: React.PropTypes.bool,

        /**
         * Event triggered when the user decides to save the browser view
         *
         * @type {func}
         */
        onSave: React.PropTypes.func,

        /**
         * Event triggered when the user cancels the saving of browser view
         *
         * @type {func}
         */
        onCancel: React.PropTypes.func,
    },

    componentDidMount: function() {

        // Event listener for saving the view
        events.listen(this.props.eventsObj, "saveView", function (evt) {
            this._handleSave();
        }.bind(this));
    },

    render: function() {
        
        return (
            <div className="entity-form-field">
                <div className="entity-form-field-value">
                    <TextField floatingLabelText='View Name' ref='name' value={this.props.name}/>
                </div>
                <div className="entity-form-field-value">
                    <TextField floatingLabelText='Description' ref='description' value={this.props.description}/>
                </div>
                <div className="entity-form-field-value">
                    <Checkbox
                        ref="defaultView"
                        value="default"
                        label="Default"
                        defaultSwitched={this.props.default} />
                </div>
            </div>
        );
    },
    
    /**
     * Handles the save button click. Collects the user input data provided. 
     *
     * @private
     */
    _handleSave: function() {

        var name = this.refs.name.getValue();
        
        // Require the view name.
        if(name.length == 0) {
            this.refs.name.setErrorText('View Name is required.');
            return;
        }
        
        if(this.props.onSave) {

            var data = {
                id: this.props.id,
                name: name,
                description: this.refs.description.getValue(),
                default: this.refs.defaultView.isChecked()
            }
            
            this.props.onSave(data);
        }
    }
});

module.exports = SaveView;
