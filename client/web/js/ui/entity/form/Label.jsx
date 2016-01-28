/**
 * Displays the label text
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('Chamel');

/**
 * Label element
 */
var Label = React.createClass({

    render: function () {

        return (
            <div className="entity-form-field-label">
                {this.props.children}
            </div>
        );
    }

});

module.exports = Label;