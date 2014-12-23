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
	// Try to determine the current devices size
	this.getDeviceSize_();
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

/**
 * Detect the size of the current device and set this.size
 *
 * @private
 */
 netric.Device.prototype.getDeviceSize_ = function() {
 	var width = alib.dom.getClientWidth();
 	
 	if (width <= 768) {
 		this.size = netric.Device.sizes.small;
 	} else if (width > 768 && width < 1200) {
 		this.size = netric.Device.sizes.medium;
 	} else if (width >= 1200) {
 		this.size = netric.Device.sizes.large;
 	}
 }