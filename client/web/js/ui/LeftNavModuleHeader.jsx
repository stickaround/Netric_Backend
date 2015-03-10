/**
 * Header view for the left nav when loaded from a module
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.LeftNavModuleHeader");

alib.require("netric.ui.AppBar");

/**
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};

/**
 * Module shell
 */
netric.ui.LeftNavModuleHeader = React.createClass({

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
