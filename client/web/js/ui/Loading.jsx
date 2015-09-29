'use strict';

var React = require('react');

var Loading = React.createClass({

    render: function() {
        return (
            <div className="loading">
                <i className="fa fa-spinner fa-pulse" />
            </div>
        );
    }

});

module.exports = Loading;