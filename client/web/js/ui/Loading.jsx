'use strict';

var React = require('react');

var Loading = React.createClass({
	
	propTypes: {
		className: React.PropTypes.string,
    },

    getDefaultProps: function() {
        return {
        	className: "loading",
        }
    },
	
    render: function() {
        return (
            <div className={this.props.className}>
                <i className="fa fa-spinner fa-pulse" />
            </div>
        );
    }

});

module.exports = Loading;