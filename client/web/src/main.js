/**
 * This is the main library file used to build and pack netric
 */

//Needed for onTouchTap
//Can go away when react 1.0 release. check this repo:
//https://github.com/zilverline/react-tap-event-plugin

/*
 * var injectTapEventPlugin = require("react-tap-event-plugin");
 * injectTapEventPlugin();
 *
 */

// Eventually everything will need to load like this
require("../sass/base.scss");

// Build netric object to export
var netric = require("./base");
netric.location = require("./location/location");
netric.Device = require("./Device");
netric.Application = require("./Application");
netric.moduleLoader = require("./module/loader");

if (module)
    module.exports = netric;

export default netric;
