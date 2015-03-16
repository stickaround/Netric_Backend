/**
 * Render an entity form from UIML
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.entity.FormBuilder");

/**
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};
netric.ui.entity = netric.ui.entity || {};

/**
 * Convert UIML into a UI Form
 */
netric.ui.entity.FormBuilder = React.createClass({

    render: function() {

        var xmlNode = this.props.xmlNode;
        var xmlChildNodes = xmlNode.childNodes;

        // Process through children
        var childElements = [];
        for (var i = 0; i < xmlChildNodes.length; i++) {
            var childNode = xmlChildNodes[i];
            if (childNode.nodeType == 1) {
                childElements.push(<netric.ui.entity.FormBuilder xmlNode={childNode} />);
            }
        }

        // Process current node and create element at this level
        var componentName = "netric.ui.entity.form.";
        var parts = xmlNode.nodeName.split("_");
        for (var i in parts) {
            // Convert to uc first
            var f = parts[i].charAt(0).toUpperCase();
            componentName += f + parts[i].substr(1);
        }

        /*
         * Try to render the dynamic component and pass childElements,
         * but if the component is not defined for the given element
         * then throw an exception because this should never happen.
         */
        var component = netric.getObjectByName(componentName);
        if (component != null) {
            return (
                React.createElement(component,
                    {
                        xmlNode:xmlNode,
                        childElements:childElements
                    }
                )
            );
        } else {
            // Let client know we have a problem with the UIML
            throw 'Unsupported element type in UIML: ' + xmlNode.nodeName;
        }
    }
});
