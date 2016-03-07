/**
 * Plugin for logging time on a task
 *

 */
'use strict';

var React = require('react');

var LogTime = React.createClass({

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
          <div>LogTime Plugin</div>
        );
    }

});

module.exports = LogTime;