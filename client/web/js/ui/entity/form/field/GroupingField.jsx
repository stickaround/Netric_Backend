/**
 * Text field compnent
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var GroupingChip = require("../../GroupingChip.jsx");

/**
 * Base level element for enetity forms
 */
var GroupingField = React.createClass({

    render: function() {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        

        var field = this.props.entity.def.getField(fieldName);
        var fieldValues = this.props.entity.getValueName(fieldName);

        var chips = [];
        for (var i in fieldValues) {
        	chips.push(
        		<GroupingChip id={fieldValues[i].key} name={fieldValues[i].value} />
        	);
        }

        // TODO: create a GroupingChip component

        if (this.props.editMode) {
          return (
              <div>{chips}</div>
          );
        } else {
          return (
            <div>{chips}</div>
          );
        }
    }
});

module.exports = GroupingField;