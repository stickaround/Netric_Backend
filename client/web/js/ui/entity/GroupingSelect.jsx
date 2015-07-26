/**
 * Grouping add dialog/dropdown
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var DropDownMenu = require("../DropDownMenu.jsx");
var groupingLoader = require("../../entity/groupingLoader");

/**
 * Groupings object
 */
var groupings_ = null;

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
		label: React.PropTypes.string
	},

	/**
	 * Set defaults
	 */
	getDefaultProps: function() {
	    return {
            label: 'Add',
            ignore: new Array(),
	    };
  	},

  	/**
  	 * Get the initial state of this componenet
  	 */
  	getInitialState: function() {
		return {
	    	ddSelectedIndex: 0,
            groupings: this.getGroupingsFromModel()
		}
	},

    /**
     *
     */
    getGroupingsFromModel: function() {

        /*
         * If we have not yet loaded then load it up, but return immediately with the
         * last loaded (or empty) grouping set so we can continue building the interface.
         */
        if (!groupings_) {
            groupings_ = groupingLoader.get(this.props.objType, this.props.fieldName, function() {
                this._handleGroupingChange();
            }.bind(this));

            alib.events.listen(groupings_, "change", this._handleGroupingChange);
        }

        return groupings_.getGroupsHierarch();
    },

    /**
     * When the component mounts start listening for grouping changes
     *
    componentWillMount: function() {
        groupingLoader.get(this.props.objType, this.props.fieldName, function(groupings) {
            alib.listen(groupings, "change", this._handleGroupingChange);
        }.bind(this));
    },

    /**
     * Stop listening for changes if we are no longer in the DOM
     *
    componentWillUnmount: function() {
        groupingLoader.get(this.props.objType, this.props.fieldName, function(groupings) {
            alib.unlisten(groupings, "change", this._handleGroupingChange);
        }.bind(this));
    },*/

    /**
     * Render component
     *
     * @returns {React.DOM}
     */
	render: function() {

        // TODO: use the groupingLoader to load up groups
		var menuItems = [
		   { payload: '', text: this.props.label }
		];

        this._addGroupingOption(this.state.groupings, menuItems);

		return (
			<DropDownMenu 
				selectedIndex={this.state.ddSelectedIndex} 
				ref='dropdown' 
				menuItems={menuItems}
				onChange={this._handleOnChange} />
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
				<FlatButton label={this.props.label} onTouchTap={this._handleDialogShow} />
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
    _handleGroupingChange: function() {
        if (groupings_) {
            this.setState({groupings: this.getGroupingsFromModel() });
        }
    },

	/**
	 * Handle when a user selects a grouping or a multiple groupings
	 */
	_handleSelect: function(groupingIds) {
	},

	/**
	 * Display the dialog for adding groupings
	 */
	_handleDialogShow: function(evt) {
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
	_handleOnChange: function(e, selectedIndex, menuItem) {
		
		// Update our local state
		this.setState({ddSelectedIndex: selectedIndex})

		if (this.props.onChange) {
            this.props.onChange(menuItem.payload, menuItem.text);
		}

		// Reset back to the first element
		this.setState({ddSelectedIndex: 0});
	},

    /**
     * Iterate through groups and add to the options array
     *
     * @param {Array} groups Groups to addd
     * @param {Array} arrOptions UI Options array
     * @param {string} opt_prefix Optional string prefix for sub-groups
     * @private
     */
    _addGroupingOption: function(groups, arrOptions, opt_prefix) {

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
    }
});

module.exports = GroupingSelect;
