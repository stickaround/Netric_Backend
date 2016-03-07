/**
 * A row 
 *

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
        // Type can be 'inline', 'dialog', or 'popup'
    	var type = xmlNode.getAttribute("type");
        let nodeText = "";
        if (xmlNode.childNodes.length) {
            nodeText = xmlNode.childNodes[0].nodeValue;
        }

        // We only display tour information in edit mode
        if (this.props.editMode) {
            return (
                <div className='info' data-tour={tourId} data-tour-type={type}>{nodeText}</div>
            );
        } else {
            return (<div />);
        }

    }

});

module.exports = Helptour;
