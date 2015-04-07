/**
 * Header view for the left nav when loaded from a module
 *
 * @jsx React.DOM
 */
'use strict';
var React = require('react');

/**
 * Module shell
 */
var LeftNavModuleHeader = React.createClass({

    getDefaultProps: function() {
        return {
            moduleTitle: "Untitled Module"
        };
    },

    componentDidMount: function() {

        /*
        netric.module.loader.get("messages", function(mdl){
            this.setState({name: mdl.name});
        }.bind(this));
        */

    },

    render: function() {
        return (
            <div className="left-nav-header">
                <h2><i className="fa fa-chevron-left" onClick={this.props.onNavBtnClick}></i> {this.props.moduleTitle}</h2>
            </div>
        );
    }

});

module.exports = LeftNavModuleHeader;