/**
 * Component that will display the object types in a dropdown
 *

 */
'use strict';

var React = require('react');
var definitionLoader = require("../../entity/definitionLoader");
var Controls = require('../Controls.jsx');
var DropDownMenu = Controls.DropDownMenu;

/**
 * Create a dropdown that contains the object types
 */
var ObjectTypeDropDown = React.createClass({

    propTypes: {

        /**
         * Optional. The object type that was selected
         *
         * @var {string}
         */
        objType: React.PropTypes.string,

        /**
         * Callback called when the user selects an object type
         *
         * @var {function}
         */
        onChange: React.PropTypes.func
    },

    /**
     * Return the starting state of this component
     *
     * @returns {{}}
     */
    getInitialState: function() {
        return {
            entityDefinitions: null
        };
    },

    /**
     * We have entered the DOM
     */
    componentDidMount: function() {
        let func = function ProcessReturnedDefinitions(definitions) {
            this.setState({entityDefinition: definitions});
        }.bind(this);

        // Get all the object types
        definitionLoader.getAll(func);
    },

    /**
     * Render the dropdown containing all object types
     */
    render: function() {
        if (!this.state.entityDefinition) {

            // Entity definitions is loading still so return an indicator
            return (<div>Loading Object Types...</div>);
        }

        // Get the definition menu data
        let definitionsMenuData = this._getDefinitionMenuData();

        // Get the selected index of the objType
        let selectedIndex = (this.props.objType) ?
            this._getSelectedIndex(definitionsMenuData, this.props.objType) : 0;

        return (
            <div className="entity-form-field-value">
                <DropDownMenu
                    menuItems={definitionsMenuData}
                    selectedIndex={parseInt(selectedIndex)}
                    onChange={this._handleDefintionsMenuSelect}/>
            </div>
        );
    },

    /**
     * Callback used to handle the selecting of definition
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {int} key The index of the menu clicked
     * @param {array} menuItem The object value of the menu clicked
     * @private
     */
    _handleDefintionsMenuSelect: function (e, key, menuItem) {
        if(this.props.onChange) this.props.onChange(menuItem.objType)
    },

    /**
     * Function that will loop thru state.entityDefinition (Entity/Definition) to get the objType and create a menu data
     *
     * @returns {Array} Menu data of the objTypes
     * @private
     */
    _getDefinitionMenuData: function () {
        let definitionsMenuData = [];

        for (let idx in this.state.entityDefinition) {
            let def = this.state.entityDefinition[idx];

            definitionsMenuData.push({
                objType: def.objType,
                text: def.title
            })
        }

        // Sort entityDefinition
        definitionsMenuData.sort(function (a, b) {
            if (a.text < b.text) return -1;
            if (a.text > b.text) return 1;
            return 0;
        });

        // If no field name has been selected, enter a first explanation entry
        if(!this.props.objType) {
            definitionsMenuData.splice(0, 0, {
                objType: '',
                text: 'Select Object Type'
            });
        }

        return definitionsMenuData;
    },

    /**
     * Gets the index of an objType based on the name
     *
     * @param {Array} data Array of data that will be mapped to get the index of the saved objType
     * @param {string} value The value that will be used to get the index
     * @private
     */
    _getSelectedIndex: function (data, value) {
        var index = 0;
        for (var idx in data) {
            if (data[idx].objType == value) {
                index = idx;
                break;
            }
        }

        return index;
    }
});

module.exports = ObjectTypeDropDown;
