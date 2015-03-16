/**
 * Root form component
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.form.Form");

/**
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};
netric.ui.entity = netric.ui.entity || {};
netric.ui.entity.form = netric.ui.entity.form || {};

/**
 * Base level element for enetity forms
 */
netric.ui.entity.form.Form = React.createClass({
    render: function() {
        return (
            <div>{this.props.childElements}</div>
        );
    }
});
