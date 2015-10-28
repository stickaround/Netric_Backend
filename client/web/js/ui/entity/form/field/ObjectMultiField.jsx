/**
 * Object Multi field component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var GroupingChip = require("../../GroupingChip.jsx");
var GroupingSelect = require("../../GroupingSelect.jsx");

/**
 * Base level element for enetity forms
 */
var ObjectMultiField = React.createClass({

    /**
     * Expected props
     */
    propTypes: {
        xmlNode: React.PropTypes.object,
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
