/**
 * A row 
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Tab element
 */
var Row = React.createClass({

    render: function() {

    	return (
            <div className="entity-form-row">
                {this.props.children}
            </div>
        );
    }

});

module.exports = Row;