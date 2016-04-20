/**
 * Column View used for advanced search.
 * Pass the column object in the props and should contain the fieldName index
 *

 */
'use strict';

var React = require('react');
var Controls = require('../Controls.jsx');
var FieldsDropDown = require('../entity/FieldsDropDown.jsx');
var DropDownMenu = Controls.DropDownMenu;
var IconButton = Controls.IconButton;
var FlatButton = Controls.FlatButton;


/**
 * Displays the columns to view used in advanced search
 */
var ColumnView = React.createClass({

    propTypes: {
        
        /**
         * The type of object we are adding column view for
         *
         * @type {string}
         */
        objType: React.PropTypes.string.isRequired,

        /**
         * Array of columnToView to pre-populate
         *
         * @type {array}
         */
        columnToViewData: React.PropTypes.array,

        /**
         * Event triggered any time the user makes changes to the column view
         *
         * @type {func}
         */
        onChange: React.PropTypes.func
    },
    
    render: function() {

        let columnToViewDisplay = [];

        for (var idx in this.props.columnToViewData) {

            let columnToView = this.props.columnToViewData[idx];

            columnToViewDisplay.push(
                <div className="row" key={idx}>
                    <div className="col-small-3">
                        <FieldsDropDown
                            objType={this.props.objType}
                            selectedField={columnToView}
                            onChange={this._handleColumnViewClick.bind(this, idx)} />
                    </div>
                    <div className="col-small-1 col-medium-1">
                        <IconButton onClick={this._handleRemoveColumnToView.bind(this, idx)} className="fa fa-times" />
                    </div>
                </div>
            )
        }
    		
        return (
            <div className="container-fluid">
                {columnToViewDisplay}
                <div className="row">
                    <div className="col-small-12">
                        <FlatButton onClick={this._handleAddColumnToView} label={"Add"} />
                    </div>
                </div>
            </div>
        );
    },
    
    /**
     * Removes the column to view
     *
     * @param {int} index The index of the columnToView to be removed
     * @private
     */
    _handleRemoveColumnToView: function (index) {

        var columnToViewData = this.props.columnToViewData;
        columnToViewData.splice(index, 1);

        if (this.props.onChange) {
            this.props.onChange(columnToViewData);
        }
    },

    /**
     * Callback used to handle commands when user selects the column name in the dropdown menu
     *
     * @param {int} index The index of the sort order that changed its field name
     * @param {string} fieldName The value of the field name that was selected
     * @private
     */
    _handleColumnViewClick: function (index, fieldName) {

        let columnToViewData = this.props.columnToViewData;
        columnToViewData[index] = fieldName;

        if (this.props.onChange) {
            this.props.onChange(columnToViewData);
        }
    },

    /**
     * Append a column to the columnToViewData array
     *
     * @private
     */
    _handleAddColumnToView: function() {

        let columnToViewData = this.props.columnToViewData;

        // Set the default column field to id
        columnToViewData.push("id");

        if (this.props.onChange) {
            this.props.onChange(columnToViewData);
        }
    },
});

module.exports = ColumnView;
