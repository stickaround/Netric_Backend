/**
 * Grouping add dialog/dropdown
 *

 */
'use strict';

var React = require('react');
var groupingLoader = require("../../entity/groupingLoader");
var Chamel = require('chamel');
var DropDownMenu = Chamel.DropDownMenu;

/**
 * Chip used to represent a grouping entry
 */
var GroupingSelect = React.createClass({

    propTypes: {
        // Callback to be fired as soon as a grouping is selected
        onChange: React.PropTypes.func,
        // The object type we are working with
        objType: React.PropTypes.string.isRequired,
        // The grouping field name
        fieldName: React.PropTypes.string.isRequired,
        // List of groupings to exclude (already set in the entity)
        ignore: React.PropTypes.array,
        // Optional text label to use for the "add grouping" button/drop-down
        label: React.PropTypes.string,
        // Determine if we allow no selection in the dropdown
        allowNoSelection: React.PropTypes.bool,
        // Will contain a initial value if available
        selectedValue: React.PropTypes.string
    },

    /**
     * Set defaults
     */
    getDefaultProps: function () {
        return {
            label: 'Add',
            ignore: new Array(),
            allowNoSelection: true,
            selectedValue: null
        };
    },

    /**
     * Get the initial state of this componenet
     */
    getInitialState: function () {
        return {
            groupings: null
        }
    },

    /**
     * We have entered the DOM
     */
    componentDidMount: function () {
        groupingLoader.get(this.props.objType, this.props.fieldName, function (groupings) {
            this._handleGroupingChange(groupings);
        }.bind(this));

    },

    /**
     * Render component
     *
     * @returns {React.DOM}
     */
    render: function () {

        if (!this.state.groupings) {

            // We are still getting the groupings data from the groupingLoader so return an empty div
            return (<div />);
        }

        // TODO: use the groupingLoader to load up groups
        var menuItems = [{payload: '', text: this.props.label}];

        this._addGroupingOption(this.state.groupings, menuItems);

        var selectedIndex = (this.props.selectedValue) ?
            this._getSelectedIndex(menuItems, this.props.selectedValue) : 0;

        return (
            <DropDownMenu
                selectedIndex={parseInt(selectedIndex)}
                ref='dropdown'
                menuItems={menuItems}
                onChange={this._handleOnChange}/>
        );

        // TODO: Move this back to a dialog or fix the drop-down so it resizes when
        // more elements are added to it (menuItems)

        /*
         * We may want to move back to a dialog (below) if the drop-down proves to be
         * insufficient for any reason. For now the DD is a simple solution that allows us
         * to get groupings out the door with minimal work.
         * - Sky Stebnicki
         return (
         <span>
         <FlatButton label={this.props.label} onClick={this._handleDialogShow} />
         <Dialog
         ref='groupings'
         title={this.props.label}
         actions={dlgActions}
         modal={false}>
         Put a menu here of all the groupings to select from
         </Dialog>
         </span>
         );
         */
    },

    /**
     * Callback fired when we catch a change event for the groupings object
     *
     * @private
     */
    _handleGroupingChange: function (groupings) {
        this.setState({groupings: groupings.getGroupsHierarch()});
    },

    /**
     * Handle when a user selects a grouping or a multiple groupings
     */
    _handleSelect: function (groupingIds) {
    },

    /**
     * Display the dialog for adding groupings
     */
    _handleDialogShow: function (evt) {
        //this.refs.groupings.show();
    },

    /**
     * Handle when a user selects a value from the menu
     *
     * @private
     * @param {Event} e
     * @param {int} selectedIndex The index of the item selected
     * @param {Object} menuItem The menu item clicked on
     */
    _handleOnChange: function (e, selectedIndex, menuItem) {

        if (this.props.onChange) {
            this.props.onChange(menuItem.payload, menuItem.text);
        }
    },

    /**
     * Iterate through groups and add to the options array
     *
     * @param {Array} groups Groups to addd
     * @param {Array} arrOptions UI Options array
     * @param {string} opt_prefix Optional string prefix for sub-groups
     * @private
     */
    _addGroupingOption: function (groups, arrOptions, opt_prefix) {

        var prefix = opt_prefix || "";
        // Add a '-' to the very first subgroup
        var nextPrefix = prefix + "-\u0020\u0020";

        for (var i in groups) {
            arrOptions.push({
                payload: groups[i].id,
                text: prefix + groups[i].name
            });

            // Recursively handle children

            if (groups[i].children.length) {
                this._addGroupingOption(groups[i].children, arrOptions, nextPrefix);
            }
        }

        return arrOptions;
    },

    /**
     * Gets the index of the selected group
     *
     * @param {Array} data Array of data that will be mapped to get the index of the saved field/operator/blogic value
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
    },
});

module.exports = GroupingSelect;
