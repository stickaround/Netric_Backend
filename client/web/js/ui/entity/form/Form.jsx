/**
 * Root form component
 *

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

        /**
         * Current element node level
         *
         * @type {entity/form/Node}
         */
        elementNode: React.PropTypes.object,

        /**
         * Entity being edited
         *
         * @type {entity\Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Generic object used to pass events back up to controller
         *
         * @type {Object}
         */
        eventsObj: React.PropTypes.object,

        /**
         * Flag indicating if we are in edit mode or view mode
         *
         * @type {bool}
         */
        editMode: React.PropTypes.bool
    },
    
    render: function() {
        return (
            <div className="entity-form">{this.props.children}</div>
        );
    }
});

module.exports = Form;