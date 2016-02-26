/**
 * Plugin that will print a friendly drop-down to add values into a text box or textarea as merge fields
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Controls = require('../../../Controls.jsx');
var DropDownMenu = Controls.DropDownMenu;
var IconButton = Controls.IconButton;
var FlatButton = Controls.FlatButton;
var AppBar = Controls.AppBar;
var Selector = require('./Selector.jsx');

var MergeField = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * The object type where we will get the entity fields
         *
         * @type {string}
         */
        objType: React.PropTypes.string,

        /**
         * Function that will be called when selecting a merge field
         *
         * @type {function}
         */
        onSelect: React.PropTypes.func,

        /**
         * Function that is called when clicking the back button
         *
         * @type {function}
         */
        onNavBtnClick: React.PropTypes.func
    },

    getInitialState: function () {

        return ({
            field: null,
            fieldSelected: null,
            subtypeFieldSelected: null,
            errorMsg: null
        });
    },

    render: function () {

        // Determine if we need to display the toolbar or just the icon button
        var toolBar = null;
        if (!this.props.hideToolbar) {
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
                </AppBar>
            );
        }

        var displayErrorMsg = null;
        if (this.state.errorMsg) {
            displayErrorMsg = (
                <div className="entity-form-input-error">
                    {this.state.errorMsg}
                </div>
            );
        }

        let displaySubtypSelector = null;

        /*
         * If the selected field is an object and has a subtype
         *  then we will display subtype's field selector to display the subtype's fields
         */
        if (this.state.field
            && this.state.field.subtype
            && this.state.field.type == this.state.field.types.object) {
            displaySubtypSelector = (
                <div className="entity-form-field-inline-block">
                    <Selector
                        objType={this.state.field.subtype}
                        includeManager={false}
                        displayType="dropdown"
                        filterBy="none"
                        hideFieldTypes={['object_multi', 'fkey_multi']}
                        parentFieldName={this.state.field.name}
                        selectedField={this.state.subtypeFieldSelected}
                        onChange={this._handleSubtypeMenuSelect}
                    />
                </div>
            );
        }

        return (
            <div className="entity-form">
                {toolBar}
                {displayErrorMsg}
                <div>
                    <div className="entity-form-field-inline-block">
                        <Selector
                            objType={this.props.objType}
                            includeManager={false}
                            displayType="dropdown"
                            filterBy="none"
                            hideFieldTypes={['object_multi', 'fkey_multi']}
                            selectedField={this.state.fieldSelected}
                            onChange={this._handleMenuSelect}
                            getSelectedFieldObject={this._handleGetSelectedFieldObject}
                        />
                    </div>
                    {displaySubtypSelector}
                </div>
                <div>
                    <FlatButton label='Select' onClick={this._handleSelectField}/>
                </div>
            </div>
        );
    },

    /**
     * Respond when the user clicks the back button
     *
     * @param evt
     * @private
     */
    _handleBackButtonClicked: function (evt) {
        if (this.props.onNavBtnClick) {
            this.props.onNavBtnClick();
        }
    },

    /**
     * Callback used to handle the selecting of field dropdown menu
     *
     * @param {string} fieldValue The value of the field that was selected
     * @private
     */
    _handleMenuSelect: function (fieldValue) {
        this.setState({
            fieldSelected: fieldValue,
            subtypeFieldSelected: null,
            errorMsg: null
        });
    },

    /**
     * Callback used to handle the selecting of subtype field dropdown menu
     *
     * @param {string} fieldValue The value of the field that was selected
     * @private
     */
    _handleSubtypeMenuSelect: function (fieldValue) {
        this.setState({
            subtypeFieldSelected: fieldValue
        });
    },

    /**
     * Callback used to get the field object selected
     *
     * @param {object} field The field object that was selected
     * @private
     */
    _handleGetSelectedFieldObject: function (field) {
        this.setState({field: field});
    },

    /**
     * Handles the selecting of merge field
     * @private
     */
    _handleSelectField: function () {
        var fieldSelected = null;

        /*
         * If the selected field is an object and has a subtype
         *  then we will display subtype's field selector to display the subtype's fields
         */
        if (this.state.field
            && this.state.field.subtype
            && this.state.field.type == this.state.field.types.object) {

            if (this.state.subtypeFieldSelected) {
                fieldSelected = this.state.subtypeFieldSelected;
            } else {
                this.setState({errorMsg: 'Please select a subtype merge field.'})
            }
        } else if(this.state.fieldSelected) {
            fieldSelected = this.state.fieldSelected
        } else {
            this.setState({errorMsg: 'Please select a merge field.'})
        }


        if (fieldSelected && this.props.onSelect) {
            this.props.onSelect({fieldSelected: fieldSelected});
        }
    }
});

module.exports = MergeField;