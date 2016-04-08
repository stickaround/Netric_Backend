/**
 * @fileOverview Form model that will parse the xml form string and get its childNodes
 *
 * Use the public function parseXML() to parse the xml form string
 *
 * @author =    Marl Tumulak; marl.tumulak@aereus.com;
 *            Copyright (c) 2016 Aereus Corporation. All rights reserved.
 */
'use strict';

var Node = require('./form/Node');

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
    let formNode = new Node(rootFormNode.nodeName);
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

        // Make sure that the children is an element node type
        if (childNode.nodeType == childNode.ELEMENT_NODE) {

            // If we found a child node, then let's create an instance of node model
            parentNode.childNodes[i] = new Node(childNode.nodeName);
            parentNode.childNodes[i].loadXmlData(childNode);

            /*
             * Call this function again to check if this child has its own child nodes
             * Use the node model created as our parentNode
             */
            this.getXmlNodes(childNode, parentNode.childNodes[i]);
        }
    }
}

module.exports = Form;