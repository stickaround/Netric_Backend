/**
 * Flex box column
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Tab element
 */
var Column = React.createClass({

    render: function() {

    	return (
            <div className="entity-form-column">
                {this.props.children}
            </div>
        );
    }

});

module.exports = Column;