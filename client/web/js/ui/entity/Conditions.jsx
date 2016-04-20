/**
 * Display list of entity conditions
 *

 */
'use strict';

var React = require('react');
var WhereComponent = require('./Where.jsx');
var Where = require("../../entity/Where");
var Controls = require('../Controls.jsx');
var IconButton = Controls.IconButton;
var FontIcon = Controls.FontIcon;
var FlatButton = Controls.FlatButton;


var Conditions = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * Array of conditions to pre-populate
         *
         * @type {entity\Where[]}
         */
        conditions: React.PropTypes.array,

        /**
         * Event triggered any time the user makes changes to the conditions
         *
         * @type {func}
         */
        onChange: React.PropTypes.func,

        /**
         * The type of object we are adding conditions for
         *
         * @type {string}
         */
        objType: React.PropTypes.string.isRequired
    },

    getDefaultProps: function() {
        return {
            // If the caller does not pass any conditions, initilize to 0
            conditions: []
        };
    },

    /**
     * Render list of conditions
     */
    render: function() {

        var Wheres = [];
        for (var i in this.props.conditions) {
            Wheres.push(
                <WhereComponent
                  key={parseInt(i)}
                  index={parseInt(i)}
                  objType={this.props.objType}
                  onChange={this._handleWhereChange}
                  onRemove={this._handleWhereRemove}
                  where={this.props.conditions[i]}
                />
            );
        }

        return (
          <div className="container-fluid">
              {Wheres}
              <div className="row">
                  <div className="col-small-12">
                      <FlatButton onClick={this._handleAddWhere} label={"Add"} />
                  </div>
              </div>
          </div>
        );
    },

    /**
     * Handle event where an event changes
     *
     * @param {int} index The index of the condition in the array of Wheres
     * @param {Where} where Where object that was edited
     */
    _handleWhereChange: function(index, where) {
        var conditions = this.props.conditions;
        conditions[index] = where;

        if (this.props.onChange) {
            this.props.onChange(conditions);
        }
    },

    /**
     * Remove a where condition from the conditions array
     *
     * @param {int} index The index of the condition in the array of Wheres
     */
    _handleWhereRemove: function(index) {
        var conditions = this.props.conditions;
        conditions.splice(index, 1);

        if (this.props.onChange) {
            this.props.onChange(conditions);
        }
    },

    /**
     * Append a where to the conditions array
     */
    _handleAddWhere: function() {
        var conditions = this.props.conditions;
        // Add a very generic Where condition
        var where = new Where("id");
        conditions.push(where);

        if (this.props.onChange) {
            this.props.onChange(conditions);
        }
    }
});

module.exports = Conditions;
