/**
 * Tab UIML element
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var Tab = Chamel.Tab;
var EntityFormShowFilter = require("../../mixins/EntityFormShowFilter.jsx");

/**
 * Tab element
 */
var FormTab = React.createClass({

    mixins: [EntityFormShowFilter],

    render: function () {

        var xmlNode = this.props.xmlNode;
        var label = xmlNode.getAttribute('name');
        var showif = xmlNode.getAttribute('showif');

        var displayTab = (
            <div>
                {this.props.children}
            </div>
        );

        if (showif) {

            // If ::evaluateShowIf() returns false, it means that the showif did not match the filter specified
            if (!this.evaluateShowIf(showif)) {
                displayTab = null;
            }
        }

        return (
            displayTab
        );
        /*
         if (this.props.renderChildren) {
         return (
         <div>
         {this.props.children}
         </div>
         );
         } else {
         return (
         <Tab {...this.props} label={label}>
         {this.props.children}
         </Tab>
         );
         }
         */

    }
});

module.exports = FormTab;