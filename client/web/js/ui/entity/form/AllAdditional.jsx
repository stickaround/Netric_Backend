/**
 * All additional/custom fields
 *

 */
'use strict';

var React = require('react');

/**
 * All additional will gather all custom (non-system) fields and print them
 *
 * This allows users to add custom fields without having to worry about placing 
 * them into a form.
 */
var AllAdditional = React.createClass({

    render: function() {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        return (
            <div>
                Print all additional fields here
            </div>
        );
    }
});

module.exports = AllAdditional;