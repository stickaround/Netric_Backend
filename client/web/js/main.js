/**
 * @fileoverview Main function used for building netric module
 */
// Add react
//var React = require('react');

//Needed for onTouchTap
//Can go away when react 1.0 release
//Check this repo:
//https://github.com/zilverline/react-tap-event-plugin
//var injectTapEventPlugin = require("react-tap-event-plugin");
//injectTapEventPlugin();

// Build netric object to export
var netric = require("./base");
netric.location = require("./location/location");
netric.Device = require("./Device");
netric.Application = require("./Application");
netric.moduleLoader = require("./module/loader");

if (module)
    module.exports = netric;

export default netric;
