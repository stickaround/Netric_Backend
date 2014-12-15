/**
 * @fileoverview Device information class
 * 
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */

alib.declare("netric.Device");

alib.require("netric");

// Information about the current device
netric.Device = function() {
	// TODO: try to determine the size of the viewport of this device
}

/**
 * Static device sizes
 * 
 * @const
 * @public
 */
netric.Device.sizes = {
	// Phones and small devices
	small : 1,
	// Tablets
	medium : 3,
	// Desktops
	large : 5
};

/**
 * The size of the current device once loaded
 *
 * @type {netric.Device.sizes}
 */
netric.Device.prototype.size = netric.Device.sizes.large;