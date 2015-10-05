'use strict';

var React = require('react');

var Loading = React.createClass({
	
	propTypes: {
        loadingCss: React.PropTypes.string,
    },

    getDefaultProps: function() {
        return {
        	loadingCss: "loading",
        }
    },
	
    render: function() {
        return (
            <div className={this.props.loadingCss}>
                <i className="fa fa-spinner fa-pulse" />
            </div>
        );
    }

});

module.exports = Loading;