/**
 * Text field compnent
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

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
        		<span>{fieldValues[i].key}:{fieldValues[i].value}</span>
        	);
        }

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