/**
 * Provides user inputs required for browser view details 
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var TextField = Chamel.TextField;
var FlatButton = Chamel.FlatButton;
var Checkbox = Chamel.Checkbox;

/**
 * Displays input fields for saving the browser view
 */
var SaveView = React.createClass({
    
    propTypes: {
        browserView: React.PropTypes.object,
        onSave: React.PropTypes.func,
        onCancel: React.PropTypes.func,
    },
    
    componentDidMount: function() {
        var name = this.props.browserView.name;
        
        if(this.props.browserView.id == null) {
            name = 'My Custom View';
        }   
        
        this.refs.name.setValue(name);
        this.refs.description.setValue(this.props.browserView.description);
        this.refs.defaultView.setChecked(this.props.browserView.default);
    },
    
    render: function() { 
        
        return (
                <div>
                    <div>
                        <TextField floatingLabelText='Name:' ref='name'/>
                    </div>
                    <div>
                        <TextField floatingLabelText='Description:' ref='description' />
                    </div>
                    <div>
                        <Checkbox
                            ref="defaultView"
                            value="default"
                            label="Default"
                            defaultSwitched={false} />
                    </div>
                    <div>
                        <FlatButton label='Save' onClick={this._handleSave} />
                        <FlatButton label='Back' onClick={this._handleCancel} />
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
            this.refs.nameInput.setErrorText('View Name is required.');
            return;
        }
        
        if(this.props.onSave) {
            this.props.browserView.name = name;
            this.props.browserView.description = this.refs.description.getValue();
            this.props.browserView.default = this.refs.defaultView.isChecked()
            
            this.props.onSave();
            this._handleCancel();
        }
    },
    
    /**
     * Handles the back button click.
     *
     * @private
     */
    _handleCancel: function() {
        if(this.props.onCancel) this.props.onCancel();
    }
});

module.exports = SaveView;
