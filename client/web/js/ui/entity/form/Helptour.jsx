/**
 * A row 
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

/**
 * Hideable tour infobox
 */
var Helptour = React.createClass({

    render: function() {

		var xmlNode = this.props.xmlNode;
		var tourId = xmlNode.getAttribute("id");
    	var type = xmlNode.getAttribute("type");

    	return (
            <div data-tour={tourId} data-tour-type={type} />
        );
    }

});

module.exports = Helptour;