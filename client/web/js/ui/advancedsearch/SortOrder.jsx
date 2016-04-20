/**
 * Sort By used for advance search.
 * Pass the orderBy object in the props and should contains the fieldName and direction data
 *

 */
'use strict';

var React = require('react');
var Controls = require('../Controls.jsx');
var FieldsDropDown = require('../entity/FieldsDropDown.jsx');
var OrderBy = require("../../entity/OrderBy");
var DropDownMenu = Controls.DropDownMenu;
var IconButton = Controls.IconButton;
var FlatButton = Controls.FlatButton;

var directionMenuData = [
    {payload: 'asc', text: 'Ascending'},
    {payload: 'desc', text: 'Descending'},
];

/**
 * Displays the sort order used in the advanced search.
 */
var SortOrder = React.createClass({

    propTypes: {

        /**
         * The type of object we are adding orderBy for
         *
         * @type {string}
         */
        objType: React.PropTypes.string.isRequired,

        /**
         * Array of orderBy to pre-populate
         *
         * @type {entity\OrderBy[]}
         */
        orderByData: React.PropTypes.array,

        /**
         * Event triggered any time the user makes changes to the orderBy
         *
         * @type {func}
         */
        onChange: React.PropTypes.func
    },

    render: function () {

        let sortOrderDisplay = [];

        for (var idx in this.props.orderByData) {

            let orderBy = this.props.orderByData[idx];
            let directionIndex = this._getSelectedIndex(directionMenuData, orderBy.getDirection());

            sortOrderDisplay.push(
                <div className="row" key={idx}>
                    <div className="col-small-3">
                        <FieldsDropDown
                            objType={this.props.objType}
                            selectedField={orderBy.getFieldName()}
                            onChange={this._handleFieldNameClick.bind(this, idx)}/>
                    </div>
                    <div className="col-small-2">
                        <DropDownMenu
                            menuItems={directionMenuData}
                            selectedIndex={parseInt(directionIndex)}
                            onChange={this._handleDirectionClick.bind(this, idx)}/>
                    </div>
                    <div className="col-small-1">
                        <IconButton
                            className="fa fa-times"
                            onClick={this._handleRemoveOrder.bind(this, idx)}/>
                    </div>
                </div>
            )
        }

        return (
            <div className="container-fluid">
                {sortOrderDisplay}
                <div className="row">
                    <div className="col-small-12">
                        <FlatButton onClick={this._handleAddOrderBy} label={"Add"}/>
                    </div>
                </div>
            </div>
        );
    },

    /**
     * Removes the Sort Order
     *
     * @param {int} index The index of the sort order to be removed
     * @private
     */
    _handleRemoveOrder: function (index) {

        var orderByData = this.props.orderByData;
        orderByData.splice(index, 1);

        if (this.props.onChange) {
            this.props.onChange(orderByData);
        }
    },

    /**
     * Callback used to handle commands when user selects the field name in the dropdown menu
     *
     * @param {int} index The index of the sort order that changed its field name
     * @param {string} fieldName The value of the field name that was selected
     * @private
     */
    _handleFieldNameClick: function (index, fieldName) {

        let orderByData = this.props.orderByData;
        orderByData[index].setFieldName(fieldName);

        if (this.props.onChange) {
            this.props.onChange(orderByData);
        }
    },

    /**
     * Callback used to handle commands when user selects the sort direction in the dropdown menu
     *
     * @param {int} index The index of the sort order that changed its direction
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {array} menuItem The object value of the menu clicked
     * @private
     */
    _handleDirectionClick: function (index, e, key, menuItem) {

        let orderByData = this.props.orderByData;

        orderByData[index].setDirection(menuItem.payload);

        if (this.props.onChange) {
            this.props.onChange(orderByData);
        }
    },

    /**
     * Append a sort order to the orderBy array
     *
     * @private
     */
    _handleAddOrderBy: function () {

        let orderByData = this.props.orderByData;
        let orderBy = new OrderBy();

        // Set the default fieldName to id
        orderBy.setFieldName("id");

        // Set the default direction to asc
        orderBy.setDirection("asc");

        // Add the newly created orderBy to the orderByData array
        orderByData.push(orderBy);

        if (this.props.onChange) {
            this.props.onChange(orderByData);
        }
    },

    /**
     * Gets the index of an entry based on the name
     *
     * @param {Array} data Array of data that will be mapped to get the index of an entry
     * @param {string} value The value that will be used to get the index
     * @private
     */
    _getSelectedIndex: function (data, value) {
        var index = 0;
        for (var idx in data) {
            if (data[idx].payload == value) {
                index = idx;
                break;
            }
        }

        return index;
    }
});

module.exports = SortOrder;
