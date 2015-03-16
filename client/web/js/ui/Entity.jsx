/**
 * Render an entity
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.Entity");

/**
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};

/**
 * Module shell
 */
netric.ui.Entity = React.createClass({

    render: function() {

        var appBar = "";

        if (this.props.onNavBtnClick) {
            appBar = <netric.ui.AppBar
                title={this.props.objType}
                iconClassNameLeft="fa fa-times"
                onNavBtnClick={this.navigationClick_} />;
        } else {
            appBar = <netric.ui.AppBar title={this.props.objType} />;
        }

        // Get the form
        var xmlData = '<form>' + this.props.form + '</form>';

        // http://api.jquery.com/jQuery.parseXML/
        var xmlDoc = jQuery.parseXML(xmlData);
        var rootFormNode = xmlDoc.documentElement;

        return (
            <div>
                <div>
                    {appBar}
                </div>
                <div>
                    Render: {this.props.objType}.{this.props.oid}
                    <netric.ui.entity.FormBuilder xmlNode={rootFormNode} />
                </div>
            </div>
        );
    },

    // The navigation button was clicked
    navigationClick_: function(evt) {
        if (this.props.onNavBtnClick)
            this.props.onNavBtnClick(evt);
    }

});
