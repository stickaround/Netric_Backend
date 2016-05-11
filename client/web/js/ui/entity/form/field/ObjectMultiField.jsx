/**
 * Object Multi field component
 *

 */
'use strict';

var React = require('react');
var GroupingSelect = require("../../GroupingSelect.jsx");

/**
 * Base level element for enetity forms
 */
var ObjectMultiField = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        elementNode: React.PropTypes.object.isRequired,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    /**
     * Render the component
     */
    render: function () {
        return (<div>ObjectList Here</div>);
    }
});

module.exports = ObjectMultiField;
