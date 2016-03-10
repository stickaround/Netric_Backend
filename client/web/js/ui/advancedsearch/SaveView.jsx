/**
 * Provides user inputs required for browser view details
 * Displays the inputs for View Name, Description and if this view will be the default view.
 *

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
        data: React.PropTypes.object,
        onSave: React.PropTypes.func,
        onCancel: React.PropTypes.func,
    },
    
    componentDidMount: function() {
        this.refs.name.setValue(this.props.data.name);
        this.refs.description.setValue(this.props.data.description);
        this.refs.defaultView.setChecked(this.props.data.default);
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
            this.refs.name.setErrorText('View Name is required.');
            return;
        }
        
        if(this.props.onSave) {
            this.props.data.name = name;
            this.props.data.description = this.refs.description.getValue();
            this.props.data.default = this.refs.defaultView.isChecked()
            
            this.props.onSave(this.props.data);
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
