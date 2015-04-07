/**
 * Root form component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Base level element for enetity forms
 */
var Form = React.createClass({

	/**
     * Expected props
     */
    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },
    
    render: function() {
        return (
            <div className="entity-form">{this.props.children}</div>
        );
    }
});

module.exports = Form;