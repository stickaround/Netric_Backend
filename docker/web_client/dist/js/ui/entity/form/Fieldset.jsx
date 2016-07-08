/**
 * Fieldset UIML element
 *

 */
'use strict';

var React = require('react');

/**
 * Tab element
 */
var Fieldset = React.createClass({

    /**
     * Render the component
     */
    render: function() {

        var elementNode = this.props.elementNode;
        var name = elementNode.getAttribute('name');
        var legend;
        if (name) {
            legend = <legend>{name}</legend>;
        }
        
    	return (
            <fieldset className="entity-form-fieldset">
                {legend}
                {this.props.children}
            </fieldset>
        );
        
    }
});

module.exports = Fieldset;
