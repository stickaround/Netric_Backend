/**
 * Plugin for converting a lead
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');

var Convert = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * Entity being edited
         *
         * @type {entity\Entity}
         */
        entity: React.PropTypes.object,

        /**
         * Flag indicating if we are in edit mode or view mode
         *
         * @type {bool}
         */
        editMode: React.PropTypes.bool
    },

    render: function() {
        return (
          <div>Convert Lead</div>
        );
    }

});

module.exports = Convert;