/**
 * Field component
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.form.Field");

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
netric.ui.entity.form.Field = React.createClass({

    render: function() {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');

        return (
            <div>
                Field: {fieldName}
            </div>
        );
    }
});
