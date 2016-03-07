/**
 * Column View used for advanced search.
 * Pass the column object in the props and should contain the fieldName index
 *

 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var DropDownMenu = Chamel.DropDownMenu;
var IconButton = Chamel.IconButton;


/**
 * Displays the columns to view used in advanced search
 */
var ColumnView = React.createClass({

    propTypes: {
        onRemove: React.PropTypes.func,
        onUpdate: React.PropTypes.func,
        fieldData: React.PropTypes.object,
        index: React.PropTypes.number,
        column: React.PropTypes.object,
        objType: React.PropTypes.string,
    },
    
    getInitialState: function() {
        // Return the initial state
        return { 
            fieldName: this.props.column.fieldName,
            selectedFieldIndex: this.props.fieldData.selectedIndex,
        };
    },

    render: function() {
    		
        return (
            <div className="row" key={this.props.index}>
                <div className="col-small-3">
                    <DropDownMenu
                        menuItems={this.props.fieldData.fields}
                        selectedIndex={this.state.selectedFieldIndex}
                        onChange={this._handleMenuClick} />
                </div>
                <div className="col-small-1">
                    <IconButton onClick={this._handleRemoveColumnToView} className="fa fa-times" />
                </div>
            </div>
        );
    },
    
    /**
     * Removes the column to view
     *
     * @private
     */
    _handleRemoveColumnToView: function () {
        if(this.props.onRemove) this.props.onRemove('columnView', this.props.index);
    },
    
    /**
     * Callback used to handle commands when user selects the column field
     *
     * @param {DOMEvent} e      Reference to the DOM event being sent
     * @param {int} key     The index of the menu clicked
     * @param {array} field     The object value of the menu clicked
     * @private
     */
    _handleMenuClick: function(e, key, field) {
        if(this.props.onUpdate) this.props.onUpdate(field.name, this.props.index);
        
        this.setState({ 
            selectedFieldIndex: key
        });
    },
});

module.exports = ColumnView;
