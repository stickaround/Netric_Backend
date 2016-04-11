/**
 * @fileOverview Form model that will parse the xml form string and get its childNodes
 *
 * Use the public function parseXML() to parse the xml form string
 *
 * @author =    Marl Tumulak; marl.tumulak@aereus.com;
 *            Copyright (c) 2016 Aereus Corporation. All rights reserved.
 */
'use strict';

var FormNode = require('./form/FormNode');

/**
 * Create an instance of Form Model
 *
 * @constructor
 */
var Form = function () {
}

/**
 * Parses the xml string by encapsulating it inside <form> tag
 *
 * After parsing, the xml child nodes are mapped and stored in node.childNodes
 *
 * @params {string} xmlString The xml form string that will be parsed
 * @public
 * @return {entity/form/Node}
 */
Form.prototype.parseXML = function (xmlString) {

    // Encapsulate the xmlString with <form> tag
    let xmlData = '<form>' + xmlString + '</form>';

    // http://api.jquery.com/jQuery.parseXML/
    let xmlDoc = jQuery.parseXML(xmlData);
    let rootFormNode = xmlDoc.documentElement;

    // Create an instance of node using the xml form node as the argument
    let formNode = new FormNode(rootFormNode.nodeName);
    formNode.loadXmlData(rootFormNode);

    // Get the xml child nodes
    this.getXmlNodes(rootFormNode, formNode);

    return formNode;
}

/**
 * Function that will get the xml child nodes
 *
 * @param {object} xmlNode The xml form node where we will map its child nodes
 * @param {entity/form/Node} parentNode The parent node where we will save the mapped child nodes
 * @public
 */
Form.prototype.getXmlNodes = function (xmlNode, parentNode) {

    var xmlChildNodes = xmlNode.childNodes;

    // Process through children
    for (let i = 0; i < xmlChildNodes.length; i++) {
        let childNode = xmlChildNodes[i];

        // Make sure that the xml child node is an element node type (ELEMENT_NODE)
        if (childNode.nodeType == childNode.ELEMENT_NODE) {

            // Let's create a new instance of node form model for this child node
            let childFormNode = new FormNode(childNode.nodeName);

            // Load the xml data using the childNode (xmlChildNodes[i])
            childFormNode.loadXmlData(childNode);

            // Now let's add the child form node model to the parentNode
            parentNode.addChildNode(childFormNode);

            /*
             * Call this function again to check if this child has its own child nodes
             * Use the node model created as our parentNode
             */
            this.getXmlNodes(childNode, childFormNode);
        }
    }
}

module.exports = Form;